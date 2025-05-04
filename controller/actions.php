<?php
/**
	* Events calendar $extends the phpBB Software package.
	* @copyright (c) 2024, Steve, https://steven-clark.tech/
	* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace steve\calendar\controller;

use steve\calendar\calendar\constants;

class actions
{
	protected $auth;
	protected $config;
	protected $db;
	protected $helper;
	protected $language;
	protected $notification_manager;
	protected $request;
	protected $template;
	protected $user;
	//
	protected $posting;
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
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		//
		\steve\calendar\calendar\event_posting $posting,
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
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		//
		$this->posting = $posting;
		$this->date_time = $date;
		$this->table_calendar_events = $calendar_events;
		$this->table_events_attending = $calendar_events_attending;
	}
	
	public function delete_event($event_id)
	{
		if (!check_link_hash($this->request->variable('hash', ''), 'delete'))
		{
			throw new \phpbb\exception\http_exception(403, 'NO_AUTH_OPERATION');
		}
		
		$event_data = $this->posting->get_event($event_id);
		if (empty($event_id) || empty($event_data))
		{
			throw new \phpbb\exception\http_exception(404, 'EVENT_EMPTY');
		}
		
		if (!$this->posting->u_event_action('u_delete_calendar_event', $event_data['user_id']))
		{
			throw new \phpbb\exception\http_exception(403, 'NO_AUTH_DELETE_EVENT');
		}
			
		if ($this->request->is_ajax())
		{
			$sql = 'DELETE FROM ' . $this->table_events_attending . '
				WHERE event_id = ' . (int) $event_id;
			$this->db->sql_query($sql);
			
			$sql = 'DELETE FROM ' . $this->table_calendar_events . '
				WHERE event_id = ' . (int) $event_id;
			$this->db->sql_query($sql);

			$this->notification_manager->delete_notifications('steve.calendar.notification.type.upcoming_event', ['item_id'	=> $event_id,]);
			
			$data = [
				'EVENT_ID'		=> $event_id,
				'MESSAGE_TITLE'	=> $this->language->lang('INFORMATION'),
				'MESSAGE_TEXT'	=> $this->language->lang('EVENT_DELETED'),
			];
			
			$json_response = new \phpbb\json_response;
			return $json_response->send($data);
		}

		return $this;
	}
	
	public function event_actions($action, $event_id)
	{
		if (empty($action) || !in_array($action, array('add', 'edit')) || ($action == 'add' && !empty($event_id)) || ($action == 'edit' && empty($event_id)))
		{
			throw new \phpbb\exception\http_exception(403, 'INVALID_EVENT_ACTION');
		}
		
 		if ($action == 'add' && !$this->auth->acl_get('u_add_calendar_event'))
		{
			throw new \phpbb\exception\http_exception(403, 'NO_AUTH_OPERATION');
		}
		
		add_form_key('postform');
		
		$form_data = [
			'title' 		=> utf8_normalize_nfc($this->request->variable('title', '', true)),
			
			'year' 			=> $this->request->variable('year', 0),			
			'month_string' 	=> $this->request->variable('month_string', ''),
			'day'			=> $this->request->variable('day', 0),
			'hour'		 	=> $this->request->variable('hour', 0),
			'minute' 		=> $this->request->variable('minute', 0),
			
			'year_end' 			=> $this->request->variable('year_end', 0),
			'month_string_end' 	=> $this->request->variable('month_string_end', ''),
			'day_end'			=> $this->request->variable('day_end', 0),
			'hour_end'		 	=> $this->request->variable('hour_end', 0),
			'minute_end' 		=> $this->request->variable('minute_end', 0),			
						
			'tz'			=> $this->request->variable('tz', ''),
			'annual' 		=> $this->request->variable('annual', false),
			'world_date'	=> $this->request->variable('world_date', false),
			'birthday'		=> $this->request->variable('birthday', false),		
			'attend' 		=> $this->request->variable('attend', false),

			'information' 	=> utf8_normalize_nfc($this->request->variable('information', '', true)),
		];
		
		$month = date("m", strtotime($form_data['month_string']));
		$month_string = strtoupper($form_data['month_string']);
		$year = $this->date_time->date_key($form_data['year'], $month, $form_data['day'], "Y");
		$day_string = strtoupper($this->date_time->date_key($year, $month, $form_data['day'], "l"));
		
		$month_end = date("m", strtotime($form_data['month_string_end']));
		$month_string_end = strtoupper($form_data['month_string_end']);
		$year_end = !empty($form_data['year_end']) ? $this->date_time->date_key($form_data['year_end'], $month_end, $form_data['day_end'], "Y") : 0;
		$day_string_end = strtoupper($this->date_time->date_key($year_end, $month_end, $form_data['day_end'], "l"));
		
		if ($action == 'edit')
		{
			$event_data = $this->posting->get_event($event_id);
			if (empty($event_data))
			{
				throw new \phpbb\exception\http_exception(404, 'EVENT_NOT_FOUND');
			}			
			if (!$this->posting->u_event_action('u_delete_calendar_event', $event_data['user_id']))
			{
				throw new \phpbb\exception\http_exception(403, 'NO_AUTH_AUTH_EVENT_ACTION');
			}
			
			$sql = 'SELECT *
				FROM ' . $this->table_events_attending  . '
				WHERE event_id = ' . (int) $event_id . '
					AND user_id = ' . (int) $event_data['user_id'];
			$result = $this->db->sql_query($sql);
			$attendee = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);			
		}
		
		$error = [];
		if ($this->request->is_set_post('submit'))
		{		
			$error = $this->posting->validate_form($error, $form_data, $month);
			
			$time_stamp = $this->date_time->event_timestamp($year, $month, $form_data['day'], $form_data['hour'], $form_data['minute']);
			$time_stamp_end = !empty($form_data['year_end']) ? $this->date_time->event_timestamp($form_data['year_end'], $month_end, $form_data['day_end'], $form_data['hour_end'], $form_data['minute_end']) : 0;
			
			$uid = $bitfield = $options = '';
			$topic_message = $form_data['information'];

			if (empty($error))
			{
				generate_text_for_storage($form_data['information'], $uid, $bitfield, $options, true, true, true, true);
				
				$sql_ary = [];
				if ($action == 'add')
				{
					$sql_ary += [
						'user_id'		=> (int) $this->user->data['user_id'],
					];
				}
				
				$sql_ary += [
					'title'				=> (string) $form_data['title'],
					
					'time_stamp' 		=> $time_stamp,
					'time_zone'			=> (string) $form_data['tz'],
					'hour'				=> (int) $form_data['hour'],
					'minute'			=> (int) $form_data['minute'],
					'day'         		=> (int) $form_data['day'],
					'day_string'  		=> (string) $day_string,
					'month'				=> (int) $month,
					'month_string'		=> (string) $month_string,
					'year'				=> (int) $year,
					
					'time_stamp_end' 	=> $time_stamp_end,
					'hour_end'			=> (int) $form_data['hour_end'],
					'minute_end'		=> (int) $form_data['minute_end'],
					'day_end'         	=> (int) $form_data['day_end'],
					'day_string_end'  	=> (string) $day_string_end,
					'month_end'			=> (int) $month_end,
					'month_string_end'	=> (string) $month_string_end,
					'year_end'			=> (int) $year_end,					
					
					'duration'			=> (int) 0,
					
					'annual'			=> (bool) !empty($form_data['annual']) ? $form_data['annual'] : (empty($form_data['annual']) && !empty($form_data['world_date']) ? true : false),
					'birthday'			=> (bool) $form_data['birthday'],
					'world_date'		=> (bool) $form_data['world_date'],

					'information'		=> (string) $form_data['information'],
					'bbcode_uid'		=> (string) $uid,
					'bbcode_bitfield'	=> (string) $bitfield,
				];
					
				$this->db->sql_transaction('begin');
				
				if ($action == 'add')
				{
					$sql = 'INSERT INTO ' . $this->table_calendar_events . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
					$this->db->sql_query($sql);
					$next_id = $this->db->sql_nextid();
					
					if (!empty($form_data['attend']) && $time_stamp > time())
					{
						$sql_attend = [
							'event_id'        	=> (int) $next_id,
							'user_id'     		=> (int) $this->user->data['user_id'],
							'time_stamp'        => (int) $time_stamp,
							'year'				=> (int) $year,
							'annual'			=> (bool) $form_data['annual'],
							'title'				=> (string) $form_data['title'],
						];
						
						$sql = 'INSERT INTO ' . $this->table_events_attending . ' ' . $this->db->sql_build_array('INSERT', $sql_attend);
						$this->db->sql_query($sql);
					}		
				}
				else
				{
					if (!empty($event_data['annual']) && empty($form_data['annual']))
					{
						$sql = 'DELETE FROM ' . $this->table_events_attending . '
							WHERE event_id = ' . (int) $event_id . ' 
								AND year <> ' . (int) $year;
						$this->db->sql_query($sql);

						$this->notification_manager->delete_notifications('steve.calendar.notification.type.upcoming_event', [
							'item_id'			=> $event_id,
							//'item_partent_id'	=> $event_id,
						]);
					}
					
					if (empty($form_data['attend']) && !empty($attendee['user_id']))
					{
						$event_years = !empty($event_data['annual']) && empty($form_data['annual']) ? $year : $event_data['year'];
						$sql = 'DELETE FROM ' . $this->table_events_attending . '
							WHERE event_id = ' . (int) $event_id . '
							AND user_id = ' . (int) $attendee['user_id'] . '
								AND year = ' . (int) $event_years;
						$this->db->sql_query($sql);

						$this->notification_manager->delete_notifications('steve.calendar.notification.type.upcoming_event', [
							'item_id'			=> $event_id,
							'item_partent_id'	=> $event_data['year'],
						]);
					}
					
					$sql_arry = [
						'time_stamp' 		=> (int) $time_stamp,
						'year'				=> (int) $year,
						'annual'			=> (bool) $form_data['annual'],
						'title'				=> (string) $form_data['title'],
					];					

					$sql = 'UPDATE ' .  $this->table_events_attending  . ' 
						SET ' . $this->db->sql_build_array('UPDATE', $sql_arry) . '
						WHERE event_id = ' . (int) $event_id;
					$this->db->sql_query($sql);
					
					$sql = 'UPDATE ' .  $this->table_calendar_events . ' 
						SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
						WHERE event_id = ' . (int) $event_id;
					$this->db->sql_query($sql);
				}
				
				$route_year = !empty($year) ? $year : date("Y");
				$route = $this->helper->route('steve_calendar_day', [
					'day_string'	=> $this->date_time->date_key($route_year, $month, $form_data['day'], "D"),
					'day'			=> $form_data['day'],
					'month' 		=> $this->date_time->date_key($route_year, $month, $form_data['day'], "F"),
					'year' 			=> !empty($form_data['year']) ? $form_data['year'] : date("Y"),
				]);
				
				if (!empty($this->config['calendar_event_post_enable']) && $action == 'add')
				{
					$day_js = $this->date_time->date_key($route_year, $month, $form_data['day'], "jS");
					$url = $this->posting->add_topic($this->language->lang('EVENT_PREFIX', $form_data['title']), $this->posting->topic_message($year, $month, $month_string, $day_js, $route, $topic_message));
				}
				
				$this->db->sql_transaction('commit');

				meta_refresh(3, $route);
				throw new \phpbb\exception\http_exception(200, $action == 'add' ? 'EVENT_ADDED' : 'EVENT_EDITED', [$route]);
			}
		}
		
		if ($action === 'edit' && empty($error))
		{
			$information = $event_data['information'];
			decode_message($information, $event_data['bbcode_uid']);
		}
		
		$months = new constants;
		phpbb_timezone_select($this->template, $this->user, isset($event_data['time_zone']) ? $event_data['time_zone'] : (isset($form_data['tz']) ? $form_data['tz'] : ''), true);
		
		$this->template->assign_vars([
			'CALENDAR'			=> true,
			
			'TITLE'				=> isset($event_data['title']) ? $event_data['title'] : (isset($form_data['title']) ? $form_data['title'] : ''),
			
			'YEAR'				=> isset($event_data['year']) ? $event_data['year'] : (isset($form_data['year']) ? $form_data['year'] : ''),
			'YEAR_END'			=> isset($event_data['year_end']) ? $event_data['year_end'] : (isset($form_data['year_end']) ? $form_data['year_end'] : ''),			
			'DAY'         		=> isset($event_data['day']) ? $event_data['day'] : (isset($form_data['day']) ? $form_data['day'] : ''),
			'DAY_END'         	=> isset($event_data['day_end']) ? $event_data['day_end'] : (isset($form_data['day_end']) ? $form_data['day_end'] : ''),
			
			'S_MONTH_OPTION'	=> $this->posting->select_month_options($this->language->lang('SELECT_MONTH'), $months->months_to_array(true), isset($event_data['month_string']) ? $event_data['month_string'] : (isset($form_data['month_string']) ? $form_data['month_string'] : '')),
			'S_HOUR_OPTIONS'	=> $this->posting->select_hrs_mins($this->language->lang('SELECT'), (int) 24, isset($event_data['hour']) ? date("h:i a",  strtotime("{$event_data['hour']}:00")) : $form_data['hour'], true),
			'S_MIN_OPTIONS'		=> $this->posting->select_hrs_mins($this->language->lang('SELECT'), (int) 60, isset($event_data['minute']) ? $event_data['minute'] : $form_data['minute'], false),			
			
			'S_MONTH_END_OPTION'	=> $this->posting->select_month_options($this->language->lang('SELECT_MONTH'), $months->months_to_array(true), isset($event_data['month_string_end']) ? $event_data['month_string_end'] : (isset($form_data['month_string_end']) ? $form_data['month_string_end'] : '')),
			'S_HOUR_END_OPTIONS'	=> $this->posting->select_hrs_mins($this->language->lang('SELECT'), (int) 24, isset($event_data['hour_end']) ? date("h:i a",  strtotime("{$event_data['hour_end']}:00")) : $form_data['hour_end'], true),
			'S_MIN_END_OPTIONS'		=> $this->posting->select_hrs_mins($this->language->lang('SELECT'), (int) 60, isset($event_data['minute_end']) ? $event_data['minute_end'] : $form_data['minute_end'], false),			
			
			'ANNUAL'			=> !empty($event_data['annual']) ? $event_data['annual'] : (!empty($form_data['annual']) ? $form_data['annual'] : ''),
			'BIRTHDAY'			=> !empty($event_data['birthday']) ? $event_data['birthday'] : (!empty($form_data['birthday']) ? $form_data['birthday'] : ''),
			'WORLD_DATE'		=> !empty($event_data['world_date']) ? $event_data['world_date'] : (!empty($form_data['world_date']) ? $form_data['world_date'] : ''),
			
			'ATTEND'			=> !empty($attendee['user_id']) ? true : (!empty($form_data['attend']) ? $form_data['attend'] : ''),
					
			'INFORMATION'		=> isset($information) ? $information : (isset($form_data['information']) ? $form_data['information'] : ''),			
		]);
		
		$this->posting->template_vars($this->helper->route('steve_calendar_add_event', ['action' => $action, 'event_id' => $event_id]), $error);

		return $this->helper->render('calendar_actions.html', $this->language->lang('ADD_EVENT'));		
	}
}
