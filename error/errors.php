<?
class errors extends Exception 
{
	private
		$field_name = null;
	private
		$value = null;
	private
		$description = '';
	private
		$heading = '';
	public 
		function __construct($message = null, $field_name = null, $code = null, $value = null) 
		{
			$this->field_name = $field_name;
			$this->value = $value;
			parent::__construct($message, $code);
		}
	public
		function getValue()
		{
			return $this->value;
		}
	public static
		function printStackTable($traceArray)
		{
			if (is_array($traceArray) && count($traceArray))
			{
				$table = new table();
				$tr = new tr();
					new th('File');
					new th('Call');
					new th('Args');
				unset ($tr);
					foreach($traceArray as $trace)
					{
						$tr = new tr();
							if (isset($trace['file']) && isset($trace['line']))
								new td($trace['file'] . '('.$trace['line'].')');
							else
								new td();
							$td = new td();
							$disp = '';
							if (isset($trace['class']))
								 $disp .= $trace['class'];
							if (isset($trace['type']))
								$disp .= $trace['type'];
							if (isset($trace['function']))
								$disp .= $trace['function'];
							print $disp;
							unset($td);
							if (!in_array($disp, array(
								'mysql->__construct',
								'mysql->connect',
								'mysql5->__construct',
								'mysql5->connect',
								'pgsql->connect',
								'pgsql->__construct'
							)))
								new td('<pre>' . self::displayArgs(&$trace['args']) . '</pre>');
							else
								new td('n/a');
						unset ($tr);
					}
				unset ($table);
			}
		}
	public static
		function displayArgs($args)
		{
			$return = '';
			if (!is_null($args))
				foreach($args as $key => $argument)
				{
					if (is_string($key))
						$return .= $key.'=';
					$return .= '(';
					if (is_array($argument))
						$return .= self::displayArgs($argument);
					else
					if (is_object($argument))
						$return .= 'instance of ' . get_class($argument);
					 else
						 $return .= var_export($argument, true);
					$return .= ') ';
				}
			return $return;
		}
	public
		function __toString()
		{
			ob_start();
			self::printStackTable($this->getTrace());
			$return = ob_get_contents();
			ob_end_clean();
			$return = preg_replace('/(mysql|pgsql)->__construct\([^\)]*\)/m', '***', $return);
			return $return;
		}
	public
		function setDescription($description)
		{
			$this->description = $description;
		}
	public
		function setHeading($heading)
		{
			$this->heading = $heading;
		}
	public
		function display()
		{
			$div = new div(array('class' => 'errors'));
			if (result()->heading)
				new h1(result()->heading);
			if (result()->description)
				new p(result()->description);
				$ul = new ul();
				$li = new li();
				print $this->getMessage(); 
				unset ($li);
				unset ($ul);
			unset ($div);
		}
}
?>
