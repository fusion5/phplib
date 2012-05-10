<?
class popup extends a
{
	private
		$width = 400;
	private
		$height = 300;
	private
		$refresh = false;
	public
	function __construct($url, $attributes = null, $width = 700, $height = 450)
	{
		append (&$attributes['class'], ' popup');
		$this->attributes = $attributes;
		$attributes['target'] = '_blank';
		$this->width = $width;
		$this->height = $height;
		$url->addParam('popup', '1');
		if (isset($this->attributes['refresh']))
			$url->addParam('refresh', '1');
		$title = &$this->attributes["title"];
		if ($title) $title .= " - ";
		else $title = ''; 
		if (empty($attributes['disable_edit'])) $label = g('Opens in new window %s');
		$title .= sprintf($label, "($this->width x $this->height)");
		parent::__construct($url, $this->attributes);
	}
}
?>
