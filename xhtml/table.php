<?
class table extends xmltag
{
	protected
		$tagAttributes = array("border", "cellpading", "cellspacing", "frame", "rules", "summary", "width");
	public
		function __construct(array $attributes = null)
		{
			parent::__construct("table", $attributes, true);
		}
}
?>
