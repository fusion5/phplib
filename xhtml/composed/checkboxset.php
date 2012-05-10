<?
class checkboxset extends xmltag
{
	public
	function __construct($attributes)
	{
		$this->attributes = $attributes;
		if (isset($this->attributes['value']))
			$values = $this->attributes['value'];
		else
			$values = array();
		append($attributes['class'], ' set');
		if (is_string($values))
			$values = split(',', trim($values));
		$options = $this->attributes['options'];
		if (is_string($options))
			throw new errors ('Options as strings are not supported!');
		$span = new span($attributes);
		if (isset($this->attributes['label']))
			new label($this->attributes['label'], array ('class' => 'set'));
		if (is_array($options))
		{
			new input(array(
				'type' => 'hidden',
				'name' => $attributes['name'],
				'value' => ''
			));
			foreach($options as $value => $label)
			{
				$tmp = $attributes;
				$tmp['type'] = 'checkbox';
				if (isset($tmp['id']))
					$tmp['id'] .= '_' . $value;
				$tmp['label'] = first(&$this->attributes['labels'][$label], $label);
				$tmp['value'] = $value;
				$tmp['name'] = $attributes['name']."[$value]";
				$tmp['checked'] = in_array($value, $values);
				unset($tmp['error']);
				new input($tmp);
			}
		}
		unset ($span);
		if (isset($this->attributes['error']))
		{
			$span = new span(array('class' => 'errormessage'));
			print $this->attributes['error'];
			unset($span);
		}
	}
}
?>
