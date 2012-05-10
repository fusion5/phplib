<?
class sqlfunction
{
	private
		$functionName;
	public
		function __construct($functionName)
		{
			$this->setFunctionName($functionName);
		}
	public
		function setFunctionName($name)
		{
			if (!$name)
				throw new errors(g('You must supply a function name!'));
			$this->functionName = $name;
		}
	public
		function getFunctionName()
		{
			return $this->functionName;
		}
	public
		function __toString()
		{
			return strtoupper($this->functionName).'()';
		}
}
?>
