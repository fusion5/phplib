<?
class message implements imessage
{
	protected
		$headers = array 
		(
			"From" => "BeOpen Software <contact@beopen.ro>",
			"MIME-Version" => "1.0",
			"Content-Type" => "text/plain; charset=utf-8",
			"Subject" => "(no subject)"
		); 
	protected
		$message;
	public
	function __construct($to, $subject = null)
	{
    $this->setFrom(first(ADMIN_EMAIL , '"BeOpen Software" <contact@beopen.ro>'));
    $this->setTo($to);
    $this->setDate(date("r"));
		$this->setSubject($subject);
	}
  public
  function setDate($date)
  {
    $this->headers['Date'] = $date;
  }
	protected
	function setSubject($value)
	{
		$this->headers['Subject'] = $value;
	}
	public
	function setFrom($sender)
	{
		$this->headers["From"] = $sender;
	}
	public 
		function setBCC($bcc)
		{
			$this->headers["BCC"] = $bcc;
		}
	public 
		function setCC($cc)
		{
			$this->headers["CC"] = $cc;
		}
  public
		function setTo($receiver)
		{
			if ($receiver)
				$this->headers["To"] = $receiver;
			else
				unset($this->headers["To"]);
		}
  public 
		function getHeaders()
		{
			return $this->headers;
		}
  public
		function setContent($content)
		{
			$this->message = $content;
		}
  public
		function getContent() 
		{
			return $this->message;
		}
  public 
    function send()
    {
      smtp::sendMessage($this);
    }
	public 
		function getHeadersString($headersArray)
		{
			$r = '';
			foreach ($headersArray as $header => $value)
			{
				$r .= $header . ': ' . $value . ' ' . CRLF; 
			}
			return $r;
		}
}
?>
