<?
class pg_text_field extends pg_databasefield
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
			return array(
				$this->type . 'Type'=>
				'<xsd:simpleType name="'.$this->type.'Type">'.
				'<xsd:restriction base="xsd:string">'.
				'</xsd:restriction>'.
				'</xsd:simpleType>',
				$this->type => '<xsd:element name="'.$this->type.'" type="tns:'.$this->type.'Type" />'
			);
		}
}
?>
