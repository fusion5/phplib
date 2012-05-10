<?
class img extends xmltag
{
	protected
		$tagAttributes = array('src', 'alt', 'height', 'width', 'ismap', 'longdesc', 'usemap');
	public
		function __construct($attributes = null)
		{
			parent::__construct('img', $attributes);
		}
}
?>
