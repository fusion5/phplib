<?
class textarea extends xmltag
{
	protected
		$tagAttributes = array("cols", "rows", "disabled", "name", "readonly");
	public
	function __construct($attributes)
	{
		if (is_string(&$attributes['label']))
			new label($attributes['label'], array(
				'for' => &$attributes['id'],
				'class' => 'textarea'
			));
		parent::__construct('textarea', $attributes, true);
		if (isset($this->attributes['value']))
			print $this->attributes['value'];
	}
	public
		function __destruct()
		{
			parent::__destruct();			
			if (isset($this->attributes['error']))
			{
				$span = new span(array('class' => 'errormessage'));
				print $this->attributes['error'];
				unset($span);
			}
		}
}
?>
