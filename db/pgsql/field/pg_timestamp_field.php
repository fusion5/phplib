<?
class pg_timestamp_field extends pg_databasefield
{
	public
		function renderDatabaseField($attributes)
		{
			$attributes = $this->controlAttributes($attributes);
			if (!isset($attributes['format']))
				$attributes['format'] = 'Y-m-d H:i';
			new comDatetime($attributes);
		}
	public
		function setValue($parameter)
		{
			if ($parameter !== null)
			{
				if (is_array($parameter))
				{
					$value = $parameter['Y'].'-'.$parameter['m'].'-'.$parameter['d'].' '.$parameter['H'].':'.$parameter['i'];
					if (isset($parameter['s']))
						$value .= ':' . $parameter['s'];
					$parameter = $value;
				}
				else
					$parameter = (string)$parameter;
				if (!ereg('^([0-9]{4}[/-][0-9]{1,2}[/-][0-9]{1,2})([ \t]+(([0-9]{1,2}:[0-9]{1,2}){1}(:[0-9]{1,2}){0,1}(\.[0-9]+){0,1}([ \t]*([+-][0-9]{1,2}(:[0-9]{1,2}){0,1}|[a-zA-Z]{1,5})){0,1})){0,1}$', $parameter))
				{
					$label = first(&$this->name, 'Unkown field');
					throw new errors(gf('Invalid date read from <em>%s</em>: %s', $label, $parameter));
				}
			}
			parent::setValue($parameter);
		}
	public
		function getXMLSchemaPart()
		{
			$typmod = $this->getInfo();
			$typeName = $this->type;
			if ($typmod != -1)
				$typeName .= '_' . $typmod;
			$r = 
				'<xsd:simpleType name="'.$typeName.'Type">'.
				'<xsd:restriction base="xsd:dateTime">';
			if ($typmod == -1)
				$r.= '<xsd:pattern value="\p{Nd}{2}:\p{Nd}{2}:\p{Nd}{2}(.\p{Nd}+)?" />';
			else 
				if ($typmod == 0)
					$r.= '<xsd:pattern value="\p{Nd}{2}:\p{Nd}{2}:\p{Nd}{2}" />';
				else
					$r.= sprintf('<xsd:pattern value="\p{Nd}{2}:\p{Nd}{2}:\p{Nd}{2}.\p{Nd}{%d}" />', $typmod);
			$r.=
				'</xsd:restriction>'.
				'</xsd:simpleType>';
			return array(
				$typeName.'Type' => $r,
				$typeName => '<xsd:element name="'.$typeName.'" type="tns:'.$typeName.'Type" />'
			);
		}
}
?>
