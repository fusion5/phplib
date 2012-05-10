<?
class pg_numeric_field extends pg_databasefield
{
	public
		function renderDatabaseField($attributes)
		{
			$attributes = $this->controlAttributes($attributes);
			$attributes['type'] = 'text';
			$attributes['size'] = $this->getTotalDigits() + 1;
			new input($attributes);
		}
	public
		function setValue($value)
		{
			if ($this->mayBeNull && ($value === null || $value === ''))
			{
				$this->value = null;
				return;
			}
			if ($value !== null)
			{
				if (!is_numeric($value))
					throw new errors(gf('The field %s must be numeric. Current value is: %s', $this->name, $value), $this->name);
				if ($this->getCurrentMode() != 'delete')
					$value = number_format($value, 2, '.', '');
			}
			parent::setValue($value);
		}
	public
		function getXMLSchemaPart()
		{
			$totalDigits = $this->getTotalDigits();
			$fractionDigits = $this->getFractionDigits();
			$typeName = $this->type . '_' . $totalDigits . '_' . $fractionDigits;
			$r = 
				'<xsd:simpleType name="'.$typeName.'Type">'.
				'<xsd:restriction base="xsd:decimal">'.
				'<xsd:totalDigits value="'.$totalDigits.'"/>'.
				'<xsd:fractionDigits value="'.$fractionDigits.'"/>'.
				'</xsd:restriction>'.
				'</xsd:simpleType>';
			return array(
				$typeName . 'Type' => $r,
				$typeName => '<xsd:element name="'.$typeName.'" type="tns:'.$typeName.'Type" />'
			);
		}
	public
		function getTotalDigits()
		{
			$typmod = $this->getInfo();
			$typmodOffset = 4;
			$totalDigits = (($typmod - $typmodOffset) >> 16) & 0xffff;
			return $totalDigits;
		}
	public
		function getFractionDigits()
		{
			$typmod = $this->getInfo();
			$typmodOffset = 4;
			$fractionDigits = ($typmod - $typmodOffset) & 0xffff;
			return $fractionDigits;
		}
}
?>
