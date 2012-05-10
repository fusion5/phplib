<?
class myDateField extends myDatabaseField
{
	const format = 'd-m-Y';
	const databaseFormat = 'Y-m-d';
	public
		function renderDatabaseField($attributes)
		{
			$attributes = $this->controlAttributes($attributes);
			$attributes['format'] = self::format;
			new comDatetime($attributes);
		}
	public
		function setValue($value)
		{
			if (isset($value))
			{
				if (is_string($value))
					$value = strtotime($value);
				else
				if (is_numeric($value))
					$value = $value;
				else
				if (is_array($value))
					$value = strtotime($value['Y'].'-'.$value['m'].'-'.$value['d']);
			}
			if (is_null($value))
				$value = time();
			parent::setValue(date(self::databaseFormat, $value));
		}
	public
		function getXMLSchemaPart()
		{
			return '';
		}
}
?>
