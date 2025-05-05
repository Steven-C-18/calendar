<?php
/**
	* Events Calendar An extension for the phpBB 3.3.0 Forum Software package.
	* @author Steve <https://steven-clark.tech/phpBB3/index.php>
	* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace steve\calendar\calendar;

class upcoming_events
{
	protected $auth;
	protected $config;
	protected $db;
	protected $helper;
	protected $language;
	protected $notification_manager;
	protected $user;
	
	protected $date;
	protected $calendar_events;
	protected $calendar_events_attending;
	
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,		
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language,
		\phpbb\notification\manager $notification_manager,
		\phpbb\user $user,
		
		\steve\calendar\calendar\date_time $date,
		$calendar_events,
		$calendar_events_attending)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;	
		$this->helper = $helper;
		$this->language = $language;
		$this->notification_manager = $notification_manager;
		$this->user = $user;
		
		$this->date_time = $date;
		$this->table_calendar_events = $calendar_events;
		$this->table_events_attending = $calendar_events_attending;
	}

	private function events_table()
	{
		$days = intval($this->config['calendar_event_remind']);
		$sql = 'SELECT event_id, user_id, time_stamp, day, day_string, month, month_string, year, annual, title
			FROM ' . $this->table_calendar_events . "
			WHERE FROM_UNIXTIME(time_stamp) BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL $days DAY)";
		$results = $this->db->sql_query($sql);

		return $results;
	}
	
	private function get_attendees($event_ids)
	{
		$sql = 'SELECT event_id, user_id
			FROM ' . $this->table_events_attending . '
			WHERE ' . $this->db->sql_in_set('event_id', array_unique($event_ids)) . '
				AND year = ' . date("Y");
		$result = $this->db->sql_query($sql);
		
		$attendees = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (empty($row))
			{
				continue;
			}
			$attendees[(int) $row['event_id']][] = $row;
		}
		$this->db->sql_freeresult($result);
		
		return $attendees;
	}
	
	public function get_events()
	{
		if (empty($this->config['calendar_enable_notifications']))
		{
			return false;
		}
		
		$results = $this->events_table();
		
		$events = $event_ids = $attendees = [];
		while ($row = $this->db->sql_fetchrow($results))
		{
			if (empty($row))
			{
				continue;
			}			
			$event_ids[] = (int) $row['event_id'];
			$events[(int) $row['event_id']] = $row;
		}
		$this->db->sql_freeresult($results);
		
		if (empty($events))
		{
			return false;
		}
		
		$attendees = $this->get_attendees($event_ids);

		foreach ($events as $key => $event)
		{
			if (empty($event))
			{
				unset($events[$key]);
			}
			
			if (!empty($attendees[$event['event_id']]))
			{
				foreach ($attendees[$event['event_id']] as $attendee)
				{
					if (empty($attendee))
					{
						continue;
					}
					
					$year_js = empty($event['annual']) ? $event['year'] : date("Y");
					$this->notification_manager->add_notifications('steve.calendar.notification.type.upcoming_event', [
						'item_id'		=> $event['event_id'],
						'event_id'		=> $event['event_id'],
						'day'			=> $event['day'],
						'month'			=> $event['month'],
						'year'			=> $event['year'],
						'time_stamp'	=> $event['time_stamp'],
						'year'			=> $year_js,
						'user_id'		=> $event['user_id'],
						'users'			=> $attendee['user_id'],
						'url'			=> $this->helper->route('steve_calendar_day', [
							'day_string'=> $this->date_time->date_key($year_js, $event['month'], $event['day'], "D"), 
							'day' 		=> $event['day'], 
							'month' 	=> $this->date_time->date_key($year_js, $event['month'], (int) 1, "F"), 
							'year' 		=> $event['year']
						]),
						'title'			=> censor_text($event['title']),
					]);
				}
			}
		}
		unset($events, $attendees[$event['event_id']]);
		
		return $this;
	}
}
