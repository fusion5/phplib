<?
class th extends xmltag
{
	public
	function __construct($caption = '', $attributes = array())
	{
		parent::__construct('th', $attributes, true);
		print $caption;
	}
}
?>
