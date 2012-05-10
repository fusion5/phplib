<?
class option extends xmltag
{
	protected
		$tagAttributes = array("disabled", "label", "value", "selected");
	public
	function __construct($value, $label, $selected = false)
	{
		$attributes = array
		(
			"value" => $value
		);
		if ($selected)
			$attributes["selected"] = "selected";
		parent::__construct('option', $attributes, true);
			print $label;
	}
}
?>
