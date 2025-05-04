<?php
/**
	* Events calendar $extends the phpBB Software package.
	* @copyright (c) 2024, Steve, https://steven-clark.tech/
	* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace steve\calendar;

class ext extends \phpbb\extension\base
{
	const PHPBB_MIN_VERSION = '3.3.0';

	public function is_enableable()
	{
		$config = $this->container->get('config');
		return !isset($config['calendar_version']) && phpbb_version_compare($config['version'], self::PHPBB_MIN_VERSION, '>=');
	}

 	public function enable_step($old_state)
	{
		switch ($old_state)
		{
			case '':
				$phpbb_notifications = $this->container->get('notification_manager');
				$phpbb_notifications->enable_notifications('steve.calendar.notification.type.upcoming_event');
				
				return 'notification';
			break;

			default:
				return parent::enable_step($old_state);
			break;
		}
	}

	public function disable_step($old_state)
	{
		switch ($old_state)
		{
			case '':
				$phpbb_notifications = $this->container->get('notification_manager');
				$phpbb_notifications->disable_notifications('steve.calendar.notification.type.upcoming_event');
				
				return 'notification';
			break;

			default:
				return parent::disable_step($old_state);
			break;
		}
	}

 	public function purge_step($old_state)
	{
		switch ($old_state)
		{
			case '':
				$phpbb_notifications = $this->container->get('notification_manager');
				$phpbb_notifications->purge_notifications('steve.calendar.notification.type.upcoming_event');
				
				return 'notification';
			break;

			default:
				return parent::purge_step($old_state);
			break;
		}
	}
}
