<?
abstract class h extends xmltag
{
	public
	function __construct($number, $caption = '', $attributes = array())
	{
		parent::__construct('h'.$number, $attributes, true);
			print $caption;
	}
}
?>
