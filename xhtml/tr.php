<?
class tr extends xmltag
{
	public 
	function __construct($row = null, $attributes = array())
	{
		if ($row != null)
		{
			$class = '';
			if ($row->first())
				$class .= ' first';
			if ($row->last())
				$class .= ' last';
			if ($row->odd())
				$class .= ' odd';
			else
				$class .= ' even';
			append(&$attributes['class'], ' '.$class);
		}
		parent::__construct("tr", $attributes, true);
	}
}
?>
