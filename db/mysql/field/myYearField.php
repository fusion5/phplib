<?
class myYearField extends myDatabaseField
{
	public
		function renderDatabaseField($attributes)
		{
			$attributes = $this->controlAttributes($attributes);
			$attributes['format'] = 'Y';
			new comDatetime($attributes);
		}
	public
		function setValue($value)
		{
			if (isset($value))
			{
				if (is_array($value) && isset($value['Y']))
					$value = $value['Y'];
				if (!is_numeric($value))
					throw new errors(gf('Field %s must be numeric. Current value is: %s', $this->name, $value), $this->name);
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
