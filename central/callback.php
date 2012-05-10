<?
class callback
{
	private
		$controllerName;
	private
		$methodName;
	public 
		function __construct($controller = '', $methodName = null)
		{
			if (is_string($controller)) $this->setControllerName($controller);
			if (is_object($controller)) $this->setController($controller);
			if (!isset($methodName))
				throw new errors('No method name set!');
			if (is_string($methodName)) 
				$this->setMethodName($methodName);
		}
	public 
		function setController($controller)
		{
			if ($controller instanceof dbo)
				$this->setControllerName($controller->dboName());
			else
				$this->setControllerName(get_class($controller));
		}
	public 
		function call($firstParam)
		{
			$call = $this->getCallback();
			if (is_callable($call))
				return call_user_func($call, $firstParam);
			else
				throw new errors('The current state contains an invalid callback ');
		}
	public 
		function callArray(array $params)
		{
			$call = $this->getCallback();
			file_put_contents('soapdebug.txt', 'callArray...');
			if (is_callable($call))
				return call_user_func_array($call, $params);
			else
				throw new errors('The current state contains an invalid callback ');
		}
	private
		function getCallback()
		{
			$call = array();
			if (!isset($this->controllerName))
				return false;
			$dbo = &central()->db->objects[$this->controllerName];
			if (isset($dbo))
				$call = array($dbo, $this->methodName);
			else
				$call = array(new $this->controllerName, $this->methodName);
			if (count($call))
				return $call;
			else
				return false;
		}
	public 
		function setControllerName($name)
		{
			if (!is_string($name))
				throw new errors(g('The name parameter must be a string'));
			if (classdef_exists($name))
			{
				$this->controllerName = $name;
			}
			else
			{
				if (isset(central()->db))
					$dbo = central()->db->objects[$name];
				if (isset($dbo))
				{
					$class = get_class($dbo);
					$this->controllerName = $name;
				}
				else
					throw new errors(gf('The controller %s was not found', $name));
			}
		}
	public 
		function __toString()
		{
			return 'callback: ' . $this->controllerName.'->'.$this->methodName;
		}
	public 
		function getControllerName()
		{
			return $this->controllerName;
		}
	public 
		function setMethodName($method)
		{
			if (!is_string($method))
				throw new errors(g('The callback method must be a string'));
			$this->methodName = $method;
		}
	public 
		function getMethodName()
		{
			return $this->methodName;
		}
	public 
		function getSerialized()
		{
			return urlencode(serialize($this));
		}
	public 
		function hasPublicAccess()
		{
			static $hpa;
			if (!isset($hpa[$this->getControllerName()][$this->getMethodName()])) 
			{
				if (classdef_exists($this->getControllerName()))
				{
					$reflectionClass = new ReflectionClass($this->getControllerName());
					$method = $reflectionClass->getMethod($this->getMethodName());
					$docComment = $method->getDocComment();
					$hpa[$this->getControllerName()][$this->getMethodName()] = (boolean)preg_match('|@public_access|', $docComment);
				}
				else
					$hpa[$this->getControllerName()][$this->getMethodName()] = false;
			}
			return $hpa[$this->getControllerName()][$this->getMethodName()];
		}
	public static 
		function getUnserialized($s)
		{
			if (is_string($s))
			{
				$o = unserialize(urldecode($s));
				if ($o == null)
					throw new errors('Invalid string - can\'t unserialize callback - it\'s null!');
				if (!$o instanceof callback)
					throw new errors('Invalid callback - it\'s not an instance of callback!');
				return $o;
			}
			else
				return null;
		}
}
?>
