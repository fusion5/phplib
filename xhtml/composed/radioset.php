<?
class radioset extends xmltag
{
	public
	function __construct($attributes)
	{
		$this->attributes = $attributes;
		$options = $this->attributes["options"];
		if (is_string($options))
			throw new errors('Options as strings are not supported!');
		append($attributes['class'], ' set');
		$span = new span($attributes);
		if (isset($this->attributes['label']))
			new label($this->attributes['label'], array('class' => 'set'));
		if (is_array($options))
		{
			foreach($options as $value => $label)
			{
				if (isset($attributes['wrapper']))
				{
				  $wrapper = $attributes['wrapper'];
				  $w = new $wrapper();
				}
				$tmp = $attributes;
				$tmp['type'] = 'radio';
				if (isset($tmp['id']))
					$tmp['id'] .= '_' . $value;
				$tmp['label'] = first(&$this->attributes['labels'][$label], $label);
				$tmp['value'] = $value;
					$tmp['checked'] = ($value == first(&$this->attributes['value'], ''));
				unset($tmp['error']);
				new input($tmp);
				if (isset($w))
					unset($w);
			}
		}
		unset($span);
		if (isset($this->attributes['error']))
		{
			$span = new span(array('class' => 'errormessage'));
			print $this->attributes['error'];
			unset($span);
		}
	}
}
?>
