<?
abstract class pg_databasefield extends field
{
	public
		$info;
	public
		$autoIncrement = false;
	public
		$primary = false;
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
	public
		$num = 0;
	public static
		function getFieldInstance(dbo $dbo, array $fieldRow)
		{
			$type = $fieldRow['typname'];
			$name = $fieldRow['attname'];
			$general_class_name = 'pg_' . $type . '_field';
			$class_name = $name . '_field';
			if (classdef_exists($class_name))
				$return = new $class_name($dbo);
			else
			if (classdef_exists($general_class_name))
				$return = new $general_class_name($dbo);
			else
				throw new errors(gf('Cannot find class definition for the field %s (Looked after %s and then %s)', $name, $class_name, $general_class_name));
			if (!($return instanceof pg_databasefield))
				throw new errors(gf('The class definition for the field %s (%s) was found, but it is not an instance of pg_databasefield!', $name, get_class($return)));
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
			$this->setName($fieldRow['attname']);
			$this->mayBeNull = $fieldRow['attnotnull'] != 't';
			$this->hasDefault = $fieldRow['atthasdef'] != 'f';
			$this->num = $fieldRow['attnum'];
			if ($fieldRow['atttypmod'] > 0)
				$this->setLength($fieldRow['atttypmod']);
			$this->type = $fieldRow['typname'];
		}
	public
		function getFieldRow()
		{
			return $this->fieldRow;
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
	public
		function getType() 
		{
			return $this->type;
		}
	public
		function getNum()
		{
			return $this->num;
		}
	protected
		function getCurrentMode()
		{
			return $this->dbo->getCurrentMode();
		}
	public 
		function setValue($value) 
		{
			if (is_string($value))
				$value = trim($value);
			if (is_callable(array($this->dbo, 'getFieldAttributes')))
			{
				$attributes = $this->dbo->getFieldAttributes($this->name);
			}
			$label = first(&$attributes['label'], &$this->name, 'Unkown field');
			if ($this->getCurrentMode() == 'update') 
				if ($this->primary && $this->hasDefault && (!isset($value)))
					throw new errors(gf('The field <em>%s</em> is a primary key but it doesn\'t have a value', $label), $this->name);
			if (($this->getCurrentMode() == 'insert') || ($this->getCurrentMode() == 'update'))
				if (!$this->mayBeNull && !$this->hasDefault)
					if ((!$value) && ($value !== '0') && ($value !== 0)) 
					{
						throw new errors(gf('The field <em>%s</em> must have a value', $label), $this->name);
					}
			if ($this->getCurrentMode() == 'insert')
				if ($this->hasDefault && $this->primary)
					$value = null;
			if (!$value && ($value !== '0') && ($value !== 0))
				$value = null;
			parent::setValue(&$value);
		}
	public
		function render($attributes)
		{
			if ($this->getExternal())
				$this->renderExternalField($attributes); // Render the field with the renderExternalField method, which is special for external fields
			else
				$this->renderDatabaseField($attributes); // Render the field with the generic method of the field
		}
	abstract public
		function renderDatabaseField($attributes);
	abstract public
		function getXMLSchemaPart();
	public
		function renderExternalField($attributes)
		{
			if (!$this->getExternal())
				return new p(g('Trying to call renderExternalField on a field which is not external!'));
			$attributes = $this->controlAttributes($attributes);
			$db = $this->dbo->db();
			if (!isset($this->refTable))
				new p(g('The referenced table doesn\'t exist!'));
			$externalObject = $db->objects[$this->refTable];
			$externalField = $this->refColumn;
			if (!$externalObject->selected()) $externalObject->select('*');
			$options = first(&$attributes['options'], array());
			if ($this->mayBeNull && !(isset($attributes['notnull'])))
				$options[''] = first(&$attributes['emptylabel'], g('Empty value'));
			foreach($externalObject as $externalRow)
				$options[$externalObject->$externalField] = $externalObject->__toString();
			$attributes['options'] = $options;
			new select($attributes);
		}
	public
		function setForeignKey(dbo $foreignObject, pg_databasefield $foreignField, $onUpdate, $onDelete)
		{
			$this->setRefTable($foreignObject->dboName());
			$this->setRefColumn($foreignField->getName());
			$this->setOnDelete($onDelete);
			$this->setOnUpdate($onUpdate);
			$this->setExternal(true);
		}
	public
		function setExternal($e)
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
	public
		function getMayBeNull() 
		{
			return $this->mayBeNull;
		}
	private
		function getHasDefault() 
		{
			return $this->hasDefault;
		}
	private
		function getAutoIncrement() 
		{
		}
	public
		function setPrimary($primary)
		{
			$this->primary = $primary;
		}
	public
		function getPrimary() 
		{
			return $this->primary;
		}
}	
?>
