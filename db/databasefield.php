<?
abstract class databasefield extends field
{
	protected
		$info;
	protected
		$primary = false;
	protected
		$external = false;
	protected
		$refTable = false;
	protected
		$refColumn = false;
	protected
		$onDelete = 'restrict';
	protected
		$onUpdate = 'restrict';
	protected
		$fieldRow;
	protected	
		$dbo;
	protected
		$db;
	protected
		$num = 0;
	public 
		function __construct(dbo $dbo, array $fieldRow = null) 
		{
			$this->dbo = $dbo;
			$this->db = $this->dbo->db;
			if ($fieldRow)
				$this->fieldRow = $fieldRow;
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
		function setType($t)
		{
			$this->type = $t;
		}
	public
		function getType() 
		{
			return $this->type;
		}
	public
		function setInfo($i)
		{
			$this->info = $i;
		}
	public
		function getInfo()
		{
			return $this->info;
		}
	public
		function setNum($n)
		{
			$this->num = $n;
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
		function getLabel()
		{
			if (is_callable(array($this->dbo, 'getFieldAttributes')))
				$attributes = $this->dbo->getFieldAttributes($this->name);
			return first(&$attributes['label'], &$this->name, 'Unkown field');
		}
	public 
		function setValue($value) 
		{
			if (is_string($value))
				$value = trim($value);
			$label = $this->getLabel();
				if (!$this->mayBeNull && !$this->getHasDefault())
					if ((!$value) && ($value !== '0') && ($value !== 0)) 
					{
						throw new errors(gf('The field %s must have a value', $label), $this->name);
					}
			if (!$value && ($value !== '0') && ($value !== 0))
				$value = null;
			parent::setValue($value);
		}
	public
		function render($attributes, fieldset $fieldset = null)
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
			if ($this->getHasDefault() && !isset($attributes['value']))
				$attributes['value'] = $this->getDefault();
			new select($attributes);
		}
	public
		function setForeignKey(dbo $foreignObject, databasefield $foreignField, $onUpdate, $onDelete)
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
		function setMayBeNull($n)
		{
			$this->mayBeNull = $n;
		}
	public
		function getMayBeNull() 
		{
			return $this->mayBeNull;
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
