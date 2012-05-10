<?
class myTimestampField extends myDatetimeField
{
	public
		function setValue($value)
		{
			if (($this->hasDefault == 'CURRENT_TIMESTAMP') && (($this->getCurrentMode() == 'insert') || ($this->getCurrentMode() == 'update')))
			{
				$this->setRawValue(null);
			}
			else
			{
				parent::setValue($value);
			}
		}
	public
		function getXMLSchemaPart()
		{
			return '';
		}
}
?>
