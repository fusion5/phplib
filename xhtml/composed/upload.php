<?
class upload extends xmltag
{
	public
		function __construct($attributes)
		{
			parent::__construct('span', array ('id' => $attributes['id'], 'class' => $attributes['class']), true);
			if (isset($this->attributes['label']))
				new label($this->attributes['label'], array('class' => $attributes['class']));
			$dbuploadaction = dbuploadaction::getInstance($this->fsid);
			if ($dbuploadaction instanceof dbuploadaction)
			{
				$this->result = $dbuploadaction->result();
			}
		}
}
?>
