<?
class pg_date_field extends pg_databasefield
{
	public
		function renderDatabaseField($attributes)
		{
			$attributes = $this->controlAttributes($attributes);
			$attributes['format'] = 'Y-m-d';
			new comDatetime($attributes);
		}
	public
		function setValue($parameter)
		{
			if ($parameter !== null)
			{
				if (is_array($parameter))
					$parameter = $parameter["Y"].'-'.$parameter["m"].'-'.$parameter["d"];
				else
					$parameter = (string)$parameter;
				if (!preg_match('|^([0-9]{4}[/-][0-9]{1,2}[/-][0-9]{1,2})$|', $parameter))
				{
					$label = first(&$attributes['label'], &$this->name, 'Unkown field');
					throw new errors(gf('Invalid date read from <em>%s</em>: %s', $label, $parameter));
				}
			}
			parent::setValue($parameter);
		}
	public
		function getXMLSchemaPart()
		{
			return array(
				$this->type . 'Type' => 
				'<xsd:simpleType name="'.$this->type.'Type">'.
				'<xsd:restriction base="xsd:date">'.
				'<xsd:pattern value="\p{Nd}{4}-\p{Nd}{2}-\p{Nd}{2}"/>'.
				'</xsd:restriction>'.
				'</xsd:simpleType>',
				$this->type => '<xsd:element name="'.$this->type.'" type="tns:'.$this->type.'Type"/>'
			);
		}
}
?>
