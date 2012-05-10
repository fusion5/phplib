<?
class benchmark
{
	private static
		$instance;
	private
		$categories = array('core', 'database'); // Possible value: core, database
	public static 
		function getInstance() 
		{
			if (!isset(self::$instance)) self::$instance = new self();
			return self::$instance;
		}
	private
		$time;
	private
		$bookarks;
	public
		function __construct()
		{
			$this->time = microtime(true);
			$this->bookmark('core', 'Benchmark started');
		}
	public
		function bookmark($category, $label)
		{
			if (in_array($category, $this->categories))
			{
				$delta = microtime(true) - $this->time;
				$delta = number_format($delta, 3);
				$this->bookarks[] = 'At ' . $delta . ' started:			' . $label;
			}
		}
	public static
		function database($label)
		{
			benchmark::getInstance()->bookmark('database', $label);
		}
	public static
		function core($label)
		{
			benchmark::getInstance()->bookmark('core', $label);
		}
	public
		function __destruct()
		{
			$this->bookmark('core', 'Benchmark ended');
			$logfile = APP_NAME . CRLF;
			foreach($this->bookarks as $bookmark)
			{
				$logfile .= ' ' . $bookmark . CRLF;
			}
			if (defined('BENCHMARK_FILE'))
				file_put_contents(BENCHMARK_FILE, $logfile);
		}
}
?>
