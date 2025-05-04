<?php
/**
	* Events calendar $extends the phpBB Software package.
	* @copyright (c) 2024, Steve, https://steven-clark.tech/
	* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace steve\calendar\event;
 
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	protected $auth;
	protected $config;
	protected $helper;
	protected $language;
	protected $template;
	protected $php_ext;
	
	protected $calendar;
	
	protected $operator;

	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language,
		\phpbb\template\template $template,
		$php_ext,
		
		\steve\calendar\calendar\calendar $calendar,
		\phpbb\collapsiblecategories\operator\operator $operator = null)
	{		
		$this->auth = $auth;
		$this->config = $config;
		$this->helper = $helper;
		$this->language = $language;
		$this->template = $template;
		$this->php_ext = $php_ext;

		$this->calendar = $calendar;
		$this->operator = $operator;
	}
	
	static public function getSubscribedEvents()
	{
		return [
			'core.user_setup'							=> 'load_language_on_setup',
			'core.permissions'							=> 'add_permission',
			'core.page_header'							=> 'add_page_header_link',	
			'core.viewonline_overwrite_location'		=> 'viewonline_page',
			'core.index_modify_page_title'				=> 'index_modify_page_title'
		];
	}
	
	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name' => 'steve/calendar',
			'lang_set' => 'calendar_all',
		];
		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function add_permission($event)
	{		
 		$event['categories'] = array_merge($event['categories'], [
			'calendar'				=> 'ACL_CAT_CALENDAR',
		]);

		$permissions = [
			'u_view_calendar' 				=> ['lang' => 'ACL_U_VIEW_CALENDAR', 			'cat' => 'calendar'],
			'u_view_calendar_events' 		=> ['lang' => 'ACL_U_VIEW_CALENDAR_EVENTS', 	'cat' => 'calendar'],
			'u_calendar_search'				=> ['lang' => 'ACL_U_SEARCH_CALENDAR',			'cat' => 'calendar'],
			'u_add_calendar_event' 			=> ['lang' => 'ACL_U_ADD_CALENDAR_EVENT', 		'cat' => 'calendar'],
			'u_delete_calendar_event'	 	=> ['lang' => 'ACL_U_DELETE_CALENDAR_EVENT', 	'cat' => 'calendar'],			
			'u_edit_calendar_event' 		=> ['lang' => 'ACL_U_EDIT_CALENDAR_EVENT', 		'cat' => 'calendar'],
			'u_attend_calendar_event'		=> ['lang' => 'ACL_U_ATTEND_CALENDAR_EVENT',	'cat' => 'calendar'],
			'u_unattend_calendar_event'		=> ['lang' => 'ACL_U_UNATTEND_EVENT',			'cat' => 'calendar'],
		];

		$event['permissions'] = array_merge($event['permissions'], $permissions);
	}
	
	public function add_page_header_link()
	{
 		if (!$this->auth->acl_get('u_view_calendar') || empty($this->config['calendar_enabled']))
		{
			return;
		}

		$day = $this->helper->route('steve_calendar_day', ['day_string' => date("D"), 'day'	=> date("d"), 'month' => date('F'), 'year' => date("Y")]);
		$month = $this->helper->route('steve_calendar_month', ['month' => date("F"), 'year' => date("Y")]);
		$year = $this->helper->route('steve_calendar_year', ['year' => date("Y")]);
			
		$calendar_default_link = $year;

		switch ($calendar_default_link)
		{
			case $this->config['calendar_default_link'] === 'YEAR':
				$calendar_default_link = $year;
			break;
			case $this->config['calendar_default_link'] === 'MONTH':
				$calendar_default_link = $month;
			break;
			case $this->config['calendar_default_link'] === 'DAY':
				$calendar_default_link = $day;
			break;
		}
		
		$u_search = $this->auth->acl_get('u_calendar_search') && $this->auth->acl_get('u_search') && $this->config['load_search'] ? true : false;

		$this->template->assign_vars([
			'U_VIEW_CALENDAR'		=> $calendar_default_link,
			'U_ADD_EVENT'			=> $this->auth->acl_get('u_add_calendar_event') ? $this->helper->route('steve_calendar_add_event', ['action' => 'add', 'event_id' => 0]) : false,
			'THIS_MONTH_NUM'		=> date('n'),
			'U_SEARCH_CALENDAR'		=> $u_search ? $this->helper->route('steve_calendar_search', ['action' => 'results']) : false,
			'CALENDAR_YEAR'			=> date('Y'),
			'CALENDAR_ABOVE_FORUMS'	=> !empty($this->config['calendar_above_forums_index']) ? true : false,
		]);
		
		if ($this->operator !== null)
		{
			$this->template->assign_vars([
				'S_CALENDAR_HIDDEN' 		=> in_array('calendar_on_index', $this->operator->get_user_categories()),
				'U_CALENDAR_COLLAPSE_URL' 	=> $this->helper->route('phpbb_collapsiblecategories_main_controller', ['forum_id' => 'calendar_on_index', 'hash' => generate_link_hash("collapsible_calendar_on_index")]),
			]);
		}		
	}

	public function viewonline_page($event)
	{
 		if (!$this->auth->acl_get('u_view_calendar') || empty($this->config['calendar_enabled']))
		{
			return;
		}

		if ($event['on_page'][1] == 'app' && strrpos($event['row']['session_page'], 'app.' . $this->php_ext . '/Calendar') === 0)
		{
			$event['location'] = $this->language->lang('VIEWING_CALENDAR');
			$event['location_url'] = $event['row']['session_page'];
		}
	}

	public function index_modify_page_title()
	{
 		if (!$this->auth->acl_get('u_view_calendar') || empty($this->config['calendar_enabled']) || empty($this->config['calendar_event_index']))
		{
			return;
		}

		$this->calendar->get_calendar(date("Y"), 0)	
			->upcoming_events()
			->get_birth_days(date("Y"), 0, date("m"));
	}
}
