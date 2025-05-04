<?php
/**
	* Events calendar $extends the phpBB Software package.
	* @copyright (c) 2024, Steve, https://steven-clark.tech/
	* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace steve\calendar\controller;

class calendar_view
{
	protected $auth;
	protected $config;
	protected $helper;
	protected $language;
	protected $pagination;
	protected $template;

	protected $calendar;
	protected $routing;
	
	public function __construct(
		\phpbb\auth\auth $auth, 
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language,
		\phpbb\pagination $pagination,
		\phpbb\template\template $template,
		
		\steve\calendar\calendar\calendar $calendar,
		\steve\calendar\calendar\routing $routing)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->helper = $helper;
		$this->language = $language;
		$this->pagination = $pagination;
		$this->template = $template;
		
		$this->calendar = $calendar;
		$this->routing = $routing;

 		if (!$this->auth->acl_get('u_view_calendar'))
		{
			throw new \phpbb\exception\http_exception(403, 'NO_AUTH_CALENDAR');
		}
 		if (empty($this->config['calendar_enabled']))
		{
			throw new \phpbb\exception\http_exception(404, 'CALENDAR_DISABLED');
		}
	}

	public function view_year($year)
	{
		$this->routing->validate_year($year)
			->year_next_prev($year);

		$this->calendar->get_calendar($year, 0);
		
		$this->template->assign_block_vars('navlinks', [
			'U_BREADCRUMB'		=> $this->helper->route('steve_calendar_year', ['year' => $year]),
			'BREADCRUMB_NAME'	=> $year,
		]);
		
		return $this->helper->render('calendar.html', $this->language->lang('CALENDAR_YEAR', $year));
	}

	public function view_month($month, $year)
	{
		$this->routing->validate_year($year)
			->validate_month($month)
			->year_next_prev($year)
			->month_next_prev($month, $year);

		$month_route = $this->helper->route('steve_calendar_month', ['month' => $month, 'year' => $year]);
				
		$this->calendar->get_calendar($year, $this->get_month_int($month))
			->get_month_events($month, 0, $year, $month_route)
			->get_birth_days($year, 0, $this->get_month_int($month));
		
		return $this->helper->render('calendar.html', $this->language->lang('CALENDAR_MONTH', $month, $year));
	}

	public function view_day($day_string, $day, $month, $year)
	{
		$this->routing->validate_year($year)
			->validate_month($month)
			->validate_day($day_string, $day)
			->year_next_prev($year)
			->month_next_prev($month, $year)
			->day_next_prev($day, $month, $year);

		$day_route = $this->helper->route('steve_calendar_day', ['day_string' => $day_string, 'day' => $day, 'month' => $month, 'year' => $year]);

		$this->calendar->get_calendar($year, $this->get_month_int($month), $day_route)
			->get_month_events($month, $day, $year, $day_route)
			->get_birth_days($year, $day, $this->get_month_int($month));
			
		return $this->helper->render('calendar.html', $this->language->lang('CALENDAR_DAY', $day_string, $day, $month, $year));
	}
	
	public function get_month_int($month)
	{
		return date("n", strtotime($month));
	}
}
