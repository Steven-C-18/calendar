<?php
/**
	* Events calendar $extends the phpBB Software package.
	* @copyright (c) 2024, Steve, https://steven-clark.tech/
	* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace steve\calendar\controller;

class search
{
	protected $auth;
	protected $config;
	protected $db;
	protected $helper;
	protected $language;
	protected $pagination;
	protected $request;
	protected $template;
	protected $user;
	
	protected $date;
	protected $calendar_events;
	
	protected $now;
	protected $min;
	protected $max;
	protected $max_words;
	protected $per_page;
		
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,		
		\phpbb\db\driver\driver_interface $db,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language,
		\phpbb\pagination $pagination,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		
		\steve\calendar\calendar\calendar $calendar,
		\steve\calendar\calendar\date_time $date,
		$calendar_events)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->helper = $helper;
		$this->language = $language;
		$this->pagination = $pagination;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->calendar = $calendar;
		
		$this->date_time = $date;
		$this->table_calendar_events = $calendar_events;

		$this->language->add_lang('search');

		if (!$this->auth->acl_get('u_search') || !$this->auth->acl_get('u_calendar_search') || !$this->config['load_search'])
		{
			throw new \phpbb\exception\http_exception(403, 'NO_SEARCH');
		}
		
		$now = $this->date_time->now();
		$this->now = $now['now'];
		$this->now_year = $now['year'];
		$this->now_month = $now['month']; 
		$this->now_day = $now['day'];	
		
		$this->min = $min = (int) 3;
		$this->max = $max = (int) 100;
		$this->max_words = $max_words = intval($this->config['max_num_search_keywords']);
		$this->per_page = $per_page = intval($this->config['calendar_event_limit']);
	}
	
	private function get_words()
	{
		return $this->request->variable('calendar_keywords', '', true);
	}
	
	private function generate_keywords($keywords)
	{
		$message = $this->language->lang('NO_KEYWORDS', $this->language->lang('CHARACTERS', $this->min), $this->language->lang('CHARACTERS', $this->max));
		if (strlen($keywords) < $this->min || empty($keywords))
		{
			throw new \phpbb\exception\http_exception(403, $message);
		}

		$keywords = explode(' ', preg_replace('#\s+#u', ' ', utf8_strtolower($keywords)));
		$total_keywords = count($keywords);		
		
		if (!empty($this->max_words) && $total_keywords > $this->max_words)
		{
			throw new \phpbb\exception\http_exception(403, $this->language->lang('MAX_NUM_SEARCH_KEYWORDS_REFINE', $this->max_words));
		}

		$i = 0;	
		for ($i, $total_keywords; $i < $total_keywords; $i++)
		{
			if (strlen($keywords[$i]) < $this->min)
			{
				throw new \phpbb\exception\http_exception(403, $message);
			}

			$keywords[$i] = $this->db->sql_like_expression($this->db->get_any_char() . $keywords[$i] . $this->db->get_any_char());
		}

		$sql_keywords = ' WHERE (';
		$sql_lower = $this->db->sql_lower_text('c.title');
		$sql_keywords .= " $sql_lower " . implode(" OR $sql_lower ", $keywords) . ' )';
		
		unset($keywords[$i]);
		
		return (string) $sql_keywords;
	}
	
	private function search_query_count()
	{
		$this->keywords = $this->get_words();
		$this->page = $this->request->variable('page', 0);
		$this->year = $this->request->variable('year', 0);
		
		$sql = ' SELECT COUNT(event_id) AS count_events
			FROM ' . $this->table_calendar_events . ' c
				' . $this->generate_keywords($this->keywords);
		$result = $this->db->sql_query($sql);
		$count_events = $this->db->sql_fetchfield('count_events');
		$this->db->sql_freeresult($result);

		return (int) $count_events;
	}
	
	private function search_query()
	{
		$this->found = $this->search_query_count();
		if (empty($this->found))
		{
			return false;
		}
		//AND year = ' . (int) $this->year;
		$sql = 'SELECT c.*, u.user_id, u.username, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height
			FROM ' . $this->table_calendar_events . ' c LEFT JOIN ' . USERS_TABLE . ' u ON c.user_id = u.user_id
				' . $this->generate_keywords($this->keywords) . '
				ORDER BY year DESC, month DESC, day DESC';
		$result = $this->db->sql_query_limit($sql, $this->per_page, $this->page);

		$results = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (empty($row))
			{
				continue;
			}
			$results[(int) $row['event_id']] = $row;
		}
		$this->db->sql_freeresult($result);
			
		return $results;
	}
	
	public function search($action)
	{
		$this->calendar->view_events($this->search_query(), 0, $this->now_year, $route = '', 0);

		$this->pagination->generate_template_pagination($this->helper->route('steve_calendar_search', ['action' => 'results']) . '?calendar_keywords=' . $this->keywords, 'pagination', 'page', $this->found, $this->per_page, $this->page);		
		
		$this->template->assign_vars([
			'FOUND'				=> $this->language->lang('FOUND_SEARCH_MATCHES', $this->found),
			'COUNT'				=> $this->found,
			'SEARCH_CALENDAR'	=> true,
			'NOW_URL'			=> $this->helper->route('steve_calendar_day', ['day_string' => date("D", $this->now), 'day'	=> $this->now_day, 'month' => date('F', $this->now), 'year' => $this->now_year]),
			'NOW_DAY'			=> $this->now_day,
			'NOW_MONTH'			=> $this->now_month,
			'NOW_YEAR'			=> $this->now_year,
			'U_ADD_EVENT'		=> $this->auth->acl_get('u_add_calendar_event') ? $this->helper->route('steve_calendar_add_event', ['action' => 'add', 'event_id' => 0]) : false,	
		]);
		
		return $this->helper->render('calendar_search.html', $this->language->lang('SEARCH') . ' ' . $this->language->lang('CALENDAR'));
	}
}
