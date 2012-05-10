<?
class docmessage extends message
{
	private
		$link = null;
	private
		$central;
	public
	function __construct($recipient, $subject, $path, $parameters)
	{
		parent::__construct($recipient, $subject);
		$this->setLink(new url($path, $parameters));
		$this->central = central();
	}
	public
	function setLink($link)
	{
		$this->link = $link;
	}
	public
	function getContent()
	{
		$content = executive::getDocumentContent($this->link->getPath(), $this->link->getParamStr(), "txt");
		$content = htmlspecialchars_decode($content);
		return $content;
	}
}
?>
