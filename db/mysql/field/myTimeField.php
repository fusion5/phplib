<?
class myTimeField extends myDatabaseField
{
	public
		function renderDatabaseField($attributes)
		{
			$attributes = $this->controlAttributes($attributes);
			$attributes['format'] = 'H:i:s';
			new comDatetime($attributes);
		}
	public
		function getXMLSchemaPart()
		{
			return '';
		}
}
?>
