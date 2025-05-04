<?php
/**
	* Events calendar $extends the phpBB Software package.
	* @copyright (c) 2024, Steve, https://steven-clark.tech/
	* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace steve\calendar\notification\type;

class upcoming_event extends \phpbb\notification\type\base
{
	public function get_type()
	{
		return 'steve.calendar.notification.type.upcoming_event';
	}

	public static $notification_option = [
		'lang'	=> 'NOTIFICATION_TYPE_UPCOMING_EVENT',
		'group'	=> 'NOTIFICATION_GROUP_CALENDAR_EVENTS',
	];
	
	protected $helper;
	protected $user_loader;
	protected $config;
		
	public function set_config(\phpbb\config\config $config)
	{
		$this->config = $config;
	}

	public function set_user_loader(\phpbb\user_loader $user_loader)
	{
		$this->user_loader = $user_loader;
	}

	public function set_controller_helper(\phpbb\controller\helper $helper)
	{
		$this->helper = $helper;
	}

	public function is_available()
	{
		return (bool) $this->config['calendar_enabled'] && $this->config['calendar_enable_notifications'];
	}

	public static function get_item_id($data)
	{
		return (int) $data['item_id'];
	}

	public static function get_item_parent_id($data)
	{
		return (int) $data['year'];
	}

	public function find_users_for_notification($data, $options = [])
	{
		$options = array_merge([
			'ignore_users'		=> [],
		], $options);
		
		if (empty($data['users']))
		{
			return [];
		}
			
		$users = explode(',', $data['users']);

		return $this->check_user_notification_options($users, $options);
	}

	public function users_to_query()
	{
		return [$this->get_data('user_id')];
	}

 	public function get_avatar()
	{
		return (string) $this->user_loader->get_avatar($this->get_data('user_id'), true, true);
	}

	public function get_title()
	{
		return (string) $this->language->lang('NOTIFICATION_UPCOMING_EVENT', $this->get_data('title'), $this->user->format_date($this->get_data('time_stamp')));
	}

	public function get_url()
	{
		return (string) $this->get_data('url');
	}

	public function get_email_template()
	{
		return '@steve_calendar/upcoming_event';
	}

	public function get_email_template_variables()
	{
		return [
			'URL' 		=> generate_board_url(true) . $this->get_data('url'),
			'TITLE'		=> htmlspecialchars_decode($this->get_data('title')),
		];
	}

	public function create_insert_array($data, $pre_create_data = [])
	{
		$this->set_data('event_id', $data['event_id']);
		$this->set_data('year', $data['year']);
		$this->set_data('user_id', $data['user_id']);
		$this->set_data('users', $data['users']);
		$this->set_data('url', $data['url']);
		$this->set_data('title', $data['title']);
		$this->set_data('time_stamp', $data['time_stamp']);	

		parent::create_insert_array($data, $pre_create_data);
	}
}
