<?
class mysql5 extends mysql
{
	const 
		debug = false;
	public
		function debug()
		{
			return self::debug;
		}
	public 
		function connect($username, $password) 
		{
			$this->databaseUser = $username;
			$this->databasePassword = $password;
			benchmark::getInstance()->bookmark('database', 'Connecting to database...');
			if (!($this->dbResource = mysql_pconnect($this->host, $this->databaseUser, $this->databasePassword))) 
				throw new errors('Could not connect to database: Wrong or missing database connection information');
			if (!mysql_select_db($this->schemata, $this->getRes()))
				throw new errors('Could not select database: ' . $this->schemata);
			mysql_query("SET NAMES 'utf8'", $this->getRes());
			$this->isConnected = true;
			benchmark::getInstance()->bookmark('database', 'Getting version (determining if recreating the cache is required)...');
			$version = $this->databaseVersion();
			$this->objects = $this->loadCachedSchema($version);
			benchmark::getInstance()->bookmark('database', 'Loaded cached schema');
			if ($this->objects == false)
			{
				benchmark::getInstance()->bookmark('database', 'Creating the cache...');
				$fields = $this->query('SELECT 
					TABLE_NAME, COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, NUMERIC_PRECISION, NUMERIC_SCALE, CHARACTER_MAXIMUM_LENGTH, EXTRA
					FROM information_schema.columns WHERE table_schema = ?', $this->schemata);
				if (!mysql_num_rows($fields))
					throw new errors(g('There are no tables or views defined in the database!'));
				try
				{
					while ($fieldRow = mysql_fetch_assoc($fields)) 
					{
						$objectName = $fieldRow['TABLE_NAME'];
						if (!isset($this->objects[$objectName]))
						{
							$objectInstance = $this->getObject($objectName);
							$this->objects[$objectName] = $objectInstance;
							$this->objectNames[] = $objectName;
						}
						else
							$objectInstance = $this->objects[$objectName];
						$objectInstance->addField($this->getFieldInstance($objectInstance, $fieldRow));
					}
					$constraints = $this->query('SELECT * FROM phplib_constraint');
					if (!mysql_num_rows($fields))
						throw new errors(g('Could not obtain constraints information from phplib_constraint!'));
					while ($constraint = mysql_fetch_assoc($constraints))
					{
						if ($constraint['CONSTRAINT_NAME'] == 'PRIMARY')
						{
							$object = $this->objects[$constraint['TABLE_NAME']];
							$object->setPrimaryKey($constraint['COLUMN_NAME']);
						}
						else if (isset($constraint['REFERENCED_TABLE_NAME']) && isset($constraint['REFERENCED_COLUMN_NAME']))
						{
							$localObject = $this->objects[$constraint['TABLE_NAME']];
							$localField = $localObject->field($constraint['COLUMN_NAME']);
							$foreignObject = $this->objects[$constraint['REFERENCED_TABLE_NAME']];
							$foreignField = $foreignObject->field($constraint['REFERENCED_COLUMN_NAME']);
							$localField->setForeignKey($foreignObject, $foreignField, $constraint['UPDATE_RULE'], $constraint['DELETE_RULE']);
						}
					}
				}
				catch (errors $e)
				{
					trace($e->getMessage());
					throw $e;
				}
				$this->cacheSchema($this->objects, $version);
				benchmark::getInstance()->bookmark('database', 'Cache created');
			}
			else
			{
			}
		}
	private
		function databaseVersion()
		{
			if (!$this->isConnected())
				return false;
			benchmark::getInstance()->bookmark('database', 'Getting last updates');
			$updates = $this->query('SHOW GLOBAL STATUS WHERE 
				Variable_name = \'Com_alter_table\' OR
				Variable_name = \'Com_alter_db\' OR
				Variable_name = \'Com_create_table\' OR
				Variable_name = \'Com_create_db\' OR
				Variable_name = \'Com_drop_table\' OR
				Variable_name = \'Com_drop_db\'
				');
			$count = 0;
			while($update = mysql_fetch_assoc($updates)) 
				$count += $update['Value'];
			benchmark::getInstance()->bookmark('database', 'Last updates got: ' . $count);
			return $count;
		}
	public
		function getFieldInstance(dbo $dbo, array $fieldRow)
		{
			$type = $fieldRow['DATA_TYPE'];
			$name = $fieldRow['COLUMN_NAME'];
			$class_name = $name . 'Field';
			$general_class_name = 'my' . ucfirst($type) . 'Field';
			if (classdef_exists($class_name))
				$return = new $class_name($dbo, $fieldRow);
			else
			if (classdef_exists($general_class_name))
				$return = new $general_class_name($dbo);  // Don't pass the fieldRow, because we don't need it
			else
				throw new errors(gf('Cannot find class definition for the field %s (Looked after %s and then %s)', $name, $class_name, $general_class_name));
			if (!($return instanceof databasefield))
				throw new errors(gf('The class definition for the field %s (%s) was found, but it is not an instance of databasefield!', $name, get_class($return)));
			$return->setName($name);
			$return->setMayBeNull($fieldRow['IS_NULLABLE'] == 'YES');
			if ($fieldRow['EXTRA'] == 'auto_increment')
				$return->setDefault(true);
			else
				$return->setDefault($fieldRow['COLUMN_DEFAULT']);
			$return->setInfo($fieldRow);
			$return->setType($type);
			return $return;
		}
}
?>
