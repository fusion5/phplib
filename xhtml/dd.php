<?
class dd extends xmltag
{
	public
	function __construct($caption = '', $attributes = null)
	{
		parent::__construct('dd', $attributes, true);
		if (!$caption) $caption = "&nbsp";
		print $caption;
	}
}
?>
