<?
class exchange_messages
{
	private
		$messageXML;
	private
		$messageArray = array();
	public
		function __construct(DOMDocument $messages)
		{
			$this->messageXML = $messages;
			foreach($this->messageXML->firstChild->childNodes as $message)
				$this->messageArray[] = new exchange_message($message);
		}
	public
		function getMessages()
		{
			return $this->messageArray;
		}
}
?>
