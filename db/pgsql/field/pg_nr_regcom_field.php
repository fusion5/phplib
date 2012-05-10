<?
class pg_nr_regcom_field extends pg_databasefield
{
	public
		function renderDatabaseField($attributes)
		{
			$attributes = $this->controlAttributes($attributes);
			$attributes['type'] = first(&$attributes['type'], 'text');
			$attributes['size'] = $this->getLength();
			new input($attributes);
		}
}
?>
