<?
class mixeddocmessage extends message
{
	private
		$HTMLlink = null;
	private
		$TEXTLink = null;
	private
		$central;
	const
		boundary = "==Multipart_Boundary_p0xsf34gsd2312v";
	public
	function __construct($recipient, $subject)
	{
		parent::__construct($recipient, $subject);
		$this->central = central();
		$boundary = mixeddocmessage::boundary;
		$this->headers["Content-Type"] = "multipart/alternative; boundary=\"$boundary\"";
	}
	public
	function setHTMLLink($link, $parameters = '')
	{
		$this->HTMLLink = new state($link, $parameters);
	}
	public
	function setTEXTLink($link, $parameters = '')
	{
		$this->TEXTLink = new state($link, $parameters);
	}
	public
	function getContent()
	{
		if ($this->HTMLLink && $this->TEXTLink)
		{
			$textMessage = executive::getDocumentContent($this->TEXTLink->getPath(), $this->TEXTLink->getParamStr()); 
			$textMessage = htmlspecialchars_decode($textMessage);
			$htmlMessage = executive::getDocumentContent($this->HTMLLink->getPath(), $this->HTMLLink->getParamStr(), "emailxhtml");
			$boundary = mixeddocmessage::boundary;			
			$message = "Acesta este un mesaj multi-part in format MIME \r\n";
			$message .= "--$boundary \r\n";
			$message .= "Content-Type: text/plain; charset=\"utf-8\" \r\n";
			$message .= "Content-Transfer-Encoding: 7bit \r\n\r\n";
			$message .= $textMessage . "\r\n\r\n";  
			$message .= "--$boundary \r\n";
			$message .= "Content-Type: text/html; charset=\"utf-8\" \r\n";
			$message .= "Content-Transfer-Encoding: 7bit \r\n\r\n";
			$message .= $htmlMessage . "\r\n\r\n";;  
			$message .= "--$boundary--";
			return $message;
		}
		else
			print "Nu ati setat HTMLLink si TEXTLink pentru acest mesaj multipart!";
	}
}
?>
