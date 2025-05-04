<?php
/**
	* Events calendar $extends the phpBB Software package.
	* @copyright (c) 2024, Steve, https://steven-clark.tech/
	* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace steve\calendar\cron\task;

class calendar_cron_task extends \phpbb\cron\task\base
{
	protected $config;
	protected $events;
	
	public function __construct(
		\phpbb\config\config $config, 
		\steve\calendar\calendar\upcoming_events $events)
	{
		$this->config = $config;
		$this->events = $events;
	}

	public function run()
	{
		$this->events->get_events();
		$this->config->set('calendar_cron_task_last_gc', time());
	}

	public function should_run()
	{
		return $this->config['calendar_cron_task_last_gc'] < time() - $this->config['calendar_cron_task_gc'];
	}
}
