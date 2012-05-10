<?
class span extends xmltag
{
	public
	function __construct($attributes = array())
	{
		parent::__construct('span', $attributes, true);
		print ' ';
	}
}
?>
