<?php
/**
	* Events calendar $extends the phpBB Software package.
	* @copyright (c) 2024, Steve, https://steven-clark.tech/
	* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace steve\calendar\controller;

class attend_event
{
	protected $auth;
	protected $config;
	protected $db;
	protected $helper;
	protected $language;
	protected $notification_manager;
	protected $request;
	protected $user;
	
	protected $date;
	protected $calendar_events;
	protected $calendar_events_attending;
	
	protected $action;
	protected $event_id;
	protected $year;
	
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,		
		\phpbb\db\driver\driver_interface $db,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language,
		\phpbb\notification\manager $notification_manager,
		\phpbb\request\request $request,
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
		$this->request = $request;
		$this->user = $user;
		
		$this->date_time = $date;
		$this->table_calendar_events = $calendar_events;
		$this->table_events_attending = $calendar_events_attending;

 		if (empty($this->config['calendar_enabled']))
		{
			throw new \phpbb\exception\http_exception(404, 'CALENDAR_DISABLED');
		}
	}

	public function attend($action, $event_id, $year)
	{
		$this->action = $action;
		$this->event_id = (int) $event_id;
		$this->year = (int) $year;
		
		if (!in_array($action, ['attend', 'unattend', 'attended']) || !check_link_hash($this->request->variable('hash', ''), $action) 
			|| ($action == 'attend' ? !$this->auth->acl_get('u_attend_calendar_event') : !$this->auth->acl_get('u_unattend_calendar_event')))
		{
			throw new \phpbb\exception\http_exception(403, 'NO_AUTH_OPERATION');
		}
		
		$this->event_data = $this->get_event();
		$this->get_event_attendees($this->user->data['user_id']);
		
		switch ($action)
		{
			case $action == 'attend':
				$this->add_attendee();
				$message = 'ATTENDANCE_ADDED';
			break;
			case $action == 'attended':
				$this->attended();
				$message = 'ATTENDANCE_ATTENDED';
			break;
			case $action == 'unattend':
				$this->delete_attendee();
				$message = 'ATTENDANCE_REMOVED';
			break;
		}
	
		return $this->response($message);
	}

	private function response($message)
	{
		if (!$this->request->is_ajax())
		{
			throw new \phpbb\exception\http_exception(500, 'GENERAL_ERROR');
		}
		
		$month_string = $this->date_time->date_key($this->year, $this->event_data['month'], 1, "F");
		$day_string = $this->date_time->date_key($this->year, $this->event_data['month'], $this->event_data['day'], "D");
					
		$data = [
			'MESSAGE_TITLE'	=> $this->language->lang('INFORMATION'),
			'MESSAGE_TEXT'	=> $this->language->lang($message),
			'REFRESH_DATA'	=> [
				'time'		=> 3,
				'url'		=> $this->helper->route('steve_calendar_day', [
					'day_string'=> $day_string, 
					'day' 		=> $this->event_data['day'], 
					'month' 	=> $month_string, 
					'year' 		=> $this->year
				])
		]];
		
		$json_response = new \phpbb\json_response;
		return $json_response->send($data);	
	}
	
	private function delete_attendee()
	{
		if (empty($this->event_id))
		{
			return false;
		}
				
		$sql = 'DELETE FROM ' . $this->table_events_attending . "
			WHERE event_id = $this->event_id
			AND user_id = " . (int) $this->user->data['user_id'] . "
				AND year = $this->year";
		$this->db->sql_query($sql);

		$this->notification_manager->delete_notifications('steve.calendar.notification.type.upcoming_event', [
			'item_id'			=> $this->event_id,
			'item_partent_id'	=> $this->year,
		]);
		
		return $this;		
	}
	
	private function add_attendee()
	{
		$sql_ary = [
			'event_id'        	=> (int) $this->event_id,
			'user_id'     		=> (int) $this->user->data['user_id'],
			'time_stamp'        => (int) $this->event_data['time_stamp'],
			'year'				=> (int) $this->year,
			'annual'			=> (bool) $this->event_data['annual'],
			'title'				=> (string) $this->event_data['title'],
		];
					
		$sql = 'INSERT INTO ' . $this->table_events_attending . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
		$this->db->sql_query($sql);

		return $this;
	}
	
	private function attended()
	{
		$sql = 'UPDATE ' . $this->table_events_attending . "
			SET time_stamp = " . time() . ",
			attended = 1
			WHERE event_id = " . $this->event_id . "
				AND user_id = " . $this->user->data['user_id'];
		$this->db->sql_query($sql);
		
		return $this;
	}
	
	private function get_event()
	{
		if (empty($this->event_id))
		{
			return false;
		}
		
		$event_data = [];
		$sql = 'SELECT *
			FROM ' . $this->table_calendar_events . "
			WHERE event_id = $this->event_id";
		$result = $this->db->sql_query($sql);
		$event_data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
	
		if (empty($this->event_id) || empty($event_data))
		{
			throw new \phpbb\exception\http_exception(404, 'EVENT_NOT_FOUND');
		}
				
		return $event_data;
	}
	
	private function get_event_attendees($user_id)
	{
		if (empty($this->event_id))
		{
			return false;
		}
		
		$attendees = [];
		$sql = 'SELECT *
			FROM ' . $this->table_events_attending  . "
			WHERE event_id = $this->event_id
				AND user_id = " . (int) $user_id . "
				AND year = $this->year";
		$result = $this->db->sql_query($sql);
		$attendees = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
			
		if (($this->action == 'attend' && !empty($attendees)) || ($this->action == 'unattend' && empty($attendees)))
		{
			throw new \phpbb\exception\http_exception(404, $this->action == 'attend' ? 'ATTENDEE_FOUND' : 'LANG_VAR_THAT_DROPPED_OFF_THE_FACE_OF_THE_PLANET');
		}
			
		return $attendees;
	}	
}
