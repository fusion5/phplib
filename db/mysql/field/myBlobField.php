<?
class myBlobField extends myDatabaseField
{
	public
		function renderDatabaseField($attributes)
		{
			$attributes = $this->controlAttributes($attributes);
			$attributes['type'] = 'text';
			$attributes['size'] = $this->getLength();
			new textarea($attributes);
		}
	public
		function getXMLSchemaPart()
		{
			return '';
		}
}
?>
