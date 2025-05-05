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
	$lang = array();
}

$lang = array_merge($lang, array(
	'ACP_STEVE_HELP'			=> 'Support',
	'ACP_STEVE_EXTENSIONS'		=> 'Extensions',
	'ACP_STEVE_DONATE'			=> 'Donate',

	'ACP_CALENDAR_CACHE_TIME'					=> 'Calendar SQL Cache time',
	'ACP_CALENDAR_CACHE_TIME_ERROR'				=> 'Calendar SQL Cache time is set at a minimum of %1d seconds and maximum %2d seconds',
	'ACP_CALENDAR_CACHE_TIME_EXPLAIN'			=> 'Time in seconds to check for new events.<br>Min: 300 Seconds (5 minutes) Max: 86400 Seconds (1 Day)',
	'ACP_CALENDAR_CRON_TASK_GC'					=> 'Upcoming Event Notification Time',
	'ACP_CALENDAR_CRON_TASK_ERROR'				=> 'Upcoming Event Notification Time is set at a minimum of %1d seconds and maximum %2d seconds',
	'ACP_CALENDAR_CRON_TASK_GC_EXPLAIN'			=> 'Time in seconds to check for Upcoming events to remind Users.<br>Min: 300 Seconds (5 minutes) Max: 86400 Seconds (1 Day)',

	'ACP_CALENDAR_DONATE'						=> 'Donate',
	
	'ACP_CALENDAR_ENABLE'						=> 'Enable Calendar',
	'ACP_CALENDAR_EVENT_FORUM_ID'				=> 'New event topic forum',
	'ACP_CALENDAR_EVENT_ICON_ID'				=> 'New Topic Icon',
	'ACP_CALENDAR_EVENT_ICON_ID_EXPLAIN'		=> 'Topic icon to be used with Topic Events',
	'ACP_CALENDAR_EVENT_INDEX_ENABLE'			=> 'Upcoming Events on Index',
	'ACP_CALENDAR_ABOVE_FORUMS_INDEX'			=> 'Display above forums on Index',
	'ACP_CALENDAR_ENABLE_BIRTHDAYS'				=> 'Enable Birthdays',
	'ACP_CALENDAR_ENABLE_NOTIFICATIONS'			=> 'Enable board email/notifications',
	'ACP_CALENDAR_EVENT_LIMIT'					=> 'Events per Page',
	'ACP_CALENDAR_EVENT_LIMIT_EXPLAIN'			=> '',//
	'ACP_CALENDAR_EVENT_POST_ENABLE'			=> 'Enable Posting',
	'ACP_CALENDAR_EVENT_POST_ENABLE_EXPLAIN'	=> 'Post a new Topic with new events',
	'ACP_CALENDAR_EVENT_REMIND'					=> 'Upcoming Event Notifications',
	'ACP_CALENDAR_EVENT_REMIND_EXPLAIN'			=> 'Number of days to send Event reminder Notifications to event attendees<br>Min: 1, Max: 365 days',
	'ACP_CALENDAR_EVENT_REMIND_ERROR'			=> 'Upcoming Event Notifications days are set at a minimum of %1d and maximum %2d days',
	'ACP_CALENDAR_FORUM_ID_EMPTY'				=> 'The selected Forum id is empty',
	'ACP_CALENDAR_POST_SETTINGS'				=> 'Topic Settings',
	'ACP_CALENDAR_SCHEDULED_TASK_SETTINGS'		=> 'Scheduled Tasks',
	
	
	'ACP_CALENDAR_SETTINGS'				=> 'Calendar Settings',
	'ACP_CALENDAR_SETTINGS_EXPLAIN'		=> '',
	'ACP_CALENDAR_SETTING_SAVED'		=> 'Calendar settings saved successfully.',
	'ACP_CALENDAR_TITLE'				=> 'Calendar',

	'ACP_SELECT_CALENDAR_DEFAULT_LINK' 	=> 'Select Default URL',
	'NO_TOPIC_ICON'						=> 'No Topic Icon',
	'LAST_RUN'							=> ', Last Run:',

	'ACP_CALENDAR_DEFAULT_LINK'			=> 'Calendar page default link',

	'ACP_CALENDAR_DEFAULT_LINK_EXPLAIN'	=> 'Navigation link takes you to either: year, month, day(now).',

	'ACP_CALENDAR_DAY'			=> 'Day',
	'ACP_CALENDAR_MONTH'		=> 'Month',
	'ACP_CALENDAR_YEAR'			=> 'Year',

	'MONDAY'		=> 'Monday',
	'TUESDAY'		=> 'Tuesday',
	'WEDNESDAY'		=> 'Wednesday',
	'THURSDAY'		=> 'Thursday',
	'FRIDAY'		=> 'Friday',
	'SATURDAY'		=> 'Saturday',
	'SUNDAY'		=> 'Sunday',
	
	'MONDAY_D'		=> 'Mon',
	'TUESDAY_D'		=> 'Tue',
	'WEDNESDAY_D'	=> 'Wed',
	'THURSDAY_D'	=> 'Thu',
	'FRIDAY_D'		=> 'Fri',
	'SATURDAY_D'	=> 'Sat',
	'SUNDAY_D'		=> 'Sun',
	
	'JANUARY'		=> 'January',
	'FEBRUARY'		=> 'February',
	'MARCH'			=> 'March',
	'APRIL'			=> 'April',
	'MAY'			=> 'May',
	'JUNE'			=> 'June',
	'JULY'			=> 'July',
	'AUGUST'		=> 'August',
	'SEPTEMBER'		=> 'September',
	'OCTOBER'		=> 'October',
	'NOVEMBER'		=> 'November',
	'DECEMBER'		=> 'December',
	
	'JANUARY_M'		=> 'Jan',
	'FEBUARY_M'		=> 'Feb',
	'MARCH_M'		=> 'Mar',
	'APRIL_M'		=> 'Apr',
	'MAY_M'			=> 'May',
	'JUNE_M'		=> 'Jun',
	'JULY_M'		=> 'Jul',
	'AUGUST_M'		=> 'Aug',
	'SEPTEMBER_M'	=> 'Sep',
	'OCTOBER_M'		=> 'Oct',
	'NOVEMBER_M'	=> 'Nov',
	'DECEMBER_M'	=> 'Dec',	
));
