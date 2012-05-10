<?
class attmessage extends message
{
	private
		$data = null;
	private
		$fileName = 'file';
	private
		$central;
	const
		boundary = "==Multipart_Boundary_x0xsf34gsd2312v";
	public
	function __construct($recipient, $subject)
	{
		parent::__construct($recipient, $subject);
		$this->central = central();
		$boundary = self::boundary;
		$this->headers["MIME-Version"] = "1.0";
		$this->headers["Content-Transfer-Encoding"] = "8bit";
		$this->headers["Content-Type"] = "multipart/mixed; boundary=\"$boundary\"";
	}
	public
		function setData($fileString)
		{
			$this->data = $fileString;
		}
	public
		function setFileName($f)
		{
			$this->fileName = $f;
		}
	public
		function getContent()
		{
			$message = '';
			$message .= $this->getBoundary(); 
			$message .= $this->getHeadersString(array(
				'Content-Type' => 'text/plain; charset="utf-8"',
				'Content-Transfer-Encoding' => '7 bit'
			)) . CRLF;
			$message .= $this->message . CRLF . CRLF;
			if ($this->data)
			{
				$message .= $this->getBoundary();
				$message .= $this->getHeadersString(array(
					'Content-Type' => 'application/pdf; name="'.$this->fileName.'"',
					'Content-Disposition' => 'attachment; filename="'.$this->fileName.'"',
					'Content-Transfer-Encoding' => 'base64'
				)) . CRLF;
				$message .= chunk_split(base64_encode($this->data)) . CRLF . CRLF;
			}
			$message .= $this->getBoundary();
			return $message;
		}
	public
		function getBoundary()
		{
			return '--' . self::boundary . CRLF;
		}
}
?>
