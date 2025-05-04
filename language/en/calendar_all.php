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
	'CALENDAR'					=> 'Calendar',
	'CALENDAR_DISABLED'			=> 'The Calendar is currently disabled',
	
	'ACP_CALENDAR_TITLE'		=> 'Calendar',
	'ACP_CALENDAR_SETTINGS'		=> 'Settings',

	'ADD_EVENT'				=> 'Add Calendar Event',
	
	'ANNUAL'				=> 'Annual Event',

	'ATTENDANCE_ADDED' 		=> 'You have been added to the list of attendees to this event successfully.',
	'ATTENDANCE_REMOVED'	=> 'You were removed from the list of attendees to this event successfully.',
	'ATTEND_EVENT'			=> 'Attend Event',
	'ATTENDED_EVENT'		=> 'Users Attended',
	'ATTENDING_EVENT'		=> 'Users Attending',
	'ATTENDEE_FOUND'		=> 'You are already attending this event',
	
	'BIRTHDAY'				=> 'Birthday',
	
	'NOTIFICATION_GROUP_CALENDAR_EVENTS' 	=> 'Calendar Events',
	'NOTIFICATION_TYPE_UPCOMING_EVENT'		=> 'Events you are attending',
	'NOTIFICATION_UPCOMING_EVENT'			=> '<strong>Upcoming event:</strong> %1s <br> %2s',

	'VIEWING_CALENDAR'			=> 'Viewing Calendar',

	//
	'CALENDAR_ACTIVE'		=> 'Active',
	'CALENDAR_ANNUAL'		=> 'Annual',
	'CALENDAR_BIRTHDAYS'	=> 'Birthdays',

	'CALENDAR_EXPIRED'		=> 'Expired',

	'CALENDAR_NOW'			=> 'Now',

	'CALENDAR_TOGGLE_LEFT'	=> 'Toggle Calendar',
	'CALENDAR_TOGGLE_RIGHT'	=> 'Toggle Events',
	
	//
	'CALENDAR_DAY'			=> 'Calendar - %1s %2d %2s %4d',	
	'CALENDAR_MONTH'		=> 'Calendar - %1s %2d',
	'CALENDAR_RESULTS'		=> 'Results',
	'CALENDAR_YEAR'			=> 'Calendar %d',
	'CALENDAR_YEAR_EMPTY'	=> 'Calendar Year not found.',
	'CALENDAR_YEAR_INVALID'	=> 'Calendar Year Invalid.',

	'DELETE_EVENT'			=> 'Delete Event',
	
	'EDIT_EVENT'			=> 'Edit Event',

	'EVENT_ACTIVE'			=> 'Active Event',
	'EVENT_ADDED'			=> 'Calendar event added successfully.<br><a href="%s">View Event</a>',
	'EVENT_BY'				=> 'Event By',
	'EVENT_DAY_INVALID'		=> 'The Incorrect number of days for %1s, number of days in %2s %1d',	
	'EVENT_DAY_TIME'		=> 'Day/Time',
	'EVENT_DELETED'			=> 'Event deleted successfully.',
	'EVENT_EDITED'			=> 'Calendar event edited successfully.<br><a href="%s">View Event</a>',
	'EVENT_EXPIRED'			=> 'Expired Events',

	'EVENT_HOUR'			=> 'Hour',
	'EVENT_INFORMATION'		=> 'Event Information',
	'EVENT_INFO_EMPTY'		=> 'Event information required.',
	
	'EVENT_MINUTE'			=> 'Minute',
	'EVENT_NOW'				=> 'Now',
	'EVENT_PREFIX'			=> '[EVENT] %s',
	'EVENT_TITLE_EMPTY'		=> 'Event Title can not be empty.',
	
	'EVENT_TOPIC_MESSAGE'	=> '[b]Event Date:[/b] %1s %2s %3s' . "\n" . ' [url=%4s]View Event in Calendar[/url]' . "\n" . '[b]Event Information:[/b]' . "\n" . '%5s' ,
	
	//
	'EVENT_MONTH_EMPTY'		=> 'You did not select a month for the event month.',
	
	'EVENT_NOT_FOUND'		=> 'The requested Event does not exist',
	
	'EVENT_DAY_EMPTY'		=> 'You did not select a day for the event day.',

	'EVENT_YEAR_EMPTY'		=> 'You must enter a valid year for the event. If selecting; Annual Event, enter a year the event started on or will start on.',
	
	'EVENTS_FOR'			=> 'Events For',
	'EVENTS_NOW'			=> 'Events Now',
	
	
	'EVENT_START'			=> 'Start',
	'EVENT_STARTS'			=> 'Starts',
	'EVENT_END'				=> 'End',
	'EVENT_ENDS'			=> 'Ends',
	
	'EVENT_START_DAY_TIME'	=> 'Start day/hour/min',
	'EVENT_END_DAY_TIME'	=> 'End day/hour/min',
	
	'EVENT_WORLD_DAY'		=> 'World Day',

	//	
	'EVENT_PERIOD_DAYS'		=> [
		0		=> '',
		1		=> 'Lasts %d day',
		2		=> 'Lasts %d days',
	],
	'EVENT_START_DAYS'		=> [
		0		=> '',
		1		=> 'Starts in %d day',
		2		=> 'Starts in %d days',
	],
	
	'INVALID_ROUTE_DAY'		=> 'Calendar day Invalid',
	'INVALID_ROUTE_MONTH'	=> 'Calendar month Invalid',
	
	'NO_AUTH_CALENDAR'		=> 'You do not have permissions to view the Calendar.',
	'NO_AUTH_DELETE_EVENT'	=> 'You do not have the necessary permissions to delete events.',
	'NO_EVENTS'				=> 'No Events.',
	
	'SELECT'				=> 'Select',
	'SELECT_MONTH'			=> 'Select Month',
	
	'TAB'					=> 'Tab',//
	'TITLE'					=> 'Event Title',//
		
	'UNATTEND_EVENT'		=> 'un-attend Event',
	
	//prefix_
	'MONDAY'		=> 'Monday',
	'TUESDAY'		=> 'Tuesday',
	'WEDNESDAY'		=> 'Wednesday',
	'THURSDAY'		=> 'Thursday',
	'FRIDAY'		=> 'Friday',
	'SATURDAY'		=> 'Saturday',
	'SUNDAY'		=> 'Sunday',
	//???
	'MON'		=> 'Monday',
	'TUE'		=> 'Tuesday',
	'WED'		=> 'Wednesday',
	'THU'		=> 'Thursday',
	'FRI'		=> 'Friday',
	'SAT'		=> 'Saturday',
	'SUN'		=> 'Sunday',
	
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
	
	'JAN'		=> 'Jan',
	'FEB'		=> 'Feb',
	'MAR'		=> 'Mar',
	'APR'		=> 'Apr',
	'MAY'		=> 'May',
	'JUN'		=> 'Jun',
	'JULY'		=> 'Jul',
	'AUG'		=> 'Aug',
	'SEP'		=> 'Sep',
	'OCT'		=> 'Oct',
	'NOV'		=> 'Nov',
	'DEC'		=> 'Dec',
	
	'BOARD_TIMEZONE'			=> 'Timezone',
	'SELECT_TIMEZONE'			=> 'Select timezone',
	'SELECT_CURRENT_TIME'		=> 'Select current time',
	'TIMEZONE'					=> 'Timezone',
	'TIMEZONE_DATE_SUGGESTION'	=> 'Suggestion: %s',
	'TIMEZONE_INVALID'			=> 'The timezone you selected is invalid.',
]);
