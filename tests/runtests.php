<?
class runtests
{
	private 
		$classes = array();
	public 
		function __construct($classes)
		{
			$this->classes = $classes;
		}
	public
		function run()
		{
			if (count($this->classes))
				foreach($this->classes as $classname)
				{
					$instance = new $classname();
					if ($instance instanceof testcase)
					{
						$instance->run();
					}
					else
						new p('Error: the class ' . $classname . ' doesn\'t seem to be a testcase');
				}
			else
				new p('There are no classes to test!');
		}
}
?>
