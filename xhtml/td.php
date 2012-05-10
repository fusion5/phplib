<?
class td extends xmltag
{
	protected
		$tagAttributes = array("colspan", "rowspan");
	public
		function __construct($caption = '', $attributes = array())
		{
			parent::__construct('td', $attributes, true);
			print $caption;
		}
}
?>
