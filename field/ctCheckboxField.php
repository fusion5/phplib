<?
class ctCheckboxField extends controlfield
{
	public
		function render($attributes, fieldset $fieldset = null)
		{
			$attributes = $this->controlAttributes($attributes);
			if (!isset($attributes['value']) || (!$attributes['value'] && $attributes['value'] !== "0"))
				$attributes['value'] = 'on';
			if (array_key_exists('last_value', $attributes))
				$attributes['checked'] = $attributes['last_value'] == $attributes['value'];
			new input($attributes);
		}
}
?>
