<?php
/**
	* Events calendar $extends the phpBB Software package.
	* @copyright (c) 2024, Steve, https://steven-clark.tech/
	* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace steve\calendar\migrations;

class install_calendar extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['calendar_enabled']);
	}
	
	static public function depends_on()
	{
		return ['\phpbb\db\migration\data\v320\v320'];
	}

	public function update_data()
	{
		return [
			['config.add', ['calendar_enabled', true]],
			['config.add', ['calendar_event_forum_id', 0]],
			['config.add', ['calendar_event_icon_id', 0]],
			['config.add', ['calendar_event_post_enable', false]],
			['config.add', ['calendar_event_index', false]],
			['config.add', ['calendar_event_limit', 25]],
			['config.add', ['calendar_event_remind', 7]],
			['config.add', ['calendar_event_month_limit', 1]],
			['config.add', ['calendar_default_link', 'YEAR']],
			['config.add', ['calendar_upcoming_events', 7]],
			
			['config.add', ['calendar_enable_birthdays', true]],
			['config.add', ['calendar_enable_notifications', true]],
			['config.add', ['calendar_above_forums_index', true]],
			
			['config.add', ['calendar_cache_time', 1800]],
			['config.add', ['calendar_cron_task_batch', 100]],
			['config.add', ['calendar_cron_task_last_gc', 0]],
			['config.add', ['calendar_cron_task_gc', 3600]],

			['permission.add', ['u_view_calendar']],
			['permission.permission_set', ['ROLE_USER_FULL', 'u_view_calendar']],
			['permission.permission_set', ['ROLE_USER_STANDARD', 'u_view_calendar']],
			['permission.permission_set', ['REGISTERED', 'u_view_calendar', 'group']],
			
			['permission.add', ['u_view_calendar_days']],
			['permission.permission_set', ['ROLE_USER_FULL', 'u_view_calendar_days']],
			['permission.permission_set', ['ROLE_USER_STANDARD', 'u_view_calendar_days']],
			['permission.permission_set', ['REGISTERED', 'u_view_calendar_days', 'group']],
			
			['permission.add', ['u_view_calendar_events']],
			['permission.permission_set', ['ROLE_USER_FULL', 'u_view_calendar_events']],
			['permission.permission_set', ['ROLE_USER_STANDARD', 'u_view_calendar_events']],
			['permission.permission_set', ['REGISTERED', 'u_view_calendar_events', 'group']],
			
			['permission.add', ['u_calendar_search']],
			['permission.permission_set', ['ROLE_USER_FULL', 'u_calendar_search']],
			['permission.permission_set', ['ROLE_USER_STANDARD', 'u_calendar_search']],
			['permission.permission_set', ['REGISTERED', 'u_calendar_search', 'group']],
			
			['permission.add', ['u_add_calendar_event']],
			['permission.permission_set', ['ROLE_USER_FULL', 'u_add_calendar_event']],
			['permission.permission_set', ['ROLE_USER_STANDARD', 'u_add_calendar_event']],
			['permission.permission_set', ['REGISTERED', 'u_add_calendar_event', 'group']],
			
			['permission.add', ['u_edit_calendar_event']],
			['permission.permission_set', ['ROLE_USER_FULL', 'u_edit_calendar_event']],
			['permission.permission_set', ['ROLE_USER_STANDARD', 'u_edit_calendar_event']],
			['permission.permission_set', ['REGISTERED', 'u_edit_calendar_event', 'group']],
			
			['permission.add', ['u_delete_calendar_event']],
			['permission.permission_set', ['ROLE_USER_FULL', 'u_delete_calendar_event']],
			['permission.permission_set', ['ROLE_USER_STANDARD', 'u_delete_calendar_event']],
			['permission.permission_set', ['REGISTERED', 'u_delete_calendar_event', 'group']],
			
			['permission.add', ['u_attend_calendar_event']],
			['permission.permission_set', ['ROLE_USER_FULL', 'u_attend_calendar_event']],
			['permission.permission_set', ['ROLE_USER_STANDARD', 'u_attend_calendar_event']],
			['permission.permission_set', ['REGISTERED', 'u_attend_calendar_event', 'group']],
			
			['permission.add', ['u_unattend_calendar_event']],
			['permission.permission_set', ['ROLE_USER_FULL', 'u_unattend_calendar_event']],
			['permission.permission_set', ['ROLE_USER_STANDARD', 'u_unattend_calendar_event']],
			['permission.permission_set', ['REGISTERED', 'u_unattend_calendar_event', 'group']],			

			['module.add', ['acp', 'ACP_CAT_DOT_MODS', 'ACP_CALENDAR_TITLE']],
			['module.add', [
				'acp', 'ACP_CALENDAR_TITLE',
				[
					'module_basename'	=> '\steve\calendar\acp\calendar_module',
					'modes'				=> ['settings'],
				],
			]],	
		];
	}

	public function update_schema()
	{
		return [	
			'add_tables'		=> [
				$this->table_prefix . 'steve_calendar_events'	=> [
					'COLUMNS'		=> [
						'event_id'        		=> ['UINT', NULL, 'auto_increment'],
						'user_id'     			=> ['UINT', 0],						
						'title'					=> ['VCHAR:100', ''],
						'time_stamp'         	=> ['VCHAR:12', ''],
						'hour'					=> ['INT:2', 0],
						'minute'				=> ['INT:2', 0],
						'day'         			=> ['INT:2', 0],
						'day_string'  			=> ['VCHAR:9', ''],
						'month'					=> ['INT:2', 0],
						'month_string'			=> ['VCHAR:9', ''],
						'year'					=> ['INT:4', 0],
						'time_stamp_end'        => ['VCHAR:12', ''],					
						'hour_end'				=> ['INT:2', 0],
						'minute_end'			=> ['INT:2', 0],
						'day_end'         		=> ['INT:2', 0],
						'month_end'				=> ['INT:2', 0],
						'year_end'				=> ['INT:4', 0],
						'month_string_end'    	=> ['VCHAR:9', 0],
						'day_string_end'    	=> ['VCHAR:9', 0],					
						'time_zone'				=> ['VCHAR:100', ''],
						'duration'				=> ['INT:3', 0],
						'annual'				=> ['BOOL', 0],
						'world_date'			=> ['BOOL', 0],
						'birthday'				=> ['BOOL', 0],
						'attendees'				=> ['UINT', 0], 
						'views'     			=> ['UINT', 0],
						'post_id'     			=> ['UINT', 0],
						'fa_icon'				=> ['VCHAR:255', ''],
						'fa_icon_size'			=> ['INT:10', 0],
						'fa_icon_color'			=> ['VCHAR:6', ''],
						'information'			=> ['TEXT', '', null],					
						'bbcode_uid'			=> ['VCHAR:8', ''],
						'bbcode_bitfield'		=> ['VCHAR:255', ''],
						'bbcode_options'		=> ['UINT:11', 7],
					],
					'PRIMARY_KEY'   		=> 'event_id',
				],
				
				$this->table_prefix . 'steve_calendar_events_attending'	=> [
					'COLUMNS'		=> [
						'attend_id'        		=> ['UINT', NULL, 'auto_increment'],
						'event_id'        		=> ['UINT', 0],
						'user_id'     			=> ['UINT', 0],
						'time_stamp'         	=> ['VCHAR:12', ''],
						'time_stamp_end'        => ['VCHAR:12', ''],
						'year'					=> ['INT:4', 0],
						'annual'				=> ['BOOL', 0],
						'title'					=> ['VCHAR:100', ''],
						'notified'				=> ['BOOL', 0],
						'attended'				=> ['BOOL', 0],
					],
					'PRIMARY_KEY'   		=> 'attend_id',
				],
			],
		];
	}

	public function revert_schema()
	{
 		return [
			'drop_tables'		=> [
				$this->table_prefix . 'steve_calendar_events',
				$this->table_prefix . 'steve_calendar_events_attending',
			],
		];
	}
}
