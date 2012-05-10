<?
class comDatetime extends xmltag
{
	private
		$format = 'Y-m-d H:i:s';
	private
		$fields = array();
	private
		$value = '';
	private static
		$allowed = 'YmdHis';
	public
		function __construct($attributes)
		{
			if (!isset($attributes['id']))
				$attributes['id'] = rand(1000, 9999);
			if (isset($attributes['format']))
				$this->setFormat($attributes['format']);
			$divAttributes = array('id' => $attributes['id'], 'class' => $attributes['class']);
			parent::__construct('div', $divAttributes, true);
			$this->attributes = $attributes;
			if (isset($this->attributes['label'])) // We don't pass the same class attribute to the label because it might be used for javascript replacing.
				new label($this->attributes['label']);
			$value = &$this->attributes['value'];
			if (isset($value))
			{
				if (is_string($value))
					$value = strtotime($value);
				if (is_numeric($value))
					$this->value = date($this->format, $value);
				if (is_array($value))
					$this->value = $value['Y'] . '-' . $value['m'] . '-' . $value['d'];
			}
			if (is_null($value))
				$value = time();
			$this->value = $value;
			$this->parseFormat($this->format);
	}
	public
		function setFormat($format)
		{
			$this->format = $format;
		}
	private
		function parseFormat($format)
		{
			while (($label = $this->consumeRegexp(&$format, '[^'.comDatetime::$allowed.']+')) && ($format != '') || (strlen($format) > 0))
			{
				$token = $this->consumeRegexp(&$format, '['.comDatetime::$allowed.']{1}');
				if (is_callable(array($this, $token)))
				{
					print $label;
					$attributes = $this->getAttributes($token);
					$this->$token($attributes);
				}
				else
					new p('Missing ' . $token . '() method');
			}
		}
	private
		function consumeRegexp($string, $regexp)
		{
			$regexp = '/^'.$regexp.'/';
			preg_match($regexp, $string, $matches);
			$string = preg_replace($regexp, '', $string);
			if (isset($matches[0]))
				return $matches[0];
		}
	private
		function getAttributes($type)
		{
			$attributes = array();
			if (is_array($this->value))
				$attributes['value'] = $this->value[$type];
			else
				$attributes['value'] = date($type, $this->value);
			$attributes['name'] = $this->attributes['name'] . '['.$type.']';
			return $attributes;
		}
	private
		function Y($attributes)
		{
			$attributes['options'] = array();
			for ($i = 2017; $i >= 1917; $i--)
			{
				$attributes['options'][$i] = $i;
			}
			$attributes['class'] = 'year';
			new select($attributes);
		}
	private
		function m($attributes)
		{
			$attributes['options'] = array(
				'01' => g('January'),
				'02' => g('February'),
				'03' => g('March'),
				'04' => g('April'),
				'05' => g('May'),
				'06' => g('June'),
				'07' => g('July'),
				'08' => g('August'),
				'09' => g('September'),
				'10' => g('October'),
				'11' => g('November'),
				'12' => g('December')
			);
			$attributes['class'] = 'month';
			new select($attributes);
		}
	private
		function d($attributes)
		{
			$attributes['options'] = array();
			for ($i = 1; $i <= 31; $i++)
			{
				$attributes['options'][$i] = $i;
			}
			$attributes['class'] = 'day';
			new select($attributes);
		}
	private
		function H($attributes)
		{
			$attributes['type'] = 'text';
			$attributes['maxlength'] = 2;
			$attributes['size'] = 2;
			$attributes['class'] = 'hour';
			new input($attributes);
		}
	private
		function i($attributes)
		{
			$attributes['type'] = 'text';
			$attributes['maxlength'] = 2;
			$attributes['size'] = 2;
			$attributes['class'] = 'minute';
			new input($attributes);
		}
	private
		function s($attributes)
		{
			$attributes['type'] = 'text';
			$attributes['maxlength'] = 2;
			$attributes['size'] = 2;
			$attributes['class'] = 'second';
			new input($attributes);
		}
}
?>
