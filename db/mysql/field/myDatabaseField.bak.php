<?
abstract class myDatabaseField extends field
{
	public
		$values = array();
	public
		$autoIncrement = false;
	public
		$primary = false;
	public
		$unique = false;
	public
		$external = false;
	public
		$refTable = false;
	public
		$refColumn = false;
	public
		$onDelete = 'restrict';
	public
		$onUpdate = 'restrict';
	protected
		$fieldRow;
	protected	
		$dbo;
	protected
		$db;
	public static
		function getFieldInstance(dbo $dbo, array $fieldRow)
		{
			$type = myDatabaseField::getRowType($fieldRow);
			$name = myDatabaseField::getRowName($fieldRow);
			$exte = myDatabaseField::getRowExternal($fieldRow);
			$class_name = $name . 'Field';
			if (!$exte)
				$general_class_name = 'my' . ucfirst($type) . 'Field';
			else
				$general_class_name = 'myExternalField';
			if (classdef_exists($class_name, array('fields', $db_package . '/fields')))
				$return = new $class_name($dbo);
			else
			if (classdef_exists($general_class_name, $db_package . '/field'))
				$return = new $general_class_name($dbo);
			else
				throw new errors(gf('Cannot find class definition for the field %s (Looked after %s and then %s)', $name, $class_name, $general_class_name));
			if (!($return instanceof databasefield))
				throw new errors(gf('The class definition for the field %s (%s) was found, but it is not an instance of databasefield!', $name, get_class($return)));
			$return->readFieldRow($fieldRow);
			return $return;
		}
	public 
		function __construct(dbo $dbo, array $fieldRow = null) 
		{
			$this->dbo = $dbo;
			$this->db = $this->dbo->db;
			if ($fieldRow)
				$this->readFieldRow($fieldRow);
		}
	public
		function readFieldRow($fieldRow)
		{
			$this->fieldRow = $fieldRow;
			$this->setName($this->readName());
			$this->unsigned = $this->getUnsigned();
			$this->mayBeNull = $this->getMayBeNull();
			$this->hasDefault = $this->getHasDefault();
			$this->setLength($this->readLength());
			$this->type = $this->getType();
			$this->primary = $this->getPrimary();
			$this->values = $this->getValues();
			if (is_array($this->values))
				$this->setLength(count($this->values));
			$this->autoIncrement = $this->getAutoIncrement();
			$this->setExternal($this->readExternal());
			$this->setRefColumn($this->readRefColumn());
			$this->setRefTable($this->readRefTable());
			$this->setOnDelete($this->readOnDelete());
			$this->setOnUpdate($this->readOnUpdate());
		}
	protected
		function controlAttributes($attributes)
		{
			$attributes = parent::controlAttributes($attributes);
			append(&$attributes['class'], ' '.join($this->getCSSClasses(), ' '));
			return $attributes;
		}
	protected
		function getCSSClasses()
		{
			$class = parent::getCSSClasses();
			if ($this->type)
				$class[] = $this->type;
			return $class;
		}
	private
		function getType() 
		{
			return databasefield::getRowType($this->fieldRow);
		}
	private static
		function getRowType(array $fieldRow)
		{
			$matchtype = array();
			preg_match('/^[a-zA-Z]*/', $fieldRow['Type'], $matchtype);
			return $matchtype[0];
		}
	private
		function readLength() 
		{
			$matchlength = array();
			if (preg_match('/\([0-9]*\)/', $this->fieldRow['Type'], $matchlength)) 
			{
				$length = ereg_replace('^\(|\)$', '', $matchlength[0]);
				return $length;
			}
		}
	protected
		function getCurrentMode()
		{
			return $this->dbo->getCurrentMode();
		}
	private
		function getValues() 
		{
			$matchvalues = preg_split("/^.*\(\'|\',\'|\'\)/", $this->fieldRow['Type'], -1, PREG_SPLIT_NO_EMPTY);
			if ($matchvalues[0] != $this->fieldRow["Type"]) // daca split-ul a fost provocat 
			{
				if (is_array($matchvalues))
				{
					$t = array();
					foreach($matchvalues as $value) $t[$value] = $value; 
					return $t;
				}
			}
		}
	public 
		function setValue($value) 
		{
			if (!isset($value) && isset($this->hasDefault))
				$value = $this->hasDefault;
			if (is_callable(array($this->dbo, 'getFieldAttributes')))
			{
				$attributes = $this->dbo->getFieldAttributes($this->name);
			}
			$label = first(&$attributes['label'], &$this->name, 'Unkown field');
			if ($this->getCurrentMode() == 'update') 
				if ($this->primary && $this->autoIncrement && (!isset($value)))
					new error(gf('The field <em>%s</em> is a primary key', $label), $this->name);
			if (($this->getCurrentMode() == 'insert') || ($this->getCurrentMode() == 'update'))
				if (!$this->mayBeNull && !$this->autoIncrement)
					if ((!$value) && ($value !== "0") && ($value !== 0)) 
					{
						new error(gf('The field <em>%s</em> must have a value', $label), $this->name);
					}
			parent::setValue(&$value);
		}
	public
		function setExternal ($e)
		{
			$this->external = $e;
		}
	public
		function getExternal()
		{
			return $this->external;
		}
	public
		function setRefColumn($e)
		{
			$this->refColumn = $e;
		}
	public
		function getRefColumn()
		{
			return $this->refColumn;
		}
	public
		function setRefTable($e)
		{
			$this->refTable = $e;
		}
	public
		function getRefTable()
		{
			return $this->refTable;
		}
	public
		function setOnDelete($e)
		{
			$this->onDelete = $e;
		}
	public
		function getOnDelete()
		{
			return $this->onDelete;
		}
	public
		function setOnUpdate($e)
		{
			$this->onUpdate = $e;
		}
	public
		function getOnUpdate()
		{
			return $this->onUpdate;
		}
	private
		function getUnsigned() 
		{
			return strpos($this->fieldRow['Type'], 'unsigned') !== false;
		}
	private
		function getMayBeNull() 
		{
			return strtolower($this->fieldRow['Null']) == 'yes';
		}
	private
		function getHasDefault() 
		{
			return $this->fieldRow['Default'];
		}
	private
		function getAutoIncrement() 
		{
			return strpos($this->fieldRow['Extra'], 'auto_increment') !== false; 
		}
	public static
		function getRowName($fieldRow)
		{
			return $fieldRow['Field'];
		}
	public static
		function getRowExternal($fieldRow)
		{
			return isset($fieldRow['external']);
		}
	private
		function readName() 
		{
			return $this->getRowName($this->fieldRow);
		}
	private
		function getPrimary() 
		{
			return strtolower($this->fieldRow['Key']) == 'pri';
		}
	private
		function readExternal()
		{
			return $this->getRowExternal($this->fieldRow);
		}
	private
		function readRefColumn()
		{
			if (isset($this->fieldRow['refColumn']))
				return $this->fieldRow['refColumn'];
		}
	private
		function readRefTable()
		{
			if (isset($this->fieldRow['refTable']))
				return $this->fieldRow['refTable'];
		}
	private
		function readOnDelete()
		{
			if (isset($this->fieldRow['onDelete']))
				return $this->fieldRow['onDelete'];
		}
	private
		function readOnUpdate()
		{
			if (isset($this->fieldRow['onUpdate']))
				return $this->fieldRow['onUpdate'];
		}
}	
?>
