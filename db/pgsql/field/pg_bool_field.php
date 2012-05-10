<?
class pg_bool_field extends pg_databasefield
{
	public
		function renderDatabaseField($attributes)
		{
			$attributes = $this->controlAttributes($attributes);
			$attributes['type'] = 'checkbox';
			$attributes['checked'] = $attributes['value'] == '1';
			new input(array(
				'name' => $attributes['name'],
				'type' => 'hidden',
				'value' => '0'
			));
			$attributes['value'] = 'true';
			new input($attributes);
		}
	public
		function setValue($value)
		{
			if ($value == 't') $value = '1';
			if ($value == 'f') $value = '0';
			if (is_bool($value))
				if ($value)
					$value = '1';
				else
					$value = '0';
			parent::setValue($value);
		}
	public
		function getXMLSchemaPart()
		{
			return 
				array(
					$this->type . 'Type' =>
					'<xsd:simpleType name="'.$this->type.'Type">'.
					'<xsd:restriction base="xsd:boolean"></xsd:restriction>'.
					'</xsd:simpleType>',
					$this->type => '<xsd:element name="'.$this->type.'" type="tns:'.$this->type.'Type" />'
				);
		}
}
?>
