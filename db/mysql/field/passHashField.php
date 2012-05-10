<?
class passHashField extends myVarcharField
{
	public
		function renderDatabaseField($attributes)
		{
			$attributes = $this->controlAttributes($attributes);
			$attributes['type'] = 'password';
			$attributes['size'] = $this->getLength();
			new input($attributes);
		}
	public
		function setValue($value)
		{
			if ($value && (strlen($value) < 4))
				throw new errors(gf(
					'Parola pe care ati specificat-o este prea scurta (%s) caractere. Trebuie sa aiba minim 4', strlen($value)
				), 'pass');
			parent::setValue($value);
		}
	public
		function renderValue()
		{
			print str_repeat('*', strlen($this->getValue()));
		}
}
?>
