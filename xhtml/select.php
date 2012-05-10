<?
class select extends xmltag
{
	protected
		$tagAttributes = array("name", "size", "disabled", "multiple");
	public
	function __construct($attributes)
	{
		if (isset($attributes['label']))
		new label($attributes["label"], array(
			'for' => &$attributes['id'],
			'class' => 'select'
		));
		parent::__construct("select", $attributes, true);
		$options = first(&$this->attributes['options'], array());
		$values = first(&$this->attributes['value'], '');
		if (is_null($values))
		{
			$values = array();
		}
		if (is_string($values))
		{
			$values = split(',', $values);
		}
		if (is_string($options))
		{
			$t = split(',', trim($options));
			foreach($t as $val) $options[$val] = $val;
		}
		if (is_array($options))
		foreach($options as $value => $label)
		{
			new option($value, $label, in_array($value, $values));
		}
	}
	protected
	function getName($value)
	{
		if (isset($this->attributes['multiple']))
			$value .= '[]';
	}
	protected
	function getMultiple($value)
	{
		if ($value)
			$value = "multiple";
		else
			$value = null;
	}
}
?>
