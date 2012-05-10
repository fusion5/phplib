<?
class ctSetField extends controlfield
{
	public
		function render($attributes, fieldset $fieldset = null)
		{
			$attributes = $this->controlAttributes($attributes);
			new comSet($attributes);
		}
}
?>
