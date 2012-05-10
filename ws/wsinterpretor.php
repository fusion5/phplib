<?
class wsinterpretor 
{
	private
		$db;
	private
		$user;
	private
		$callbacks = array();
	public
		function __construct(db $db, user $user)
		{
			$this->db = $db;
			$this->user = $user;
			foreach($this->db->objectNames as $objectName)
				if ($this->db->$objectName instanceof controller)
				{
					$refl = new ReflectionClass($objectName);
					$methods = $refl->getMethods();
					foreach($methods as $method)
					{
						$declaringClass = $method->getDeclaringClass()->getName();
						$methodName = $method->getName();
						if ($method->isPublic() && $method->isUserDefined())
							if 
							(
								(!in_array ($declaringClass, abstractAccess::$ignoreClasses) || 
								(in_array  ($methodName, abstractAccess::$allowedMethods))) && 
								(!in_array ($methodName, abstractAccess::$disallowedMethods)) &&
								(strstr($method->getDocComment(), '@soap') !== FALSE) // Metoda marcata pentru soap
							)
								$this->callbacks[$method->getName()] = new callback($objectName, $methodName);
					}
				}
		}
	private
		function errorToSoapFault(errors $e)
		{
			return new SoapFault(first($e->getCode(), 'factronic_ws_error'), $e->getMessage());
		}
	public
		function __call($methodName, $arguments)
		{
			try
			{
				if (isset($this->callbacks[$methodName]))
				{
					$callback = $this->callbacks[$methodName];
					$objectName = $callback->getControllerName();
					foreach($arguments as &$argument)
						if ($argument instanceof stdClass)
							$argument = (array)$argument;
					$return = $callback->callArray($arguments);
					return $return;
				}
			}
			catch (errors $e)
			{
				$sf = $this->errorToSoapFault($e);
				throw $sf;
			}
		}
}
?>
