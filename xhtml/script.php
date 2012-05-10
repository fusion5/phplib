<?
class script extends xmltag
{
	protected
		$attributes = array	(	"type" => "text/javascript");
	protected
		$tagAttributes = array("type", "src");
	public
	function __construct($attributes)
	{
		if (isset($attributes['src']))
			$attributes['src'] = htmlentities($attributes['src']);
		parent::__construct('script', $attributes, true);
	}
}
?>
