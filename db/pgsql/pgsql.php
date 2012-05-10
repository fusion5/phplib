<?
class pgsql extends db 
{
	protected static
		$instance = null;
	protected
		$objectsByOID;
	public
		$user;
	const 
		debug = false;
	public
		function __construct($host, $defaultUsername, $defaultPassword, $schemata, $newlink = false) 
		{
			benchmark::getInstance()->bookmark('database', 'Constructing database instance...');
			parent::__construct($host, $defaultUsername, $defaultPassword, $schemata, $newlink);
			$user_class = USER_CLASS;
			$this->user = new $user_class($this, $defaultUsername, $defaultPassword);
			central($this);
			$this->objects[USER_CLASS] = $this->user;
			foreach($this->objects as $object)
			{
				$callback = array($object, 'initialize');
				if (is_callable($callback))
					call_user_func($callback);
			}
			benchmark::getInstance()->bookmark('database', 'Database objects initialized!');
		}
	private
		function getConnectionString()
		{
			$login = array (
				'host' => $this->host,
				'port' => 5432,
				'user' => $this->databaseUser,
				'password' => $this->databasePassword,
				'dbname' => $this->schemata
			);
			$return = '';
			foreach($login as $key => $value)
				$return .= $key . '=\'' . $value . '\' ';
			return $return;
		}
	public
		function getUser()
		{
			return $this->user;
		}
	public
		function connect($username, $password)
		{
			$this->databaseUser = $username;
			$this->databasePassword = $password;
			benchmark::database('Logging in with: ' . $username . ' ' . $password);
			benchmark::getInstance()->bookmark('database', 'Connecting to database...');
			if ($dbResource = pg_connect($this->getConnectionString()))
				$this->dbResource = $dbResource;
			else
				throw new errors('Could not connect to database: Wrong or missing database connection information');
			benchmark::getInstance()->bookmark('database', 'Connected to database...');
			$this->isConnected = true;
			benchmark::getInstance()->bookmark('database', 'Populating database objects (reading from database)...');
			$fields = $this->query('SELECT * FROM phplib_field');
			benchmark::getInstance()->bookmark('database', 'Populated objects!');
			if (!pg_num_rows($fields))
				throw new errors(g('There are no tables or views defined in the database!'));
			try
			{
				while ($fieldRow = pg_fetch_assoc($fields)) 
				{
					$objectName = $fieldRow['relname'];
					if (!isset($this->objects[$objectName]))
					{
						$objectInstance = $this->getObject($objectName);
						$this->objects[$objectName] = $objectInstance;
						$this->objectsByOID[$fieldRow['classid']] = $objectInstance;
						$this->objectNames[] = $objectName;
					}
					else
						$objectInstance = $this->objects[$objectName];
					$objectInstance->addField($this->getFieldInstance($objectInstance, $fieldRow));
				}
				benchmark::getInstance()->bookmark('database', 'Populating database constraints (reading from database)...');
				$constraints = $this->query('SELECT * FROM phplib_constraint');
				benchmark::getInstance()->bookmark('database', 'Populated constraints!');
				while ($constraint = pg_fetch_assoc($constraints))
				{
					switch($constraint['contype'])
					{
					case 'f': // Foreign key constraint
						$localObject = $this->objectsByOID[$constraint['local_object']];
						$foreignObject = $this->objectsByOID[$constraint['foreign_object']];
						$localFieldNums = pgsql::getArray($constraint['local_keys']);
						$foreignFieldNums = pgsql::getArray($constraint['foreign_keys']);
						$localObject->addForeignKey($localFieldNums, $foreignObject, $foreignFieldNums, $constraint['on_update'], $constraint['on_delete']);
						break;
					case 'p': // Primary key constraint
						$localObject = $this->objectsByOID[$constraint['local_object']];
						$localObject->setPrimaryKeyNums(pgsql::getArray($constraint['local_keys']));
						break;
					}
				}
			}
			catch (errors $e)
			{
				trace($e->getMessage());
				throw $e;
			}
			benchmark::getInstance()->bookmark('database', 'Populated database objects!');
		}
	public
		function getFieldInstance(dbo $dbo, array $fieldRow)
		{
			$type = $fieldRow['typname'];
			$name = $fieldRow['attname'];
			$general_class_name = 'pg_' . $type . '_field';
			$class_name = $name . '_field';
			if (classdef_exists($class_name))
				$return = new $class_name($dbo, $fieldRow);
			else
			if (classdef_exists($general_class_name))
				$return = new $general_class_name($dbo, $fieldRow);
			else
				throw new errors(gf('Cannot find class definition for the field %s (Looked after %s and then %s)', $name, $class_name, $general_class_name));
			if (!($return instanceof databasefield))
				throw new errors(gf('The class definition for the field %s (%s) was found, but it is not an instance of databasefield!', $name, get_class($return)));
			$return->setName($name);
			$return->setMayBeNull($fieldRow['attnotnull'] != 't');
			$return->setDefault($fieldRow['atthasdef']);
			$return->setNum($fieldRow['attnum']);
			$return->setInfo($fieldRow['atttypmod']);
			$return->setType($type);
			return $return;
		}
	public
		function getArray($pgArrayString)
		{
			return split(',', ereg_replace('[{-}]', '', $pgArrayString));
		}
	public 
		function disconnect() 
		{
			if ($this->isConnected()) 
			{
				parent::disconnect();
			}
		}
	public
		function filter($string)
		{
			return pg_escape_string($string);
		}
	public	
		function query() 
		{
			if ($this->isConnected()) 
			{
				$args = func_get_args();
				$qstring = array_shift($args);
				benchmark::database('Querying: ' . $qstring);
				if (DEBUG_MODE() && pgsql::debug)
					trace('Querying: '. $qstring . ' - Parameters: ' . print_r($args, true));
				$result = pg_query_params($this->getRes(), $qstring, $args);
				if ($result) 
				{
					if (DEBUG_MODE() && pgsql::debug)
						trace('Query success!');
					$result_error = pg_result_error($result);
					if ($result_error)
						throw new errors($result_error);
					return $result;
				}
				else 
				{
					throw new errors(pg_last_error($this->getRes()));
				}
			} 
			else
				throw new errors("Cannot query database because I'm not connected to it");
		}
	public
		function queryAssoc()
		{
			$assoc = array();
			$args = func_get_args();
			$dbResource = call_user_func_array(array($this, 'query'),	$args);
			while($record = pg_fetch_assoc($dbResource))
			{
				$assoc[] = $record;
			}
			pg_free_result($dbResource);
			return $assoc;
		}
	public
		function selectString($dboName, array $assoc)
		{
			if (!$this->isConnected)
				throw new errors('Not conencted to the database');
			return pg_select($this->getRes(), $dboName, $assoc, PGSQL_DML_STRING);
		}
	public
		function updateString($dboName, array $assoc, array $conditions)
		{
			if (!$this->isConnected)
				throw new errors('Not conencted to the database');
			$update_string = pg_update($this->getRes(), $dboName, $assoc, $conditions, PGSQL_DML_STRING | PGSQL_CONV_IGNORE_DEFAULT);
			return $update_string;
		}
	public
		function insertString($dboName, array $assoc)
		{
			if (!$this->isConnected)
				throw new errors('Not conencted to the database');
			$insert_string = pg_insert($this->getRes(), $dboName, $assoc, PGSQL_DML_STRING);
			if (!$insert_string)
				throw new errors(g('Insert failed!'));
			$insert_string = str_replace(';', '', $insert_string) . ' RETURNING *;';
			return $insert_string;
		}
	public
		function deleteString($dboName, array $assoc)
		{
			if (!$this->isConnected)
				throw new errors('Not conencted to the database');
			return pg_delete($this->getRes(), $dboName, $assoc, PGSQL_DML_STRING);
		}
	public 
		function startTransaction()
		{
			$this->query('BEGIN');
		}
	public 
		function commit()
		{
			$this->query('COMMIT');
		}
	public 
		function rollback()
		{
			$this->query('ROLLBACK');
		}
	public
		function tryCreateTable($ctString)
		{
			return false;
		}
}
?>
