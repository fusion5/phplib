<?
class htmlmessage extends message
{
	private
		$HTML = null;
	private
		$central;
	const
		boundary = "==Multipart_Boundary_p0xsf34gsd2312v";
	public
	function __construct($recipient, $subject)
	{
		parent::__construct($recipient, $subject);
		$this->central = central();
		$this->headers["Content-Type"] = "text/html; charset=utf-8";
		$this->headers["Content-Transfer-Encoding"] = "8bit";
	}
	public
	function setHTML($html)
	{
		$this->HTML = $html;
	}
	public
	function getContent()
	{
		$message = '';
		if ($this->HTML)
		{
			$htmlMessage = $this->HTML;
			$message .= $htmlMessage . CRLF . CRLF;
			return $message;
		}
		else
			print 'You didn\'t set the HTML content for this html message!';
	}
}
?>
