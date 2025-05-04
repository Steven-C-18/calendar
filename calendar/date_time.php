<?php
/**
	* Events calendar $extends the phpBB Software package.
	* @copyright (c) 2024, Steve, https://steven-clark.tech/
	* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace steve\calendar\calendar;

class date_time extends \DateTime
{
	protected $config;
	protected $user;

	public $timezone;
	
	const DATE = 'Y-m-d';
	const TIME = 'H:i:s';
	
	/**
		* Constructor
	*/
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\user $user, 
		$time = 'now',
		\DateTimeZone $timezone = null)
	{
		$this->config = $config;
		$this->user	= $user;
		$timezone = $timezone ?: $this->user->timezone;

		parent::__construct($time, $timezone);
	}
	
	public function set_now()
	{
		return $now = new self($this->config, $this->user, "now", $this->set_user_timezone());
	}
	
	public function set_user_timezone()
	{
		$timezone = !empty($this->user->data['user_timezone']) ? $this->user->data['user_timezone'] : $this->config['board_timezone'];
		try
		{
			$timezone = new \DateTimeZone($timezone);
		}
		catch (\Exception $e)
		{
			$timezone =  new \DateTimeZone('UTC');
		}
		return $timezone;		
	}
	
	public function date_key($year, $month, $day, $format)
	{
		$now = $this->set_now();		
		$now->setDate($year, $month, $day);		
		return $now->format($format);		
	}
	
	public function last_day_prev($year, $months)
	{
		$this->format("$year-$months-00");
		return $this->modify("last day of previous month")
			->format("d");
	}
	
	public function adjust_date($int = 'P1M', $year, $month, $day = 01, $add = true)
	{
		$datetime = $this->setDate($year, $month, $day);
		$interval = new \DateInterval($int);

		return $add ? $datetime->add($interval)->format('F') : $datetime->sub($interval)->format('F');
	}

	public function event_timestamp($year, $month, $day, $hour, $min)
	{
		$now = $this->set_now();
		return $now->setDate($year, $month, $day)
			->setTime($hour, $min)
			->setTimezone($this->set_user_timezone())
			->getTimestamp();
	}

	public function now()
	{
		$now = $this->set_now();
		return [
			'now' 		=> $now->getTimeStamp(),
			'year'		=> $now->format("Y"),
			'month' 	=> $now->format("m"),
			'day'		=> $now->format("d"),		
			'time'		=> $now->format(self::TIME),
		];
	}
		
	//date("n", strtotime($month)
	
	public function month_int($month)
	{
		return date("m", strtotime($month));
	}

	public function count_days($now, $year, $month, $day)
	{
		//DATE = 'Y-m-d'
		$test = $year . '-' . $month . '-' . $day;	
		$date1 = new \DateTime("$now");
		$date2 = new \DateTime("$test");

		$interval = $date1->diff($date2);

		return $interval->days;
	}
	
/* 	public function week_days($block, $prefix)
	{	
		$week_days = new constants;
		$week_days = $week_days->days_to_array(true);
		
		foreach ($week_days as $week_day)
		{
			$this->template->assign_block_vars($block, [
				'DAYS'		=> $this->language->lang($week_day . $prefix),
			]);
		}
		unset($week_days);
		
		return $this;
	} */

	////$number = cal_days_in_month(CAL_GREGORIAN, date('m', $this->now), $this->now_year);

	public function convert_to_lang()
	{
		return (string) $this->language->lang(strtoupper($month_string));
	}
}	