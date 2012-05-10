<?
class button extends xmltag
{
	protected
		$tagAttributes = array('disabled', 'name', 'type', 'value');
	public
		function __construct($attributes = null)
		{
			parent::__construct('button', $attributes, true);
			if (isset($attributes['caption']))
				print $attributes['caption'];
		}
}
?>
