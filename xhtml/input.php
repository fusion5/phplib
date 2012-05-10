<?
class input extends xmltag
{
	static
	public
		$types = array
		(
			"button", "checkbox", "file", "hidden", "image", "password", "radio",
			"reset", "submit", "text"
		);
	protected
		$inputAttributes = array
		(
			"button" => 	array("name", "type", "value", "size", "disabled"),
			"checkbox" => array("name", "type", "value", "size", "disabled", "checked"),
			"file" => 		array("name", "type", "size", "disabled", "accept"),
			"hidden" => 	array("name", "type", "value"),
			"image" => 		array("name", "type", "value", "size", "aligned", "alt", "src", "disabled"),
			"password" => array("name", "type", "value", "size", "disabled"),
			"radio" => 		array("name", "type", "value", "size", "disabled", "checked"),
			"reset" => 		array("name", "type", "value", "size", "disabled"),
			"submit" => 	array("name", "type", "value", "size", "disabled"),
			"text" => 		array("name", "type", "value", "size", "disabled", "readonly", "maxlength")
		);
	private
		$type = '';
	public
	function __construct($attributes)
	{
		$type = $attributes["type"];
		if ($type == null)
			throw new errors('The type attribute must be defined for input fields!');
		getType($type);
		$this->type = $type;
		$this->tagAttributes = $this->inputAttributes[$this->type];
		if (in_array($this->type, array('text', 'file', 'password')))
			$this->displayLabel($attributes);
		append($attributes['class'], ' ' . $this->type);
		parent::__construct('input', $attributes);
	}
	public
	function __destruct()
	{
		parent::__destruct();
		if (in_array($this->type, array('radio', 'checkbox')))
			$this->displayLabel($this->attributes);
		$this->displayOtherInformation($this->attributes);
	}
	private
		function displayOtherInformation($attributes)
		{
			if (isset($attributes['error']))
			{
				$span = new span(array('class' => 'errormessage'));
				print $attributes['error'];
				unset($span);
			}
			else
			if (isset($attributes['description']))
			{
				$span = new span(array('class' => 'description'));
				print $attributes['description'];
				unset($span);
			}
		}
	private
		function displayLabel($attributes)
		{
			if (isset($attributes['label']))
			{
				$params = array('class' => $this->type);
				if (isset($attributes['id']))
					$params['for'] = $attributes['id'];
				new label($attributes['label'], $params);
			}
		}
	protected
	function getChecked($checked)
	{
		if ($checked)
			$checked = "checked";
		else
			$checked = null;
	}
	protected
		function getSize($size)
		{
			$size = min($size, 40);
		}
	protected
	function getDisabled($disabled)
	{
		if ($disabled)
			$disabled = "disabled";
		else
			$disabled = null;
	}
	protected
	function getReadonly($value)
	{
		if ($value)
			$value = "readonly";
		else
			$value = null;
	}
	protected function getValue($value)
	{
	}
	protected
	function getType($type)
	{
		$type = trim(strtolower($type));
		if (in_array($type, input::$types))
			return $type;
		else
			throw new errors("Unrecognized tag type `$type`");
	}
}
?>
