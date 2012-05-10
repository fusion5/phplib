<?
class dt extends xmltag
{
	public
	function __construct($caption = '', array $attributes = null)
	{
		parent::__construct('dt', $attributes, true);
		print $caption;
	}
}
?>
