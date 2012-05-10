<?
class myFloatField extends myDatabaseField
{
	private
		$rounds;
	private
		$decimals;
	public
		function setInfo($i)
		{
			parent::setInfo($i);
			if ($i)
			{
				if (is_array($i))
				{
					$this->decimals = $i['NUMERIC_PRECISION'];
					$this->rounds = $i['NUMERIC_SCALE'];
				}
				if (is_string($i))
				{
					$matches = split(',', $i);
					if (count($matches) == 2)
					{
						$this->decimals = $matches[0];
						$this->rounds = $matches[1];
					}
				}
			}
		}
	public
		function renderDatabaseField($attributes)
		{
			$attributes = $this->controlAttributes($attributes);
			$attributes['type'] = 'text';
			$attributes['size'] = $this->getLength();
			new input($attributes);
		}
	public
		function setValue($value)
		{
			if (isset($value))
			{
				if (is_array($value) && isset($value['n']) && isset($value['d']))
				{
					$round = $value['n'];
					$decimal = $value['d'];
					$value = $round + $decimal/(strlen($decimal));
				}
				if (($value !== '') && !is_numeric($value))
				{
					$value = strtonumber($value);
					if (!is_numeric($value))
					{
						$locale = localeconv();
						$dec_point = $locale['decimal_point'];
						$thousands_sep = $locale['thousands_sep'];
						throw new errors(gf(
							'Field %s must be numeric. Current value is: %s The input must have "%s" as decimal point and "%s" as thousands separator', 
							$this->name, $value, $dec_point, $thousands_sep
						), $this->name);
					}
				}
				if ($this->unsigned)
					if ($value < 0)
						throw new errors(gf("Field %s must be >= 0. Current value unnaceptable: %s ", $this->name, $value), $this->name);
				$value = (float)$value;
			}
			parent::setValue($value);
		}
	public
		function getXMLSchemaPart()
		{
			return '';
		}
}
?>
