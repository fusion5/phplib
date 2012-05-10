<?
class label extends xmltag
{
	protected
		$tagAttributes = array("for");
	public
	function __construct($caption = '', $attributes = array())
	{
		if ($caption)
		{
			parent::__construct('label', $attributes, true);
				print $caption;
		}
	}
}
?>
