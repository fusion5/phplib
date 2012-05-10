<?
class div extends xmltag
{
	protected
		$tagAttributes = array('align');
	public
	function __construct($attributes = array())
	{
		parent::__construct('div', $attributes, true);
	}
}
?>
