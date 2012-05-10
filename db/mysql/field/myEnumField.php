<?
class myEnumField extends myDatabaseField
{
	public
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
		function setValue($value)
		{
			if (($this->getCurrentMode() == 'insert') || ($this->getCurrentMode() == 'update'))
				if (!isset($value) && $this->getHasDefault() && !$this->getMayBeNull())
					$value = $this->getDefault();
			if (!(isset($this->values[$value]) || (!$value && $this->getMayBeNull())))
				throw new errors(g('Valoare invalida pentru campul eunum '));
			parent::setValue($value);
		}
	public
		function renderDatabaseField($attributes)
		{
			$attributes = $this->controlAttributes($attributes);
			if (!isset($attributes['exclusiveoptions']))
			{
				if (is_array(&$attributes['options']))
					$attributes['options'] += $this->values;
				else
					$attributes['options'] = $this->values;
			}
			$attributes['notnull'] = !$this->mayBeNull || !empty($attributes['notnull']);
			new comEnum($attributes);
		}
	public
		function getXMLSchemaPart()
		{
			return '';
		}
}
?>
