<?
class myDatetimeField extends myDatabaseField
{
	const format = 'Y-m-d H:i:s';
	const databaseFormat = 'Y-m-d H:i:s';
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
					$value = strtotime($value['Y'].'-'.$value['m'].'-'.$value['d'].' '.$value['H'].':'.$value['i'].':'.$value['s']);
			}
			if (is_null($value) && !$this->mayBeNull)
				$value = time();
			if ($value)
				parent::setValue(date(self::databaseFormat, $value));
		}
	public
		function getXMLSchemaPart()
		{
			return '';
		}
}
?>
