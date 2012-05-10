<?
class mySetField extends myDatabaseField
{
	private
		$values = array();
	public
		function setInfo($i)
		{
			parent::setInfo($i);
			if ($i)
			{
				if (is_array($i))
				{
					$i = $i['COLUMN_TYPE'];
				}
				if (is_string($i))
				{
					preg_match_all('|\'(.*)\'|U', $i, $matches);
					if (count($matches[1]))
						foreach($matches[1] as $match)
						{
							$this->values[$match] = $match;
						}
				}
			}
		}
	public
		function renderDatabaseField($attributes)
		{
			$attributes = $this->controlAttributes($attributes);
			if (isset($attributes['options']) && (is_array($attributes['options'])))
				$attributes['options'] += $this->values;
			else
				$attributes['options'] = $this->values;
			new comSet($attributes);
		}
	public
		function setValue($value)
		{
			if (is_array($value))
				$value = join($value, ',');
			if (is_null($value))
				$value = '0';
			parent::setValue($value);
		}
	public
		function getXMLSchemaPart()
		{
			return '';
		}
}
?>
