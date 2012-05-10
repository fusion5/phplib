<?
class mysql extends db 
{
	const 
		debug = false;
	protected
		$_performingInsert;
	public
		function __construct($host, $defaultUsername, $defaultPassword, $schemata, $newlink = false) 
		{
			parent::__construct($host, $defaultUsername, $defaultPassword, $schemata, $newlink);
			if (!$this->isConnected())
				$this->connect($defaultUsername, $defaultPassword);
			if (defined('USER_CLASS') && isset($this->objects[USER_CLASS]))
				$this->user = $this->objects[USER_CLASS];
			central($this);
			foreach($this->objects as $object)
				if ($object instanceof iuser)
				{
					$callback = array($object, 'initialize');
					if (is_callable($callback))
						call_user_func($callback);
				}
			foreach($this->objects as $object)
				if (!($object instanceof iuser))
				{
					$callback = array($object, 'initialize');
					if (is_callable($callback))
						call_user_func($callback);
				}
		}
	public 
		function connect($username, $password) 
		{
			$this->databaseUser = $username;
			$this->databasePassword = $password;
			if (!($this->dbResource = mysql_connect($this->host, $this->databaseUser, $this->databasePassword))) 
				throw new errors('Could not connect to database: Wrong or missing database connection information');
			if (!mysql_select_db($this->schemata, $this->getRes()))
				throw new errors('Could not select database: ' . $this->schemata);
			mysql_query("SET NAMES 'utf8'", $this->getRes());
			$this->isConnected = true;
			$tables = $this->query('SHOW FULL TABLES;');
			if (!mysql_num_rows($tables))
				throw new errors('There aren\'t any tables in the database!');
			$items = array();
			$foreignKeys = array();
			while ($table = mysql_fetch_assoc($tables)) 
			{
				$type = $table['Table_type'];
				if (strtolower($type) == 'view') 
					continue;
				$objectName = $table['Tables_in_'.$this->schemata];
				$this->objectNames[] = $objectName;
				if (!isset($this->objects[$objectName]))
					$this->objects[$objectName] = $this->getObject($objectName);
				$objectInstance =& $this->objects[$objectName];
				$ct = mysql_fetch_assoc($this->query("SHOW CREATE TABLE `$objectName`"));
				$ctString = &$ct['Create Table'];
				if (!$ctString)
					throw new errors("Could not obtain CREATE TABLE value for $objectName");
				$objects = $this->parseCreateTable($ctString);
				foreach($objects['fields'] as $num => $declaration)
					$objectInstance->addField($this->parseColumnDefinition($objectInstance, $declaration, $num));
				foreach($objects['pk'] as $pk)
					$objectInstance->setPrimaryKey($pk);
				foreach($objects['fk'] as $fk)
				{
					$fk['localObject'] = $objectName;
					$foreignKeys[] = $fk;
				}
			}
			foreach($foreignKeys as $fk)
			{
				$foreignObject = $this->objects[$fk['foreignObject']];
				$localObject = $this->objects[$fk['localObject']];
				$localObject->fields[$fk['localField']]->setForeignKey($foreignObject, $foreignObject->fields[$fk['foreignField']], $fk['onDelete'], $fk['onUpdate']);
			}
		}
	private
		function parseCreateTable($ctString)
		{
			preg_match_all('|\((.*)\)|s', $ctString, $matches);
			$declarations = split(",\n", $matches[1][0]);
			$fields = array();
			$pk = array();
			$fk = array();
			$i = 0;
			foreach($declarations as $declaration)
			{
				$declaration = trim($declaration);
				preg_match('|^[^ ]*|', $declaration, $matches);
				$firstWord = $matches[0];
				switch($firstWord)
				{
				case 'UNIQUE': case 'KEY': case 'INDEX': case 'FULLTEXT': case 'SPATIAL': case 'CHECK':
					break;
				case 'FOREIGN':
				case 'CONSTRAINT':
					$on = 'CASCADE|SET NULL|DELETE';
					preg_match_all("/(CONSTRAINT `.*`)?\s*FOREIGN KEY\s*\(`(?<local>.*)`\)\s*REFERENCES\s*`(?<foreign_table>.*)`\s*\(`(?<foreign>.*)`\)\s*(ON DELETE (?<ondelete>$on))?\s*(ON UPDATE (?<onupdate>$on))?/", $declaration, $matches);
					if (isset($matches['local']) && isset($matches['foreign']) && isset($matches['foreign_table']))
					{
						$local = $matches['local'][0];
						$locals = split('`, `', $local);
						$foreign = $matches['foreign'][0];
						$foreigns = split('`, `', $foreign);
						assert(count($locals) == count($foreigns));
						foreach($locals as $i => $local_field_name)
						{
							$remote_field_name = $foreigns[$i];
							$fk[] = array(
								'localField' => $local_field_name,
								'foreignObject' => $matches['foreign_table'][0],
								'foreignField' => $remote_field_name,
								'onDelete' => strtolower(&$matches['ondelete'][0]),
								'onUpdate' => strtolower(&$matches['onupdate'][0])
							);
						}
					}
					break;
				case 'PRIMARY':
					preg_match_all('|`(.*)`|U', $declaration, $matches);
					$pk = $matches[1];
					break;
				default:
					$i++;
					$fields[$i] = $declaration;
					break;
				}
			}
			return compact('fields', 'pk', 'fk');
		}
	private
		function parseColumnDefinition(dbo $dbo, $coldefString, $num)
		{
			preg_match('/`?([a-zA-Z_0-9]*)`?\s*([a-zA-Z]*)\s*(\((.*)\))?\s*(CHARACTER\s+SET\s+[a-zA-Z0-9_]+)?\s*(COLLATE\s+[a-zA-Z0-9_]+)?\s*(UNSIGNED)?\s*(NOT NULL|NULL)?\s*(DEFAULT \'?([^\']*)\'?)?\s*(AUTO_INCREMENT)?.*/i', $coldefString, $matches);
			$name = trim(&$matches[1]);
			$type = trim(&$matches[2]);
			$info = trim(&$matches[4]);
			$notnull = trim(&$matches[8]);
			$default = trim(&$matches[10]);
			$autoIncrement = trim(&$matches[11]);
			$class_name = $name . 'Field';
			$general_class_name = 'my' . ucfirst($type) . 'Field';
			if (classdef_exists($class_name))
				$return = new $class_name($dbo);
			else
			if (classdef_exists($general_class_name))
				$return = new $general_class_name($dbo);
			else
				throw new errors(gf('Cannot find class definition for the field %s (Looked after %s and then %s)', $name, $class_name, $general_class_name));
			if (!($return instanceof databasefield))
				throw new errors(gf('The class definition for the field %s (%s) was found, but it is not an instance of databasefield!', $name, get_class($return)));
			$return->setName($name);
			$return->setNum($num);
			$return->setType($type);
			$return->setInfo($info);
			$return->setMayBeNull($notnull !== 'NOT NULL');
			if ($autoIncrement)
				$return->setDefault(true);
			else
				if ($default || $default === '0')
					$return->setDefault($default);
			return $return;
		}
	protected
		function tryCreateTable($objectName)
		{
			if (is_null($objectName))
				throw new errors('The objectName must not be null!');
			if (!is_string($objectName))
				throw new errors('The objectName must be a string');
			$classdef_exists = classdef_exists($objectName);
			if (!$classdef_exists)
				return false;
			assert($classdef_exists == true);
			$objectInstance = new $objectName($this);
			if (!($objectInstance instanceof controller))
			{
				new p(g('The controller was found but it\'s not an instance of controller ' . $objectName));
				return false;
			}
			assert($objectInstance instanceof controller);
			$thisClass = new ReflectionClass($objectInstance);
			$comment = $thisClass->getDocComment();
			$result = preg_match('/@mysql_create(.*)@end_mysql_create/is', $comment, $matches);
			if ($result === false) // preg_match error!
				return false;
			if ($result === 0) // Cannot find phpdoc CREATE TABLE statement. Database object doesn\'t exist
				return false;
			assert(count($matches) > 0);
			$ctString = trim($matches[1]);
			if (DEBUG_MODE())
				new p('Automatically creating database object `<strong>' . get_class($objectInstance) . '</strong>` based on its controller phpdoc @mysql_create instructions!');
			$queries = split(';', $ctString);
			foreach($queries as $query)
			{
				$query = trim($query);
				if ($query !== '')
					$this->query($query);
			}
			$objects = $this->parseCreateTable($queries[0]);
			foreach($objects['fields'] as $num => $declaration)
				$objectInstance->addField($this->parseColumnDefinition($objectInstance, $declaration, $num));
			foreach($objects['pk'] as $pk)
				$objectInstance->setPrimaryKey($pk);
			$this->objects[$objectName] = $objectInstance;
			return $objectInstance;
		}
	public 
		function disconnect() 
		{
			if ($this->isConnected())
			{
				$success = false;
				$resource = $this->getRes();
				if (is_resource($resource))
					$success = mysql_close($this->getRes());
				if ($success)
				{
					$this->isConnected = false;
					return true;
				}
			}
			return false;
		}
	static 
		function filter($value)
		{
			if (is_string($value))
			{
				$value = trim($value);
				$value = mysql_real_escape_string($value);
			}
			return $value;
		}
	public	
		function query() 
		{
			$params = func_get_args();
			$qstring = array_shift($params);
			$result = mysql_query($this->queryString($qstring, $params), $this->getRes());
			if ($result === false) 
			throw new errors("MySQL Error: <hr />" . mysql_error($this->getRes()) . '<hr /><pre>'.$qstring.'</pre>', null, mysql_errno($this->getRes()));
			if (DEBUG_MODE() && $this->debug())
				print 'Querying(' . $this->schemata . ' ' . $this->getRes() . '): <pre>'.$qstring.'</pre>';
			return $result;
		}
	public
		function debug()
		{
			return self::debug;
		}
	protected
		function getmysql_numeric($number)
		{
			if (!is_double($number))
				throw new errors('Double expected as getmysql_numeric parameter.');
			$return = (string)$number;
			return $return;
		}
	public
		function queryString($qstring, $args)
		{
			$qarray = split('\?', $qstring, count($args) + 1);
			if (count($args))
				foreach ($args as $key => &$value) 
				{
					if (is_null($value)) // if (!($value) && ($value !== '0'))
						$value = 'NULL';
					else
					if (is_int($value))
						$value = $value;
					else
					if ($value == DB_EMPTYSTRING)
						$value = '\'\'';
					else 
						$value = "'". self::filter($value)."'";
					$qarray[$key] .= $value;
				}
			$qstring = join($qarray, '');
			return $qstring;
		}
	public 
		function queryAssoc()
		{
			$assoc = array();
			$args = func_get_args();
			$dbResource = call_user_func_array(array($this, 'query'),	$args);
			if (is_resource($dbResource) && mysql_num_rows($dbResource))
			{
				while($record = mysql_fetch_assoc($dbResource))
					$assoc[] = $record;
				mysql_free_result($dbResource);
			}
			else
			{
				if (count($this->_performingInsert))
				{
					$inserted = $this->_performingInsert['inserted'];
					$dbo = $this->_performingInsert['dbo'];
					$this->_performingInsert = null;
					$primary = $dbo->idFieldNames();
					foreach($primary as $fieldName)
					{
						$field = $dbo->fields[$fieldName];
						if ($field->getPrimary() && $field->getHasDefault())
						{
							$inserted[$fieldName] = mysql_insert_id();
						}
					}
					$assoc[] = $inserted;
				}
			}
			return $assoc;
		}
	public 
		function selectString($dboName, array $assoc)
		{
			$dbo = $this->objects[$dboName];
			if (!isset($dbo))
				throw new errors("The object `$dboName` doesn't exist!");
			$operator = "AND";
			$where_array = array();
			$args = array();
			foreach ($dbo->fields as $field) 
			{
				$name = $field->name;
				$value = &$assoc[$name];
				if (isset($value))
				{
					array_push($where_array, $name . "=? ");
					array_push($args, $value);
				}
			}
			$where = join($where_array, $operator . ' ');
			if ($where) $where = "WHERE " . $where;
			$queryString = "SELECT * FROM `$dboName` $where;";
			return $this->queryString($queryString, $args);
		}
	public 
		function updateString($dboName, array $assoc, array $conditions)
		{
			$dbo = $this->objects[$dboName];
			if (!isset($dbo))
				throw new errors("The object `$dboName` doesn't exist!");
			$keys = array();
			$keyargs = array();
			foreach($conditions as $name => $value) 
				if (isset($dbo->fields[$name])) 
				{
					$keyargs[] = $value;
					$keys[] = $name . "=?";
				}
			$names = array();
			$nameargs = array();
			foreach($assoc as $name => $value) 
				if (isset($dbo->fields[$name])) 
				{
					$nameargs[] = $value;
					$names[] = $name . "=?";
				}
			$namesString = join($names, ', ');
			$keysString = join($keys, ' AND ');
			$q = "UPDATE `$dbo->dboName` SET $namesString WHERE $keysString;";
			$args = array();
			foreach($nameargs as $namearg) $args[] = $namearg;
			foreach($keyargs as $keyarg) $args[] = $keyarg;
			$q = $this->queryString($q, $args);
			return $q; 
		}
	public 
		function insertString($dboName, array $assoc)
		{
			$dbo = $this->objects[$dboName];
			$valStr = '';
			$args = array();
			$vals = array();
			foreach($assoc as $name => $value) 
				if (isset($dbo->fields[$name]))
				{
					$field = $dbo->fields[$name];
					if (isset($field))
						if (!($field->getPrimary() && $field->getHasDefault()))
							if ($value !== null) // Daca valoarea este goala (null) - cum este in cazul timestamp
							{
								$args[] = $value;
								$vals[] = $name . '=?';
							}
				}
			$valStr = join($vals, ', ');
			$q = "INSERT INTO `$dbo->dboName` SET $valStr;";
			$this->_performingInsert = array(
				'dbo' => $dbo,
				'inserted' => $assoc
			);
			return $this->queryString($q, $args);
		}
	public 
		function deleteString($dboName, array $assoc)
		{
			$dbo = $this->objects[$dboName];
			$keys = array();
			$keyargs = array();
			foreach($assoc as $name => $value) 
			{
				$field = &$dbo->fields[$name];
				if (isset($field) && $value)
				{ 
					$keyargs[] = $value;
					$keys[] = $name . "=?";
				}
			}
			$keysString = join($keys, ' AND ');
			$q = "DELETE FROM `$dbo->dboName` WHERE $keysString;";
			return $this->queryString($q, $keyargs);
		}
	public 
		function startTransaction()
		{
			$this->query('SET AUTOCOMMIT = 0');
			$this->query('START TRANSACTION');
		}
	public 
		function commit()
		{
			$this->query('COMMIT');
			$this->query('SET AUTOCOMMIT = 1');
		}
	public 
		function rollback()
		{
			$this->query('SET AUTOCOMMIT = 0');
			$this->query('START TRANSACTION');
		}
}
?>
