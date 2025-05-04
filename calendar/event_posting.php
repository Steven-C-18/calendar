<?php
/**
	* Events calendar $extends the phpBB Software package.
	* @copyright (c) 2024, Steve, https://steven-clark.tech/
	* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace steve\calendar\calendar;

use steve\calendar\calendar\constants;

class event_posting
{
	protected $auth;
	protected $cache;
	protected $config;
	protected $db;
	protected $helper;
	protected $language;
	protected $template;			
	protected $user;
	protected $php_ext;
	protected $root_path;
	
	protected $date;
	protected $calendar_events;
	
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,		
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$php_ext,
		$root_path,
		
		\steve\calendar\calendar\date_time $date,
		$calendar_events)
	{
		$this->auth = $auth;
		$this->config = $config;		
		$this->db = $db;	
		$this->helper = $helper;
		$this->language = $language;
		$this->template = $template;
		$this->user = $user;
		$this->php_ext = $php_ext;
		$this->root_path = $root_path;
		
		$this->date = $date;
		$this->table_calendar_events = $calendar_events;
	}
	
	public function get_event($event_id)
	{
		if (empty($event_id))
		{
			return false;
		}
		
		$sql = 'SELECT *
			FROM ' . $this->table_calendar_events . '
			WHERE event_id = ' . (int) $event_id;
		$result = $this->db->sql_query($sql);
		$event_data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		
		return $event_data;
	}

	public function u_event_action($auth_action, $user_id)
	{
		return $this->auth->acl_get($auth_action) && ($user_id == $this->user->data['user_id'] || $user_id != $this->user->data['user_id'] && $this->auth->acl_get('a_')) ? true : false;
	}
	
	public function validate_form($error = [], $form_data, $month)
	{
		if (!check_form_key('postform'))
		{
			$error[] = $this->language->lang('FORM_INVALID');
		}
		if (empty($form_data['title']))
		{
			$error[] = $this->language->lang('EVENT_TITLE_EMPTY');
		}
		if (empty($form_data['information']))
		{
			$error[] = $this->language->lang('EVENT_INFO_EMPTY');
		}		
		if (empty($form_data['year']))
		{
			$error[] = $this->language->lang('EVENT_YEAR_EMPTY');
		}		
		if ($month == 0 || $form_data['month_string'] == '')
		{
			$error[] = $this->language->lang('EVENT_MONTH_EMPTY');
		}
		if (empty($form_data['day']))
		{
			$error[] = $this->language->lang('EVENT_DAY_EMPTY');
		}		
		
		if (!empty($form_data['year']))
		{
			$max = cal_days_in_month(CAL_GREGORIAN, $month, $form_data['year']);
			if (empty($form_data['day']) || $form_data['day'] > $max)
			{
				$error[] = $this->language->lang('EVENT_DAY_INVALID', $form_data['month_string'], $form_data['month_string'], $max);
			}
		}

		return (array) $error;
	}
	
	public function template_vars($action_url, $error)
	{
 		if (empty($action_url))
		{
			throw new \phpbb\exception\http_exception(403, 'FORM_ACTION_INVALID');
		}
		
		$this->user->add_lang('posting');

		if (!function_exists('display_custom_bbcodes'))
		{
			include_once $this->root_path . 'includes/functions_display.' . $this->php_ext;
		}

		$this->template->assign_vars([
			'S_ERROR'		    	=> !empty($error) ? implode('<br>', $error) : false,
			'U_ACTION'				=> $action_url,			
			'U_MORE_SMILIES'    	=> append_sid("{$this->root_path}posting.$this->php_ext", 'mode=smilies'),
			'S_BBCODE_ALLOWED'		=> $this->bbcode_status(),
			'S_BBCODE_IMG'			=> $this->bbcode_status(),
			'S_BBCODE_FLASH'		=> $this->flash_status() & $this->bbcode_status() ? true : false,
			'S_LINKS_ALLOWED'		=> $this->url_status(),
			'BBCODE_STATUS'			=> $this->language->lang(($this->bbcode_status() ? 'BBCODE_IS_ON' : 'BBCODE_IS_OFF'), '<a href="' . $this->helper->route('phpbb_help_bbcode_controller') . '">', '</a>'),
			'IMG_STATUS'			=> $this->bbcode_status() ? $this->language->lang('IMAGES_ARE_ON') : $this->language->lang('IMAGES_ARE_OFF'),
			'FLASH_STATUS'			=> $this->flash_status() ? $this->language->lang('FLASH_IS_ON') : $this->language->lang('FLASH_IS_OFF'),
			'SMILIES_STATUS'		=> $this->smilies_status() ? $this->language->lang('SMILIES_ARE_ON') : $this->language->lang('SMILIES_ARE_OFF'),
			'URL_STATUS'			=> $this->bbcode_status() && $this->url_status() ? $this->language->lang('URL_IS_ON') : $this->language->lang('URL_IS_OFF'),
			'S_BBCODE_QUOTE'		=> true,
		]);
		
		if ($this->bbcode_status())
		{
			display_custom_bbcodes();
			if ($this->smilies_status())
			{	
				if (!function_exists('generate_smilies'))
				{
					include_once $this->root_path . 'includes/functions_posting.' . $this->php_ext;
				}
				generate_smilies('inline', 0);	
			}	
		}
		
		return $this;
	}

	public function select_month_options($select_lang = '', $options, $option_field)
	{
		$s_options = '<option value=""' . (empty($option_field) ? ' selected="selected"' : '') . '>' . $select_lang . '</option>' . PHP_EOL;
		foreach ($options as $key => $option)
		{
			$selected = ($option === $option_field) ? 'selected="selected"' : '';
			$s_options .= "<option value=\"$option\" $selected>". $this->language->lang($option) . "</option>" . PHP_EOL;
		}
		unset($options);
		
		return $s_options;
	}
	
	public function select_hrs_mins($select_lang = '', $end, $option_field, $hr = true)
	{	
		$s_options = '';
		for ($start =  0; $start < $end; $start++)
		{
			$select = $hr ? date("h:i a",  strtotime("$start:00")) : ($start >= 10 ? $start : '0' . $start);
			$selected = ($select == $option_field) ? 'selected="selected"' : '';			
			$s_options .="<option value=\"$start\" $selected>" . $select . "</option>" . PHP_EOL;
		}
		
		return $s_options;
	}
	
	public function bbcode_status()
	{
		return $this->config['allow_bbcode'] ? true : false;
	}
	
	public function smilies_status()
	{
		return $this->config['allow_smilies'] ? true : false;
	}
	
	public function url_status()
	{
		return $this->config['allow_post_links'] ? true : false;
	}
	
	public function flash_status()
	{
		return $this->config['allow_post_flash'] ? true : false;
	}
	
	public function topic_message($year, $month, $month_string, $day_js, $route, $topic_message)
	{	
		return $this->language->lang('EVENT_TOPIC_MESSAGE', $day_js, $this->language->lang($month_string), $year, $route, $topic_message);				
	}
	
	public function add_topic($topic_title, $topic_message)
	{
		$event_forum_id = (int) $this->config['calendar_event_forum_id'];
		
		$sql = 'SELECT forum_id
			FROM ' . FORUMS_TABLE . "
			WHERE forum_id = $event_forum_id
				AND forum_type = " . FORUM_POST;
		$result = $this->db->sql_query($sql);
		$forum = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		
		$icon_id = (int) $this->config['calendar_event_forum_id'];
		if (empty($this->config['calendar_event_post_enable']) || empty($topic_title) || empty($topic_message) || empty($forum['forum_id']))
		{
			return false;
		}
		
		if (!function_exists('submit_post'))
		{
			include_once $this->root_path . 'includes/functions_posting.' . $this->php_ext;
		}
		
		$poll = $uid = $bitfield = $options = ''; 
		generate_text_for_storage($topic_message, $uid, $bitfield, $options, true, true, true, true);

		// New Topic Example
		$data = [ 
			// General Posting Settings
			'forum_id'            	=> $forum['forum_id'],// The forum ID in which the post will be placed. (int)
			'topic_id'            	=> 0,// Post a new topic or in an existing one? Set to 0 to create a new one, if not, specify your topic ID here instead.
			'icon_id'           	=> isset($icon_id) ? $icon_id : 0,// The Icon ID in which the post will be displayed with on the viewforum, set to false for icon_id. (int)

			// Defining Post Options
			'enable_bbcode'    		=> true,// Enable BBcode in this post. (bool)
			'enable_smilies'    	=> true,// Enabe smilies in this post. (bool)
			'enable_urls'        	=> true,// Enable self-parsing URL links in this post. (bool)
			'enable_sig'        	=> true,// Enable the signature of the poster to be displayed in the post. (bool)

			// Message Body
			'message'            	=> $topic_message,// Your text you wish to have submitted. It should pass through generate_text_for_storage() before this. (string)
			'message_md5'    		=> md5($topic_message),// The md5 hash of your message

			// Values from generate_text_for_storage()
			'bbcode_bitfield'   	=> '',// Value created from the generate_text_for_storage() function.
			'bbcode_uid'        	=> '',// Value created from the generate_text_for_storage() function.

			// Other Options
			'post_edit_locked'    	=> 0,// Disallow post editing? 1 = Yes, 0 = No
			'topic_title'        	=> $topic_title,// Subject/Title of the topic. (string)

			// Email Notification Settings
			'notify_set'        	=> false,// (bool)
			'notify'            	=> false,// (bool)
			'post_time'         	=> 0,// Set a specific time, use 0 to let submit_post() take care of getting the proper time (int)
			'forum_name'        	=> '',// For identifying the name of the forum in a notification email. (string)

			// Indexing
			'enable_indexing'    	=> true,// Allow indexing the post? (bool)

			// 3.0.6
			'force_approved_state'   => true,// Allow the post to be submitted without going into unapproved queue

			// 3.1-dev, overwrites force_approve_state
			'force_visibility'       => true,// Allow the post to be submitted without going into unapproved queue, or make it be deleted
		];

		$redirect_url = submit_post('post', $topic_title, '', POST_NORMAL, $poll, $data);
		
		//we need the post id for discussion topic 
		return $redirect_url;
	}	
}
