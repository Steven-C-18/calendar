<?php
/**
	* Events calendar $extends the phpBB Software package.
	* @copyright (c) 2024, Steve, https://steven-clark.tech/
	* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace steve\calendar\calendar;

use steve\calendar\calendar\constants;

class calendar
{
	protected $auth;
	protected $config;
	protected $db;
	protected $language;
	protected $pagination;
	protected $request;
	protected $template;
	protected $user;
	
	protected $routing;
	protected $date;
	protected $calendar_events;
	protected $calendar_events_attending;
	
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,		
		\phpbb\db\driver\driver_interface $db,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language,
		\phpbb\pagination $pagination,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,

		\steve\calendar\calendar\routing $routing,
		\steve\calendar\calendar\date_time $date,
		$calendar_events,
		$calendar_events_attending)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->helper = $helper;
		$this->language = $language;
		$this->pagination = $pagination;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;

		$this->routing = $routing;
		$this->date_time = $date;
		$this->table_calendar_events = $calendar_events;
		$this->table_events_attending = $calendar_events_attending;

		$now = $this->date_time->now();
		$this->now = $now['now'];
		$this->now_year = $now['year'];
		$this->now_month = $now['month']; 
		$this->now_day = $now['day'];
	}

	public function get_calendar($year, $months)
	{
		$this->template->assign_vars([
			'CALENDAR'		=> true,

			'NOW_URL'		=> $this->helper->route('steve_calendar_day', ['day_string' => date("D", $this->now), 'day'	=> $this->now_day, 'month' => date('F', $this->now), 'year' => $this->now_year]),
			'NOW_DAY'		=> $this->now_day,
			'NOW_MONTH'		=> $this->language->lang(strtoupper(date('F', $this->now))),
			'NOW_YEAR'		=> $this->now_year,
			'U_ADD_EVENT'	=> $this->auth->acl_get('u_add_calendar_event') ? $this->helper->route('steve_calendar_add_event', ['action' => 'add', 'event_id' => 0]) : false,
			'VIEW_YEAR'		=> empty($months) ? true : false,
			
			'CALENDAR_ENABLE_BIRTHDAYS'		=> !empty($this->config['calendar_enable_birthdays']) ? true : false,
			'S_DISPLAY_BIRTHDAY_LIST'		=> ($this->config['load_birthdays'] && $this->config['allow_birthdays'] && $this->auth->acl_gets('u_viewprofile', 'a_user', 'a_useradd', 'a_userdel')),
		]);

		$user_bday = !empty($this->config['calendar_enable_birthdays']) ? $this->get_user_bdays($year) : [];
		$events = $this->get_events($year);
		
		if (empty($months))
		{
			for ($months = (int) 1; $months <= 12; $months++) 
			{
				$this->get_month($months, $year, $events, $user_bday, true);
			}
		}
		else
		{
			$this->get_month($months, $year, $events, $user_bday, false);	
		}
		unset($events, $user_bday);
		
		return $this;
	}
	
	public function get_user_bdays($year)
	{
		$sql = 'SELECT user_id, user_birthday
			FROM ' . USERS_TABLE;
		$result = $this->db->sql_query($sql);

		$user_bday = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (empty($row))
			{
				continue;
			}
			
			$day_m_y = !empty($row['user_birthday']) ? explode('-', $row['user_birthday']) : false;
			if ($day_m_y)
			{
				$user_bday[(string) $this->date_time->date_key($year, $day_m_y[1], $day_m_y[0], "Y-m-d")][] = $row;
			}
		}
		$this->db->sql_freeresult($result);
	
		return $user_bday;
	}
	
	public function get_birth_days($year, $now_mday, $now_mon, $show_bdays = true)
	{
		if (empty($show_bdays))
		{
			return false;
		}

		$time = $this->user->create_datetime();
		$now = phpbb_gmgetdate($time->getTimestamp() + $time->getOffset());

		// Display birthdays of 29th February on 28th February in non-leap-years
		$leap_year_birthdays = '';
		if ($now_mday == 28 && $now_mon == 2 && !$time->format('L'))
		{
			$leap_year_birthdays = " OR u.user_birthday LIKE '" . $this->db->sql_escape(sprintf('%2d-%2d-', 29, 2)) . "%'";
		}
		
		$sql_bday = "";
		if (!empty($now_mday))
		{
			$sql_bday = " AND (u.user_birthday LIKE '" . $this->db->sql_escape(sprintf('%2d-%2d-', $now_mday, $now_mon)) . "%' $leap_year_birthdays) ";
		} 
		else
		{
			$sql_bday = " AND MONTH(STR_TO_DATE(u.user_birthday, '%d-%m-%Y')) = " . $now_mon;
		}

		$sql_ary = [
			'SELECT' => 'u.user_id, u.username, u.user_colour, u.user_birthday',
			'FROM' => [
				USERS_TABLE => 'u',
			],
			'LEFT_JOIN' => [
				[
					'FROM' => [BANLIST_TABLE => 'b'],
					'ON' => 'u.user_id = b.ban_userid',
				],
			],
			'WHERE' => "(b.ban_id IS NULL OR b.ban_exclude = 1)
				$sql_bday
				AND u.user_type IN (" . USER_NORMAL . ', ' . USER_FOUNDER . ')',
		];

		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$results = $this->db->sql_query($sql);
			
		$birthdays = [];
		while ($row = $this->db->sql_fetchrow($results))
		{
			if (empty($row))
			{
				continue;
			}			
			$birthdays[] = $row;
		}
		$this->db->sql_freeresult($results);
		
		foreach($birthdays as $row)
		{
			if (empty($row))
			{
				continue;
			}			
			$birthday_year = (int) substr($row['user_birthday'], -4);
			$birthday_age = $birthday_year ? max(0, $year - $birthday_year) : '';
			
			$template_vars = [
				'USER'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'AGE'		=> $birthday_age > 0 ? $birthday_age : $birthday_year,
			];
			
			$this->template->assign_block_vars('calendar_birthdays', $template_vars);
		}
		unset($birthdays);
		
		return $this;
	}
	
	public function get_month($months, $year, $events, $birthday, $view_year = true)
	{
		if (empty($months) || empty($year))
		{
			return false;
		}

		$month_string = $this->date_time->date_key($year, $months, constants::FIRST, "F");

		$this->template->assign_block_vars('calendar', [
			'NOW_MONTH'		=> $this->now_year == $year && $this->now_month == $months ?  true : false,
			'MONTH_STRING'	=> $this->language->lang(strtoupper($month_string)),
			'MONTH'			=> $months,
			'URL'			=> $this->helper->route('steve_calendar_month', ['month' => $month_string, 'year' => $year]),
		]);
		
		$this->week_days('calendar.week_days', '_D');

		$start_day = $this->date_time->date_key($year, $months, 0, "w");

		$padding = '';
		for ($x = 0; $x < $start_day; $x++)
		{
			$padding .= '<a class="pad">&nbsp;</a>';
		}

		$this->template->assign_block_vars('calendar.months.padding', [
			'PADDING'		=> $padding,
		]);

		$days = $this->date_time->date_key($year, $months, constants::FIRST, "t");

		for ($day = constants::FIRST; $day <= $days; $day++)
		{
			$event_day = $this->date_time->date_key($year, $months, $day, "Y-m-d");

			$is_event = $event_annual = $event_bday = $expired = $world_date = false;	
			
			if(!empty($birthday) && !empty($birthday[$event_day])) 
			{
				foreach ($birthday[$event_day] as $key => $event) 
				{
					if (empty($event))
					{
						unset($birthday[$key]);
					}
					$event_bday = true;
				}
			}

			if(!empty($events) && !empty($events[$event_day])) 
			{
				foreach ($events[$event_day] as $key => $event) 
				{
					if (empty($event))
					{
						unset($events[$key]);
					}
					$is_event = true;
					$event_annual = $event['annual'] ? true : false;
					$world_date = $event['world_date'] ? true : false;
					$expired = !$event['annual'] && $event['time_stamp'] < $this->now && $this->now_day != $event['day'] && $event['year'] == $year ? true : false;
				}
			}
		
			$day_string = $this->date_time->date_key($year, $months, $day, "D");
			$now_day = $this->now_year == $year && $this->now_day == $day && $this->now_month == $months ?  true : false;//date key

			$this->template->assign_block_vars('calendar.months', [
				'DAYS' 		=> !empty($day) ? $day : false,
				'NOW_DAY'	=> $now_day,
				'TITLE'		=> $day_string,
				'URL'		=> $this->helper->route('steve_calendar_day', ['day_string' => $day_string, 'day' => $day, 'month' => $month_string, 'year' => $year]),
				
				'CLASS'		=> $this->event_class($now_day, $is_event, $event_annual, $expired),
				'ANNUAL'	=> $event_annual,
				'BIRTHDAY'	=> $event_bday,
				'WORLD_DATE'=> $world_date,

				'YR_M_D'	=> $event_day,
				'FA'		=> !empty($event['annual']) ? 'fa-calendar' : (!$expired ? 'fa-calendar-check-o' : 'fa-calendar-minus-o'),
			]);
			
			unset($events[$event_day], $birthday[$event_day]);
		}
		unset($day);
		
		return $this;
	}
	
	public function event_class($now_day, $is_event, $event_annual, $expired)
	{
		if (empty($is_event))
		{
			return false;
		}

		return (string) $now_day ? 'event-now' : (!empty($event_annual) && $is_event ? 'annual-event' : (!$expired ? 'active-event' : ($expired ? 'expired-event' : '')));
	}

	
	public function get_events($year)
	{
		if (!$this->auth->acl_get('u_view_calendar_events') || empty($year))
		{
			return false;
		}

		$sql = 'SELECT *
			FROM ' . $this->table_calendar_events . '
			WHERE year = ' . (int) $year . '
				OR annual = 1';
		$result = $this->db->sql_query($sql);

		$events = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (empty($row))
			{
				continue;
			}
			$key = !empty($row['annual']) ? $year : $row['year'];	
			$events[(string) $this->date_time->date_key($key, $row['month'], $row['day'], "Y-m-d")][] = $row;
		}
		$this->db->sql_freeresult($result);
	
		return $events;
	}

	public function get_month_events($month, $day = 0, $year, $route)
	{
		if (!$this->auth->acl_get('u_view_calendar_events') || empty($month) || empty($year))
		{
			return false;
		}
		
		$page = $this->request->variable('page', 0);
		
		$month_string = $this->db->sql_escape(strtoupper($month));
		$sql_and_day = !empty($day) ? 'AND day = ' . (int) $day : '';
		
		$sql = 'SELECT c.*, u.user_id, u.username, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height, u.user_birthday
			FROM ' . $this->table_calendar_events . ' c LEFT JOIN ' . USERS_TABLE . ' u ON c.user_id = u.user_id
			WHERE year = ' . (int) $year . '
				AND month_string = "' . $month_string . '"
				' . $sql_and_day . '
					OR ( annual = 1 
					AND month_string = "' . $month_string . '"
					' . $sql_and_day . ' )
			ORDER BY day ASC, hour ASC, minute ASC';
		$result = $this->db->sql_query_limit($sql, (int) $this->config['calendar_event_limit'], $page);
		
		$events = $this->event_ids = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (empty($row))
			{
				continue;
			}
			$this->event_ids[] = (int) $row['event_id'];
			$events[(int) $row['event_id']] = $row;
		}
		$this->db->sql_freeresult($result);
		
		$this->view_events($events, $month, $year,  $route, $day);
		
		return $this;
	}
	
	public function count_events($month, $day = 0, $year)
	{
		if (!$this->auth->acl_get('u_view_calendar_events') || empty($month) || empty($year))
		{
			return false;
		}
	
		$month_string = $this->db->sql_escape(strtoupper($month));
		$sql_and_day = !empty($day) ? 'AND day = ' . (int) $day : '';
		
		$sql = ' SELECT COUNT(event_id) AS count_events
			FROM ' . $this->table_calendar_events . '
			WHERE year = ' . (int) $year . '
				AND month_string = "' . $month_string . '"
				' . $sql_and_day . '
					OR ( annual = 1 
					AND month_string = "' . $month_string . '"
					' . $sql_and_day . ' )';
		$result = $this->db->sql_query($sql);
		$count_events = $this->db->sql_fetchfield('count_events');
		$this->db->sql_freeresult($result);

		return (int) $count_events;
	}
	
	public function get_event_attendees($year, $count_events)
	{
		if (!$this->auth->acl_get('u_view_calendar_events') || empty($this->event_ids) || empty($year) || empty($count_events))
		{
			return false;
		}
		
		$sql = 'SELECT c.*, u.user_id, u.username, u.user_colour
			FROM ' . $this->table_events_attending  . ' c LEFT JOIN ' . USERS_TABLE . ' u ON c.user_id = u.user_id
			WHERE ' . $this->db->sql_in_set('c.event_id', array_unique($this->event_ids)) . '
				AND year = ' . (int) $year . '
			ORDER BY u.username ASC';
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
	
	public function upcoming_events($day = 0)
	{
		$page = $this->request->variable('page', 0);
		
		$month_string = $this->db->sql_escape(strtoupper(date('F', $this->now)));
		
		$sql_and_day = !empty($day) ? 'AND day = ' . (int) $day : '';

		$sql = 'SELECT c.*, u.user_id, u.username, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height, u.user_birthday
			FROM ' . $this->table_calendar_events . ' c LEFT JOIN ' . USERS_TABLE . ' u ON c.user_id = u.user_id
			WHERE year = ' . (int) $this->now_year . '
				AND month_string = "' . $month_string . '"
				' . $sql_and_day . '
					OR ( annual = 1 
					AND month_string = "' . $month_string . '"
					' . $sql_and_day . ' )
			ORDER BY day ASC, hour ASC, minute ASC';
		$results = $this->db->sql_query_limit($sql, intval($this->config['calendar_event_limit']), $page);
		
		$events = [];
		while ($row = $this->db->sql_fetchrow($results))
		{
			if (empty($row))
			{
				continue;
			}			
			$events[(int) $row['event_id']] = $row;
		}
		$this->db->sql_freeresult($results);
		
		$this->view_events($events, 0, $this->now_year, $route = '', $day);
		
		return $this;
	}
	
	public function view_events($events, $month, $year, $route, $day)
	{
		if (empty($events) || empty($year))
		{
			return false;
		}

		$count_events = $this->count_events($month, !empty($day) ? $day : (int) 0, $year);
		$attendees = $this->get_event_attendees($year, $count_events);

		if (!empty($events))
		{
			foreach ($events as $event)
			{
				if (empty($event))
				{
					continue;
				}

				if (!empty($event['annual']))
				{
					$time_stamp = $this->date_time->event_timestamp($year, $event['month'], $event['day'], $event['hour'], $event['minute']);
				}

				$time_stamp = empty($event['annual']) ? $event['time_stamp'] : $time_stamp;
				$expired = $event['time_stamp'] < $this->now && $this->now_day != $event['day'] && $event['year'] == $year ? true : false;
				$year_js = empty($event['annual']) ? $event['year'] : $year;
				
				$now_day = $this->now_year == $year_js && $this->now_day == $event['day'] && $this->now_month == $event['month'] ?  true : false;
								
				$day_string = $this->date_time->date_key($year_js, $event['month'], $event['day'], "D");

				$month_string = $this->date_time->date_key($year_js, $event['month'], constants::FIRST, "F");
				$event_route = $this->helper->route('steve_calendar_day', ['day_string' => $day_string, 'day' => $event['day'], 'month' => $month_string, 'year' => $year_js]) . '#event-' . $event['event_id'];

				$template_vars = [
					'EVENT_ID'			=> $event['event_id'],
					
					'TIME_ZONE'			=> $event['time_zone'],
					
					'USER_AVATAR'		=> get_user_avatar($event['user_avatar'], $event['user_avatar_type'], $event['user_avatar_width'], $event['user_avatar_height']),
					'USER_NAME'			=> get_username_string('full', $event['user_id'], $event['username'], $event['user_colour']),
					'USER_AVATAR_URL'	=> get_username_string('profile', $event['user_id'], $event['username'], $event['user_colour']),
					
					'TIME_STAMP' 		=> $this->user->format_date($time_stamp),
					'TIME_STAMP_END' 	=> $event['time_stamp_end'] > (int) 1 ? $this->user->format_date($event['time_stamp_end']) : '',
				
					//days left VV
					'EVENT_PERIOD_DAYS'	=> $event['year_end'] > (int) 0 ? $this->language->lang('EVENT_PERIOD_DAYS',  $this->date_time->count_days($event['year_end']. '-' . $event['month_end'] . '-' . $event['day_end'], $event['year'],$event['month'],$event['day'])) : '',
					'EVENT_START_DAYS'	=> !$expired && $event['year'] >= $this->now_year ? $this->language->lang('EVENT_START_DAYS', $this->date_time->count_days($this->now_year . '-' . $this->now_month . '-' . $this->now_day, $event['year'], $event['month'], $event['day'])) : '',
					//days left ^^
					
					'DAY'         		=> $this->date_time->date_key($year_js, $event['month'], $event['day'], "jS"),
					'DAY_STRING'  		=> $this->language->lang(empty($event['annual']) ? $event['day_string'] : $this->date_time->date_key($year, $event['month'], $event['day'], "l")),
					
					'CLASS'				=> $now_day ? 'event-now' : (!empty($event['annual']) ? 'annual-event' : (!$expired ? 'active-event' : ($expired ? 'expired-event' : ''))),	
					'FA'				=> !empty($event['annual']) ? 'fa-calendar' : (!$expired ? 'fa-calendar-check-o' : 'fa-calendar-minus-o'),
					
					'BIRTHDAY'			=> !empty($event['birthday']) ? true : false,
					'WORLD_DATE'		=> !empty($event['world_date']) ? true : false,
					
					'TITLE'				=> censor_text($event['title']),
					'INFORMATION'		=> $this->information($event, true),
					
					'U_EVENT'			=> $event_route,
					
					'U_ATTEND'			=> $this->auth->acl_get('u_attend_calendar_event') && !$expired ? $this->helper->route('steve_calendar_attend_event', ['action' => 'attend', 'event_id' => $event['event_id'], 'year' => $year_js, 'hash' => generate_link_hash('attend')]) : false,
					'U_UNATTEND'		=> $this->auth->acl_get('u_unattend_calendar_event') ? $this->helper->route('steve_calendar_attend_event', ['action' => 'unattend', 'event_id' => $event['event_id'], 'year' => $year_js, 'hash' => generate_link_hash('unattend')]) : false,
					'U_DELETE'			=> $this->u_event_action('u_delete_calendar_event', $event['user_id']) ? $this->helper->route('steve_calendar_delete_event', ['event_id' => $event['event_id'], 'hash' => generate_link_hash('delete')]) : false,
					'U_EDIT'			=> $this->u_event_action('u_edit_calendar_event', $event['user_id']) ? $this->helper->route('steve_calendar_add_event', ['action' => 'edit', 'event_id' => $event['event_id']]) : false,
				];
				
				$this->template->assign_block_vars('events', $template_vars);
				
				if (!empty($attendees[$event['event_id']]))
				{
					foreach ($attendees[$event['event_id']] as $attendee)
					{
						$template_vars = [
							'USER_NAME'			=> get_username_string('full', $attendee['user_id'], $attendee['username'], $attendee['user_colour']),
							'U_UNATTEND'		=> !$expired && $attendee['user_id'] == $this->user->data['user_id'] && $this->auth->acl_get('u_unattend_calendar_event') ? $this->helper->route('steve_calendar_attend_event', ['action' => 'unattend', 'event_id' => $event['event_id'], 'year' => $year_js, 'hash' => generate_link_hash('unattend')]) : false,			
							'U_ATTENDED'		=> $expired && !$attendee['attended'] && $attendee['user_id'] == $this->user->data['user_id'] && $this->auth->acl_get('u_attend_calendar_event') ? $this->helper->route('steve_calendar_attend_event', ['action' => 'attended', 'event_id' => $event['event_id'], 'year' => $year_js, 'hash' => generate_link_hash('attended')]) : false,						
						];
						
						$this->template->assign_block_vars(!$attendee['attended'] ? 'events.attendees' : 'events.attended', $template_vars);
					}
					unset($attendees[$event['event_id']]);
				}
			}
			unset($events, $attendees);

			$this->pagination->generate_template_pagination($route, 'pagination', 'page', $count_events, (int) $this->config['calendar_event_limit'], $this->request->variable('page', 0));
			
			$this->template->assign_vars([
				'NOW_DAY'		=> $this->now_day,
				'NOW_MONTH'		=> $this->language->lang(strtoupper($month_string)),
				'NOW_YEAR'		=> $this->now_year,
				'PAGE_NUMBER'	=> $count_events,
			]);		
		}

		return $this;
	}

	public function u_event_action($auth_action, $user_id)
	{
		return $this->auth->acl_get($auth_action) && ($user_id == $this->user->data['user_id'] || $user_id != $this->user->data['user_id'] && $this->auth->acl_get('a_')) ? true : false;
	}

	public function week_days($block, $prefix)
	{	
		$week_days = new constants;
		$week_days = $week_days->days_to_array(true);
		
		foreach ($week_days as $week_day)
		{
			$this->template->assign_block_vars($block, [
				'DAYS'		=> $this->language->lang($week_day . $prefix),
			]);
		}
		unset($week_days);
		
		return $this;
	}

	public function flags()
	{
		return ($this->config['allow_bbcode'] ? OPTION_FLAG_BBCODE : false) + ($this->config['allow_smilies'] ? OPTION_FLAG_SMILIES : false) + ($this->config['allow_post_links'] ? OPTION_FLAG_LINKS : false);
	}

	public function information($event, $truncate = false)
	{
		return (string) generate_text_for_display($event['information'], $event['bbcode_uid'], $event['bbcode_bitfield'], $this->flags(), true);
	}
}
