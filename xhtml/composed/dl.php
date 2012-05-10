<?
class dl extends xmltag
{
	public
	function __construct($attributes)
	{
		$this->attributes = $attributes;
		$options = &$this->attributes["options"];
		if (is_string($options))
			print "Options as strings are not supported!";
		parent::__construct('dl', $attributes, true);
		if (is_array($options))
		{
			foreach($options as $term => $definition)
			{
				new dt($term);
				new dd($definition);
			}
		}
	}
}
?>
