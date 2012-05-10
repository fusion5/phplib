<?
class testcase
{
	private
		$fullErrors = false;
	public function run()
	{
		print '<h1>'.('Running testcase <em>' . get_class($this) . '</em>') . '</h1>';
		$reflector = new ReflectionClass($this);
		$methods = $reflector->getMethods();
		foreach($methods as $method)
			if (
				($method->getDeclaringClass()->getName() == get_class($this)) &&
				($method->isPublic()) && 
				!in_array($method->getName(), array('__construct', '__destruct'))
			)
			{
				$call = $method->getName();
				print '<h2>' . ($call) . '</h2>';
				try
				{
					$this->$call();
					$this->passed($call);
				}
				catch (Exception $e)
				{
					$this->failed($call, $e);
				}
			}
	}
	public function showFullErrors($b)
	{
		$this->fullErrors = $b;
	}
	private function passed($methodName)
	{
		new p('<strong>PASSED!</strong>', array('style' => 'color:green'));
	}
	private function failed($methodName, Exception $e)
	{
		new p('<strong style="color:red">FAILED!</strong> ' . $e->getMessage());
		if ($this->fullErrors)
			trace($e);
	}
	protected
		function assertEquals($variable, $expected)
		{
			if ($variable !== $expected)
				throw new Exception('Assertion failed! Expected value: <strong>'.var_export($expected, true).'</strong> Gooten value: <strong>'.var_export($variable, true).'</strong>');
		}
}
?>
