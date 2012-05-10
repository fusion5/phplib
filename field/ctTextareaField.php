<?
class ctTextareaField extends controlfield
{
	public
		function render($attributes, fieldset $fieldset = null)
		{
			$attributes = $this->controlAttributes($attributes);
			new textarea($attributes);
		}
}
?>
