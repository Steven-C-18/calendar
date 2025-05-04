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

// Permissions	
$lang = array_merge($lang, array(
	'ACL_CAT_CALENDAR'					=> 'Calendar',
	'ACL_U_ADD_CALENDAR_EVENT'			=> 'Can add calendar events',
	'ACL_U_ATTEND_CALENDAR_EVENT'		=> 'Can attend calendar events',
	'ACL_U_DELETE_CALENDAR_EVENT'		=> 'Can delete calendar events',
	'ACL_U_EDIT_CALENDAR_EVENT'			=> 'Can edit calendar events',
	'ACL_U_UNATTEND_EVENT'				=> 'Can un_attend calendar events',
	'ACL_U_VIEW_CALENDAR'				=> 'Can view calendar',
	'ACL_U_VIEW_CALENDAR_EVENTS'		=> 'Can view calendar events',
	'ACL_U_SEARCH_CALENDAR'				=> 'Can search Calendar',
));
