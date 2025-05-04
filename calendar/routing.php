<?php
/**
	* Events calendar $extends the phpBB Software package.
	* @copyright (c) 2024, Steve, https://steven-clark.tech/
	* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace steve\calendar\calendar;

use steve\calendar\calendar\constants;

class routing
{
	protected $auth;
	protected $config;
	protected $language;
	protected $template;
	protected $user;
	protected $date;
	
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language,
		\phpbb\template\template $template,
		\steve\calendar\calendar\date_time $date)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->helper = $helper;
		$this->language = $language;
		$this->template = $template;
		$this->date_time = $date;
	}

	public function year_next_prev($year)
	{
		$this->template->assign_vars([
			'YEAR'				=> $year,
			'YEAR_URL'			=> $this->helper->route('steve_calendar_year', ['year' => $year]),
			'NEXT_YEAR'			=> $year + 1,
			'NEXT_YEAR_URL'		=> $this->helper->route('steve_calendar_year', ['year' => $year + 1]),
			'PREV_YEAR'			=> $year - 1,
			'PREV_YEAR_URL'		=> $this->helper->route('steve_calendar_year', ['year' => $year - 1]),
		]);
		
		return $this;
	}
	
	public function month_next_prev($month, $year)
	{
		$month_key = strtoupper($month);
		$this->template->assign_vars([
			'MONTH_YEAR'		=> $this->language->lang(strtoupper($month_key)) . ' ' . $year,
			'MONTH'				=> $this->language->lang(strtoupper($month_key)),
			'MONTH_URL'			=> $this->helper->route('steve_calendar_month', ['month' => $month, 'year' => $year]),
			
			'NEXT_MONTH'		=> $this->language->lang(strtoupper($this->date_time->adjust_date('P1M', $year, $this->date_time->month_int($month), constants::FIRST, true))),
			'NEXT_MONTH_URL'	=> $this->helper->route('steve_calendar_month', [
				'month' 	=> $this->date_time->adjust_date('P1M', $year, $this->date_time->month_int($month), constants::FIRST, true), 
				'year' 		=> $month_key == constants::DEC ? $year + 1 : $year]
			),
			'PREV_MONTH'		=> $this->language->lang(strtoupper($this->date_time->adjust_date('P1M', $year, $this->date_time->month_int($month), constants::FIRST, false))),
			'PREV_MONTH_URL'	=> $this->helper->route('steve_calendar_month', [
				'month' 	=> $this->date_time->adjust_date('P1M', $year, $this->date_time->month_int($month), constants::FIRST, false), 
				'year' 		=> $month_key == constants::JAN ? $year - 1 : $year]
			),
		]);
		
		return $this;
	}
	
	public function day_next_prev($day, $month, $year)
	{	
		$this_day = $this->date_time->date_key($year, $this->date_time->month_int($month), $day, "D");
		$this->template->assign_vars([
			'DAY_J'			=> $this->date_time->date_key($year, $this->date_time->month_int($month), $day, "jS"),
			'DAY_STRING'	=> $this_day,
			'EVENT_DAY'		=> $this->language->lang(strtoupper($this_day)),
		]);
			
		return $this;				
	}
	
	public function validate_day($day_string, $day)
	{	
		$days = new constants;
		if (!in_array(strtoupper($day_string), $days->days_to_array(false)) || $day_string == '' || strlen($day_string) > (int) 3
			|| !is_numeric($day) || intval($day) == 0 || intval($day) > constants::LAST_D)
		{
			throw new \phpbb\exception\http_exception(403, 'INVALID_ROUTE_DAY');				
		}
		
		return $this;
	}
	
	public function validate_month($month)
	{
		$months = new constants;
		if (!in_array(strtoupper($month), $months->months_to_array(true)))
		{
			throw new \phpbb\exception\http_exception(403, 'INVALID_ROUTE_MONTH');				
		}
		
		return $this;
	}
	
	public function validate_year($year)
	{
		if (!is_numeric($year))
		{
			throw new \phpbb\exception\http_exception(403, 'CALENDAR_YEAR_INVALID');
		}
		if (empty($year))
		{
			throw new \phpbb\exception\http_exception(404, 'CALENDAR_YEAR_EMPTY');		
		}
		
		return $this;
	}
	
	public function default_routes()
	{
		$day = $this->helper->route('steve_calendar_day', ['day_string' => date("D"), 'day'	=> date("d"), 'month' => date('F'), 'year' => date("Y")]);
		$month = $this->helper->route('steve_calendar_month', ['month' => date("F"), 'year' => date("Y")]);
		$year = $this->helper->route('steve_calendar_year', ['year' => date("Y")]);
		
		$calendar_default_link = $year;

		switch ($calendar_default_link)
		{
			case $this->config['calendar_default_link'] === 'YEAR':
				$calendar_default_link = $year;
			break;
			case $this->config['calendar_default_link'] === 'MONTH':
				$calendar_default_link = $month;
			break;
			case $this->config['calendar_default_link'] === 'DAY':
				$calendar_default_link = $day;
			break;
		}		
		
		return $calendar_default_link;
	}
}
