<?
abstract class db 
{
	protected
		$dbResource = null;
	public
		$isConnected = false;
	public 
		$objects = array();
	public 
		$objectNames = array();
	public
		$schemata; 
	protected
		$databaseUser;
	protected
		$databasePass;
	protected
		$host;
	public
		function __construct($host, $defaultUsername, $defaultPassword, $schemata, $newlink = false) 
		{
			$this->host = $host;
			$this->databaseUser = $defaultUsername;
			$this->databasePassword = $defaultPassword;
			$this->schemata = $schemata;
		}
	public
		function __destruct()
		{
			$this->disconnect();
		}
 	public 
		function __clone() 
		{
			trigger_error('Clone is not allowed.', E_USER_ERROR);
		}	
	public function getObject($objectName)
	{
		try
		{
			if (classdef_exists($objectName))
			{
				$test = new $objectName($this);
				if ($test instanceof controller) 
				{
					return $test;
				}
				else
					new p(g('The controller was found but it\'s not an instance of controller ' . $objectName));
			}
		}
		catch (errors $e)
		{
			trace('Error instancing the controller for `'.$objectName.'`!');
			new p($e->getMessage());
			throw ($e);
		}
		return new dbo($objectName, $this);
	}
	public 
		function hasObject($objectName)
		{
			return isset($this->objects[$objectName]);
		}
	public
		function getUser()
		{
			if (isset($this->objects[USER_CLASS]))
				return $this->objects[USER_CLASS];
		}
	public
		function getDatabaseUser()
		{
			return $this->databaseUser;
		}
	public
		function getDatabasePassword()
		{
			return $this->databasePassword;
		}
	public
		function getSchemata()
		{
			return $this->schemata;
		}
	public 
		function disconnect() 
		{
			if ($this->isConnected())
			{
				$this->isConnected = false;
				return true;
			}
			return false;
		}
	public	
		function getRes()
		{
			return $this->dbResource;
		}
	public 
		function isConnected() 
		{
			return $this->isConnected === true; 
		}
	public
		function executeBatch($filename, array $parameters = null)
		{
			$file = './sql/' . $filename;
			if (file_exists($file))
			{
				$insertUserScript = file_get_contents($file);
				$mysqlParameters = array();
				if (count($parameters))
					foreach($parameters as $key => $value)
						if (is_string($value))
							$mysqlParameters[$key] = $this->filter($value);
				$insertUserScript = replace($insertUserScript, $mysqlParameters);
				$statements = split(';', $insertUserScript);
				foreach ($statements as $statement)
				{
					$statement = trim($statement);
					if (strlen($statement))
					{
						$this->query($statement);
					}
				}
			}
		}
	public
		function __get($name)
		{
			if (isset($this->objects[$name]))
				return $this->objects[$name];
			else
			{
				try
				{
					$object = $this->tryCreateTable($name);
					if ($object !== false)
						return $object;
				}
				catch(errors $e)
				{
					if (DEBUG_MODE())
						trace($e->getMessage());
				}
			}
			if (DEBUG_MODE())
				print "Empty attribute called: db::$$name";
		}
	public
		function getField($name)
		{
			foreach($this->objects as $object)
			{
				$fields = $object->fields();
				foreach ($fields as $field)
					if ($field->getName() == $name)
						return $field;
			}
		}
	protected
		function loadCachedSchema($lastVersion)
		{
			$cachefile = CACHE_DIR . 'schema.txt';
			$cache = @unserialize(file_get_contents($cachefile));
			if ($cache === false)
				return false;
			if (!$cache instanceof ArrayObject)
				return false;
			$cacheArray = $cache->getArrayCopy();
			if (!isset($cacheArray['version']))
				return false;
			if ($cacheArray['version'] != $lastVersion)
				return false;
			$objects = $cache['objects'];
			foreach($objects as $object)
			{
				if (!$object instanceof dbo)
					return false;
				if ($object instanceof controller)
					$object->__construct($this);
				else if ($object instanceof dbo)
					$object->__construct($object->dboName(), $this);
			}
			return $objects;
		}
	protected
		function cacheSchema($objects, $version)
		{
			$cachefile = CACHE_DIR . 'schema.txt';
			$cache = array(
				'version' => $version,
				'objects' => $objects
			);
			file_put_contents($cachefile, serialize(new ArrayObject($cache)));
		}
	abstract public function connect($username, $password);
	abstract public	function query();
	abstract public function queryAssoc();
	abstract public function selectString($dboName, array $assoc);
	abstract public function updateString($dboName, array $assoc, array $conditions);
	abstract public function insertString($dboName, array $assoc);
	abstract public function deleteString($dboName, array $assoc);
	abstract public function startTransaction();
	abstract public function commit();
	abstract public function rollback();
	abstract protected function tryCreateTable($objectName);
}
?>
