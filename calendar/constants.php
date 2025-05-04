<?php
/**
	* Events calendar $extends the phpBB Software package.
	* @copyright (c) 2024, Steve, https://steven-clark.tech/
	* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace steve\calendar\calendar;

class constants
{	
	const FIRST = 1;
	const LAST_L = 28;
	const LAST_D = 31;
	const LAST_M = 12;
	
	const MON = 'MONDAY';
	const TUE = 'TUESDAY';
	const WED = 'WEDNESDAY';
	const THU = 'THURSDAY';
	const FRI = 'FRIDAY';
	const SAT = 'SATURDAY';
	const SUN = 'SUNDAY';
	
	const MON_D = 'MON';
	const TUE_D	= 'TUE';
	const WED_D	= 'WED';
	const THU_D	= 'THU';
	const FRI_D = 'FRI';
	const SAT_D	= 'SAT';
	const SUN_D	= 'SUN';
	
	const JAN = 'JANUARY';
	const FEB = 'FEBRUARY';
	const MAR = 'MARCH';
	const APR = 'APRIL';
	const MAY = 'MAY';//\
	const JUN = 'JUNE';
	const JUL = 'JULY';
	const AUG = 'AUGUST';
	const SEP = 'SEPTEMBER';
	const OCT = 'OCTOBER';
	const NOV = 'NOVEMBER';
	const DEC = 'DECEMBER';
	//upper
	const JAN_M	= 'Jan';
	const FEB_M	= 'Feb';
	const MAR_M	= 'Mar';
	const APR_M	= 'Apr';
	const MAY_M = 'May';//\
	const JUN_M	= 'Jun';
	const JUL_M = 'Jul';
	const AUG_M	= 'Aug';
	const SEP_M	= 'Sep';
	const OCT_M	= 'Oct';
	const NOV_M	= 'Nov';
	const DEC_M = 'Dec';
	
	public function months_to_array($full = true)//key 1-12 => month1
	{
		$months = [self::JAN, self::FEB, self::MAR, self::APR, self::MAY, self::JUN, self::JUL, self::AUG, self::SEP, self::OCT, self::NOV, self::DEC];
		$months_m = [self::JAN_M, self::FEB_M, self::MAR_M, self::APR_M, self::MAY_M, self::JUN_M, self::JUL_M, self::AUG_M, self::SEP_M, self::OCT_M, self::NOV_M, self::DEC_M];
	
		return $full ? $months : $months_m;
	}

	public function days_to_array($full = true)//key 1-7 => day1
	{			
		$days = [self::MON, self::TUE, self::WED, self::THU, self::FRI, self::SAT, self::SUN];
		$days_d = [self::MON_D, self::TUE_D, self::WED_D, self::THU_D, self::FRI_D, self::SAT_D, self::SUN_D];
		
		return $full ? $days : $days_d;
	}	
}	