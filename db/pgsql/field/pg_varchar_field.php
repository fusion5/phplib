<?
class pg_varchar_field extends pg_databasefield
{
	public
		function renderDatabaseField($attributes)
		{
			$attributes = $this->controlAttributes($attributes);
			$type = $attributes['type'] = first(&$attributes['type'], 'text');
			$attributes['size'] = $this->getLength();
			if ($type == 'textarea')
				new textarea($attributes);
			else
				new input($attributes);
		}
	public
		function setValue($parameter)
		{
			if ($parameter !== null)
			{
				$parameter = trim($parameter);
				$length = $this->getLength();
				if ($length !== null)
				{
					if (strlen($parameter) > $this->getLength())
					{
						throw new errors(gf('The value of the field %s must be shorter in length than %s characters', $this->getLabel(), $this->getLength()), $this->name);
					}
				}
			}
			parent::setValue($parameter);
		}
	public
		function getLength()
		{
			if (isset($this->info) && ($this->info != -1))
				return $this->info - 4;
		}
	public
		function getXMLSchemaPart()
		{
			$typeName = $this->type;
			if ($this->getLength())
				$typeName .= '_' . $this->getLength();
			$r ='<xsd:simpleType name="'.$typeName.'Type">'.
					'<xsd:restriction base="xsd:string">';
			if ($this->getLength())
				$r .= '<xsd:maxLength value="'.$this->getLength().'" />';
			$r.= '</xsd:restriction>'.
					'</xsd:simpleType>';
			return array 
			(
				$typeName.'Type' => $r,
				$typeName => '<xsd:element name="'.$typeName.'" type="tns:'.$typeName.'Type" />'
			);
		}
}
?>
