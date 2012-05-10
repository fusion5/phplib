<?
class mixedmessage extends message
{
	private
		$HTML = null;
	private
		$TEXT = null;
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
	function setHTML($html)
	{
		$this->HTML = $html;
	}
	public
	function setTEXT($text)
	{
		$this->TEXT = $text;
	}
	public
	function getContent()
	{
		if ($this->HTML && $this->TEXT)
		{
			$textMessage = htmlspecialchars_decode($this->TEXT);
			$htmlMessage = $this->HTML;
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
			throw new errors('You didn\'t set the HTML and TEXT content for this multipart message!');
	}
}
?>
