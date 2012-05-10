<?
class p extends xmltag
{
	public
	function __construct($caption = '', $attributes = array())
	{
		parent::__construct('p', $attributes, true);
			print $caption;
	}
}
?>
