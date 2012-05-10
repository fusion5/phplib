<?
class dbo implements Iterator, idataaccess, icontroller
{
 	public
 		$fields = array();
	private
		$fieldNames = array();
	private
		$fieldsByNum = array();
	private	
		$primaryKeyNames = array();
	public
		$db;
 	public
 		$dboName;
	protected
		$result;
	protected
		$cursorIndex;
	protected
		$cursor;
	private
		$fieldAttributes = array();
	private
		$dbStatus;
	public
		$_description;
	public 
		$_singular;
	public
		$_plural;
	public
		$_gen_singular;
	public
		$_gen_plural;
	public
		$cursor_override;
	private
		$lastQuery;
	private
		$currentMode = '';
	public 
		function __construct($dboName, db $db) 
		{
			$this->db = $db;
			$this->dboName = $dboName;
		}
	public
		function __wakeup()
		{
		}
	public 
		function insert(array $parameter = null) 
		{
			$this->setCurrentMode('insert');
			try
			{
				if (is_array($parameter))
				{
					if ($this instanceof controller)
						$parameter = $this->onBeforeInsert($parameter);
					if ($parameter === null)
						throw new errors(g('Internal error: onBeforeInsert must return either the $parameter array or false (to block the execution of the insert)'));
					if ($parameter === false)
						return false;
					$parameter = $this->getRecord($parameter, 0, false, true);
				}
				else
					if (is_null($parameter))
						$parameter = $this->cursor();
			}
			catch (errors $e)
			{
				$this->setCurrentMode();
				throw $e;
			}
			$this->setCurrentMode();
			if ($parameter instanceof row)
			{
				$insert_string = $this->db->insertString($this->dboName, $parameter->getAssoc());
				$this->query($insert_string);
				return $this->cursor()->getAssoc();
			}
		}
	public 
		function delete(array $parameter = null)
		{
			$this->setCurrentMode('delete');
			$delete = array();
			if (is_array($parameter))
				$delete = $this->getRecord($parameter)->getAssoc();
			elseif ($parameter instanceof row)
				$delete = $parameter->getAssoc();
			elseif ($parameter == null)
				$delete = $this->cursor()->getAssoc();
			$this->query($this->db->deleteString($this->dboName(), $delete));
			$this->setCurrentMode();
		}
	public
		function selectUpdate(array $new = null)
		{
			if (is_array($new))
			{
				if (isset($new['old_pk']))
				{
					$id = $new['old_pk'];
					unset($new['old_pk']);
				}
				else
				{
					$id = $this->getRecord($new)->id();
				}
				$this->select('*', $id);
				if ($this instanceof controller)
					$new = $this->onBeforeUpdate($new);
				assert(is_array($new));
				$param = $this->getRecord($new);
				if ($this->selected() == 1)
				{
					$update = $param->getAssoc() + $this->getAssoc();
					$this->db->query($this->db->updateString($this->dboName, $update, $id));
					if ($this instanceof controller)
						$this->onAfterUpdate($new);
				}
				else
					throw new errors(g('Internal error - error selecting the fields.'));
			}
		}
	public function update($parameter = null)
	{
		if (is_array($parameter))
		{
			$this->setCurrentMode('update');
			try
			{
				if ($this instanceof controller)
					$parameter = $this->onBeforeUpdate($parameter);
				$update = $this->getRecord(&$parameter); 
				if (isset($parameter) && $parameter !== false)
				{
					$conditions = $update->id();
					if (!count($conditions))
						throw new errors(g('Uknown conditions for update'));
					$this->db->query($this->db->updateString($this->dboName, $update->getAssoc(), $conditions));
					if ($this instanceof controller)
						$this->onAfterUpdate($parameter);
				}
			}
			catch (errors $e)
			{
				$this->setCurrentMode();
				throw $e;
			}
			$this->setCurrentMode();
			return;
		}
		if ($parameter instanceof idataaccess)
		{
			$this->update($parameter->getAssoc());
		}
		if ($parameter == null)
		{
			$c = $this->cursor();
			if ($c instanceof row)
				$this->update($c->getAssoc());
		}
	}
	public function select($wilcard, $parameter = null)
	{
		$this->reset();
		if (!is_string($wilcard))
			throw new errors('select() method: the first parameter must be a string');
		if (is_array($parameter))
		{
			$parameter = $this->normalize($parameter);
			if (count($parameter))
			{
				$this->query($this->db->selectString($this->dboName, $parameter));
				return;
			}
			else
				$parameter = null;
		}
		if ($parameter instanceof row)
		{
			$this->query($this->db->selectString($this->dboName, $parameter->getAssoc()));
			return;
		}
		if (is_string($parameter))
		{
			if ($this->db instanceof pgsql)
				$queryString = "SELECT $wilcard FROM \"$this->dboName\" $parameter;";
			else
				$queryString = "SELECT $wilcard FROM `$this->dboName` $parameter;";
			$args = func_get_args();
			array_shift($args);	array_shift($args);
			array_unshift($args, $queryString);
			call_user_func_array (array($this, 'query'), $args);
			return;
		}
		if (is_null($parameter))
			$this->select($wilcard, '');
	}
	public 
		function join($wilcard, $tables, $parameter = null, $operator = ' AND', $extra = null) 
		{
			$this->reset();
			$this->readFields();
			new join($this, $wilcard, $tables, $parameter, $operator, $extra);
		}
	public 
		function leftjoin($wilcard, array $tables, array $parameter = null, $operator = ' AND', $extra = null) 
		{
			$this->reset();
			$this->readFields();
			new leftjoin($this, $wilcard, $tables, $parameter, $operator, $extra);
		}
	public 
		function query() 
		{
			$this->reset();
			$query = func_get_args();
			if ($this instanceof controller)
				$query = $this->onBeforeQuery($query);
			if ($query === null)
				print 'the onBeforeQuery didnt return anything. Maybe you forgot to write its return statement?';
			$this->lastQuery = $query;
			$result = call_user_func_array(array($this->db, 'queryAssoc'), $this->lastQuery);
			$this->result = $result;
		}
	public
		function item($index)
		{
			if (!is_int($index))
				throw new errors('The index must be an integer!');
			if ($index < 0)
				throw new errors('The index must be a positive number!');
			if (isset($this->result[$index]))
				return $this->getRecord($this->result[$index], $index);
			else
				return false;
		}
	protected
		function normalize($assoc)
		{
			$row = new row(null, $this, 0);
			foreach ($assoc as $key => $value)
			{
				try
				{
					$row->$key = $value;
				}
				catch(errors $e)
				{
					if (DEBUG_MODE())
						trace($e->getMessage());
					throw $e;
				}
			}
			return $row->getAssoc();
		}
	public
		function getRecord ($fields = null, $index = 0, $notrig = false, $fullcheck = false)
		{
			if ($fields == null)
				$fields = array();
			$newRow = new row(&$fields, $this, $index);
			if (!$notrig)
				$newRow->setAssoc($fields, $fullcheck);
			return $newRow;
		}
	public
		function newRecord(array $fields = null)
		{
			$this->result[] = $fields;
		}
	public
		function getWilcards($except = null) 
		{
			if (!is_array($except)) $except = array();
			$cols = array();
			if (is_array($this->fields))
				foreach($this->fields as $field) 
					if (!in_array($field->name, $except))
						array_push($cols, '`' . $this->dboName . "`.`" . $field->name . '`');
			return join($cols, ', ');
		}
	public function selected() 
	{
		return count($this->result);
	}
	public function cursor() 
	{
		if (isset($this->cursor_override))
		{
			$r = $this->cursor_override;
			$this->cursor_override = null;
			return $r;
		}
		if (!count($this->result))
			$this->newRecord();
		return $this->current();
	}
	private
		function setCurrentMode($mode = '')
		{
			$this->currentMode = $mode;
		}
	public
		function getCurrentMode()
		{
			return $this->currentMode;
		}
	public
	function reset()
	{
		$this->cursor = null;
		$this->result = array();
	}	
  function __clone()
  {
		$this->reset();
  }
	public 
		function getRecords() 
		{
			$clone = clone $this;		
			if ($this->lastQuery)
				call_user_func_array
				(
					array($clone, 'query'), 
					$this->lastQuery
				);
			return $clone;
		}
	public 
	function getRow()
	{
		$idArray = array(); 
		return $this->cursor;
		$k = 0;
		$params = func_get_args();
		foreach($this->fields as $field) 
			if ($field->primary)
				$idArray[$field->name] = $params[$k++];
		$this->select("*", $idArray);
		if ($this->selected() == 1)
			return $this->cursor();
		else
			throw new errors("Nu am putut sa identific row-ul cerut in tabelul " . $this->dboName);
	}
	public
		function addField(databasefield $field)
		{
			$this->fields[$field->getName()] = $field;
			$this->fieldsByNum[$field->getNum()] = $field;
			$this->fieldNames[] = $field->getName();
		}
	public
		function getFieldByNum($num)
		{
			return $this->fieldsByNum[$num];
		}
	public
		function addForeignKey(array $localFieldNums, dbo $foreignObject, array $foreignFieldNums, $onUpdate, $onDelete)
		{
			if ((count($localFieldNums) != 1) || (count($foreignFieldNums) != 1))
				throw new errors(g('Encountered a foreign key with multiple fields - NOT SUPPORTED YET'));
			assert(count($localFieldNums) == 1);
			assert(count($foreignFieldNums) == 1);
			$localField = $this->getFieldByNum($localFieldNums[0]);
			$localField->setForeignKey($foreignObject, $foreignObject->getFieldByNum($foreignFieldNums[0]), $onUpdate, $onDelete);
		}
	public
		function setPrimaryKeyNums(array $primaryFieldNums)
		{
			foreach ($primaryFieldNums as $num)
				$this->setPrimaryKey($this->getFieldByNum($num)->getName());
		}
	public
		function setPrimaryKey($fieldName)
		{
			$field = $this->fields[$fieldName];
			$field->setPrimary(true);
			$this->primaryKeyNames[] = $fieldName;
		}
	public 
	function __set($name, $value) 
	{
		$c = $this->cursor();
		if (isset($c))
			$c->$name = $value;
	}
	public
	function __get($name)
	{
		$c = $this->cursor();
		if (isset($c))
			return $c->$name;
	}
	public
	function __toString() 
	{
		$cursor = $this->cursor();
		if ($cursor)	
			return var_export($this->id(), true);
		else
			return 'Empty value';
	}	
	public
		function fieldNames()
		{
			return $this->fieldNames;
		}
	public
	function id()
	{
		return $this->cursor()->id();
	}
	public function formFieldName()	
	{
		return $this->cursor()->formFieldName();
	}
	public
		function idFieldNames()
		{
			if (count($this->primaryKeyNames))
				return $this->primaryKeyNames;
			else
				return array();
		}
	public
		function idFieldName()
		{
			$idFieldNames = $this->idFieldNames();
			if (count($idFieldNames) == 1)
				return array_pop($idFieldNames);
			else
				throw new errors("idFieldName is not supported for records which have no or multiple primary keys!");
		}
	public
		function fields()
		{
			return $this->fields;
		}
	public function idCondition()
	{
		if ($this->selected())
			return $this->cursor()->idCondition();
	}
	public
	function getAssoc()
	{
		if ($this->selected())
			return $this->cursor()->getAssoc();
	}
	public function first() 
	{
		if ($this->selected())
			return $this->cursor()->first();
	}
	public function last()
	{
		if ($this->selected())
			return $this->cursor()->last();
	}
	public function odd()
	{
		if ($this->selected())
			return $this->cursor()->odd();
	}
	public function db()
	{
		return $this->db;
	}
	public
	function dboName()
	{
		return $this->dboName;
	}
	public
	function getDbo()
	{
		return $this;
	}
	public 
		function rewind() 
		{
			$reset = reset($this->result);
			if ($reset === false)
			{
				$this->cursor = null;
				return false;
			}
			else
			{
				$this->cursor = $this->getRecord($reset, key($this->result), true);
				return $this->cursor;
			}
		}
	public function current() 
	{
		if ($this->cursor !== null)
			return $this->cursor;
		else 
			if (is_array($this->result))
			{
				$this->cursor = $this->getRecord(current($this->result), key($this->result), true);
				return $this->cursor;
			}
	}
	public function key() 
	{
    return key($this->result);
	}
	public 
	function next() 
	{
		$this->cursor = $this->getRecord(next($this->result), key($this->result), true);
		return $this->cursor;
	}
	public 
	function valid() 
	{
		return current($this->result) !== false;
	}
	public 
		function formfields($mode = null)
		{
			return $this->addAttributes($this->fields);
		}
	protected
		function addAttributes($fields)
		{
			if (!count($fields))
				return false;
			$return = array();
			foreach($fields as $fieldName => $field)
			{
				if (isset($this->fieldAttributes[$fieldName]))
					$return[$fieldName] = $this->fieldAttributes[$fieldName];
				else
					$return[$fieldName] = array('label' => $fieldName, 'name' => $fieldName);
			}
			return $return;
		}
	public 
		function field($name)
		{
			if (isset($this->fields[$name]))
				return $this->fields[$name];
			else
				return null;
		}
	public
		function getFieldAttributes($field)
		{
			if (isset($this->fieldAttributes[$field]))
				return $this->fieldAttributes[$field];
			else
				return null;
		}
	public
		function setAttributes(array $attributes)
		{
			$this->fieldAttributes = $attributes;
		}
	public
		function getAttributes()
		{
			return $this->fieldAttributes;
		}
	public
		function readFields()
		{
		}
	public
		function getXMLSchema($namespace = 'appnamespace')
		{
			$ret = '<?xml version="1.0"?>';
			$ret.= '<xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema">';
			$types = $this->getXMLSchemaPart();
			foreach($types as $type)
				$ret.= $type;
			$ret.= '</xsd:schema>';
			return $ret;
		}
	public
		function getXMLSchemaPart()
		{
			$fields = array();
			$dboElementName = $this->dboName;
			$complexType = '<xsd:complexType name="'.$dboElementName.'Type"><xsd:all>';
			foreach($this->fields as $field)
			{
				$part = $field->getXMLSchemaPart();
				if (is_array($part))
				{
					$xsdTypeName = null;
					foreach ($part as $xsdTypeName => $xsd)
						$fields[$xsdTypeName] = $xsd;
					if ($xsdTypeName != null)
					{
						$complexType.= '<xsd:element name="'.$field->getName().'" type="tns:'.$xsdTypeName.'Type" ';
						if ($field->getMayBeNull() || $field->getHasDefault())
							$complexType.= 'minOccurs="0" maxOccurs="1" ';
						$complexType.= '/>';
					}
				}
				else
					throw new errors('The field ' . $field->getName() . ' of type ' . $field->getType() . ' of class ' .get_class($field). ' didn\'t return an array!');
			}
			$complexType .= '</xsd:all></xsd:complexType>';
			$fields[$dboElementName] = $complexType;
			return $fields;
		}
}
?>
