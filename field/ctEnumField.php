<?
class ctEnumField extends controlfield
{
	public
		function render($attributes, fieldset $fieldset = null)
		{
			$attributes = $this->controlAttributes($attributes);
			new comEnum($attributes);
		}
}
?>
