<?php
/**
	* Events calendar $extends the phpBB Software package.
	* @copyright (c) 2024, Steve, https://steven-clark.tech/
	* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace steve\calendar\acp;

class calendar_module
{
	public $id;
	public $u_action;
	
	public function main($id, $mode)
	{
		global $config, $user, $template, $request;

		$user->add_lang_ext('steve/calendar', 'acp_calendar');
		$this->tpl_name = 'acp_calendar_settings_body';
		$this->page_title = $user->lang('ACP_CALENDAR_TITLE');						
		add_form_key('calendar');
		
		$max = (int) 86400;
		$min = (int) 300;
		
		switch ($mode)
		{	
			case 'settings':
			
				$form_data = [
					'calendar_enabled'				=> $request->variable('calendar_enabled', false),
					
					'calendar_event_post_enable' 	=> $request->variable('calendar_event_post_enable', false),
					'calendar_event_forum_id' 		=> $request->variable('calendar_event_forum_id', 0),
					'calendar_event_icon_id'		=> $request->variable('icon', 0),
					
					'calendar_default_link'			=> $request->variable('calendar_default_link', '', true),
					'calendar_event_index'			=> $request->variable('calendar_event_index', false),
					'calendar_above_forums_index'	=> $request->variable('calendar_above_forums_index', false),
					'calendar_enable_birthdays'		=> $request->variable('calendar_enable_birthdays', false),
					'calendar_enable_notifications'	=> $request->variable('calendar_enable_notifications', false),
					'calendar_event_limit'			=> $request->variable('calendar_event_limit', 0),
					
					'calendar_event_remind'			=> $request->variable('calendar_event_remind', 0),
					'calendar_cache_time'			=> $request->variable('calendar_cache_time', 0),
					'calendar_cron_task_gc'			=> $request->variable('calendar_cron_task_gc', 0),
				];
				
				if ($request->is_set_post('submit'))
				{
					if (!check_form_key('calendar'))
					{
						trigger_error($user->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
					}
					if (!empty($form_data['calendar_event_post_enable']) && empty($form_data['calendar_event_icon_id']))
					{
						trigger_error($user->lang('ACP_CALENDAR_FORUM_ID_EMPTY') . adm_back_link($this->u_action), E_USER_WARNING);
					}
					if ($form_data['calendar_event_remind'] < (int) 1 || $form_data['calendar_event_remind'] > (int) 365)
					{
						trigger_error($user->lang('ACP_CALENDAR_EVENT_REMIND_ERROR', (int) 1, (int) 365) . adm_back_link($this->u_action), E_USER_WARNING);
					}
					if ($form_data['calendar_cache_time'] < $min || $form_data['calendar_cache_time'] > $max)
					{
						trigger_error($user->lang('ACP_CALENDAR_CACHE_TIME_ERROR', $min, $max) . adm_back_link($this->u_action), E_USER_WARNING);
					}
					if ($form_data['calendar_cron_task_gc'] < $min || $form_data['calendar_cron_task_gc'] > $max)
					{
						trigger_error($user->lang('ACP_CALENDAR_CRON_TASK_ERROR', $min, $max) . adm_back_link($this->u_action), E_USER_WARNING);
					}
										
					$config->set('calendar_enabled', $form_data['calendar_enabled']);
					
					$config->set('calendar_event_post_enable', $form_data['calendar_event_post_enable']);
					$config->set('calendar_event_icon_id', $form_data['calendar_event_icon_id']);
					$config->set('calendar_event_forum_id', $form_data['calendar_event_forum_id']);
					
					$config->set('calendar_default_link', $form_data['calendar_default_link']);
					$config->set('calendar_event_index', $form_data['calendar_event_index']);
					$config->set('calendar_above_forums_index', $form_data['calendar_above_forums_index']);
					$config->set('calendar_enable_birthdays', $form_data['calendar_enable_birthdays']);
					$config->set('calendar_enable_notifications', $form_data['calendar_enable_notifications']);
					$config->set('calendar_event_limit', $form_data['calendar_event_limit']);
					
					$config->set('calendar_event_remind', $form_data['calendar_event_remind']);
					$config->set('calendar_cache_time', $form_data['calendar_cache_time']);
					$config->set('calendar_cron_task_gc', $form_data['calendar_cron_task_gc']);
					
					trigger_error($user->lang('ACP_CALENDAR_SETTING_SAVED') . adm_back_link($this->u_action));
				}

				$forum_box = make_forum_select($config['calendar_event_forum_id'], false, false, false, false, true);
				
				$this->topic_icons($config['calendar_event_icon_id']);

				$calendar_default_link = ['DAY', 'MONTH', 'YEAR'];
				$s_options = '<option value="0"' . (empty($config['calendar_default_link']) ? ' selected="selected"' : '') . '>' . $user->lang['ACP_SELECT_CALENDAR_DEFAULT_LINK'] . '</option>' . PHP_EOL;
				foreach ($calendar_default_link as $option)
				{
					$selected = ($option === $config['calendar_default_link']) ? ' selected="selected"' : '';
					$s_options .= "<option value=\"$option\"$selected>" . $user->lang('ACP_CALENDAR_' . $option) . "</option>" . PHP_EOL;
				}

				$template->assign_vars([
					'U_ACTION'						=> $this->u_action,
					
					'SETTINGS_MODE'					=> true,					
					'CALENDAR_ENABLED'				=> $config['calendar_enabled'],
					
					'CALENDAR_EVENT_POST_ENABLE'	=> $config['calendar_event_post_enable'],
					'S_CALENDAR_DEFAULT_LINK'		=> $s_options,
					'CALENDAR_EVENT_INDEX'			=> $config['calendar_event_index'],
					'CALENDAR_ABOVE_FORUMS_INDEX'	=> $config['calendar_above_forums_index'],
					'CALENDAR_ENABLE_BIRTHDAYS'		=> $config['calendar_enable_birthdays'],
					'CALENDAR_ENABLE_NOTIFICATIONS'	=> $config['calendar_enable_notifications'],
					'CALENDAR_EVENT_LIMIT'			=> $config['calendar_event_limit'],
					
					'CALENDAR_EVENT_REMIND'			=> $config['calendar_event_remind'],
					'CALENDAR_CACHE_TIME'			=> $config['calendar_cache_time'],
					'CALENDAR_CRON_TASK_GC'			=> $config['calendar_cron_task_gc'],
					'CALENDAR_CRON_LAST_GC'			=> !empty($config['calendar_cron_task_last_gc']) ? $user->lang('LAST_RUN') . ' ' . $user->format_date($config['calendar_cron_task_last_gc']) : '',
					'FORUM_BOX'						=> $forum_box,
				]);

			break;
		}
	}
	
	public function topic_icons($icon_id)
	{
		global $config, $template, $cache;

		$icons = $cache->obtain_icons();

		if (empty($icons))
		{
			return false;			
		}
		
		foreach ($icons as $id => $data)
		{
			if (empty($data['display']))
			{
				continue;
			}
			$template->assign_block_vars('topic_icon', [
				'ICON_ID'		=> $id,
				'ICON_IMG'		=> generate_board_url() . '/' . $config['icons_path'] . '/' . $data['img'],
				'ICON_WIDTH'	=> $data['width'],
				'ICON_HEIGHT'	=> $data['height'],
				'ICON_ALT'		=> $data['alt'],
				'S_CHECKED'			=> ($id == $icon_id) ? true : false,
				'S_ICON_CHECKED'	=> ($id == $icon_id) ? ' checked="checked"' : '',
			]);
		}
		unset($icons);

		return $this;
	}
}
