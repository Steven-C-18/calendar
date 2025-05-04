<?php
/**
	* Events calendar $extends the phpBB Software package.
	* @copyright (c) 2024, Steve, https://steven-clark.tech/
	* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace steve\calendar\acp;

/**
	* ACP module info.
*/
class calendar_info
{
	public function module()
	{
		return [
			'filename'	=> '\steve\calendar\acp\calendar_module',
			'title'		=> 'ACP_CALENDAR_TITLE',
			'modes'		=> [
				'settings'	=> [
					'title'		=> 'ACP_CALENDAR_SETTINGS',
					'auth'		=> 'ext_steve/calendar && acl_a_board',
					'cat'		=> ['ACP_CALENDAR_TITLE']
				],			
			],
		];
	}
}
