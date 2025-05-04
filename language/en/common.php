<?php
/**
	* Events calendar $extends the phpBB Software package.
	* @copyright (c) 2024, Steve, https://steven-clark.tech/
	* @license GNU General Public License, version 2 (GPL-2.0)
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

$lang = array_merge($lang, [
	'CALENDAR_TITLE'			=> 'Calendar',
	'ACP_CALENDAR_TITLE'		=> 'Calendar',
	'ACP_CALENDAR_SETTINGS'		=> 'Settings',
	'CALENDAR'					=> 'Calendar',
	'NOTIFICATION_GROUP_CALENDAR_EVENTS' 	=> 'Calendar Events',
	'NOTIFICATION_TYPE_UPCOMING_EVENT'		=> 'Events you are attending',
	'UPCOMING_EVENT'			=> '<strong>Upcoming event:</strong> %1s',
	'VIEWING_CALENDAR'			=> 'Viewing Calendar',
	'CALENDAR_DISABLED'			=> 'The Calendar is currently disabled',
]);
