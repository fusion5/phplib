<?
class myIntField extends myDatabaseField
{
	public
		function renderDatabaseField($attributes)
		{
			$attributes = $this->controlAttributes($attributes);
			$attributes['type'] = 'text';
			$attributes['size'] = $this->getLength();
			new input($attributes);
		}
	public
		function setValue($value)
		{
			if ($this->getCurrentMode() == 'insert' && $this->primary && $this->hasDefault) 
			{
				$this->value = null;
				return;
			}
			if ($this->mayBeNull && (is_null($value) || ($value === '')))
			{
				$this->value = null;
				return;
			}
			if (!empty($value) && !is_numeric($value))
			{
				throw new errors(gf('The field %s must be numeric', $this->name), $this->name);
			}
			if ($this->unsigned)
				if ($value < 0)
				{
					throw new errors(gf('Field %s must be >= 0', $this->name), $this->name);
				}
			parent::setValue($value);
		}
	public
		function getXMLSchemaPart()
		{
			return '';
		}
}
?>
