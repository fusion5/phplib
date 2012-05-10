<?
class pg_int4_field extends pg_databasefield
{
	public
		function renderDatabaseField($attributes)
		{
			$attributes = $this->controlAttributes($attributes);
			$attributes['type'] = 'text';
			$attributes['size'] = 11;
			new input($attributes);
		}
	public
		function setValue($value)
		{
			if ($this->mayBeNull && (is_null($value) || ($value === '')))
			{
				$this->value = null;
				return;
			}
			if ($value !== null)
			{
				if (!is_numeric($value))
					throw new errors(gf('The field %s must be numeric! The current value is: %s', $this->getLabel(), $value), $this->name);
				$value = (int)$value;
			}
			parent::setValue($value);
		}
	public
		function getXMLSchemaPart()
		{
			return array(
				$this->type . 'Type' => 
				'<xsd:simpleType name="'.$this->type.'Type">'.
				'<xsd:restriction base="xsd:int">'.
				'<xsd:maxInclusive value="2147483647" />'.
				'<xsd:minInclusive value="-2147483648" />'.
				'</xsd:restriction>'.
				'</xsd:simpleType>',
				$this->type => '<xsd:element name="'.$this->type.'" type="tns:'.$this->type.'Type" />'
			);
		}
}
?>
