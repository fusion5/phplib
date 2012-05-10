<?
class myVarcharField extends myDatabaseField
{
	public
		function setInfo($i)
		{
			parent::setInfo($i);
			if ($i)
			{
				if (is_array($i))
				{
					$length = $i['CHARACTER_MAXIMUM_LENGTH'];
					$this->setLength($length);
				}
				if (is_numeric($i))
					$this->setLength($i);
			}
		}
	public
		function renderDatabaseField($attributes)
		{
			$attributes = $this->controlAttributes($attributes);
			$attributes['type'] = first(&$attributes['type'], 'text');
			$attributes['size'] = $this->getLength();
			new input($attributes);
		}
	public
		function getXMLSchemaPart()
		{
			return '';
		}
}
?>
