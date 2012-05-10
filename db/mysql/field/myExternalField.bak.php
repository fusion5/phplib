<?
class myExternalField extends myDatabaseField
{
	public
		function render($attributes)
		{
			$attributes = $this->controlAttributes($attributes);
			$db = $this->dbo->db();
			if (!isset($this->refTable))
				new p(g('Nu exista tabelul referit!'));
			$externalObject = $db->objects[$this->refTable];
			$externalField = $this->refColumn;
			if (!$externalObject->selected()) $externalObject->select('*');
			$options = first(&$attributes['options'], array());
			if ($this->mayBeNull && !(isset($attributes['notnull'])))
				$options[''] = first(&$attributes['emptylabel'], g('Empty value'));
			foreach($externalObject as $externalRow)
				$options[$externalObject->$externalField] = $externalObject->__toString();
			$attributes['options'] = $options;
			new select($attributes);
		}
}
?>
