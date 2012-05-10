<?
class smtp
{
  private
    $connection;
  private static
    $messages = array();
  private static
    $multimessages = array();
  private static
    $instance; 
	public static
		$onMessageSent = array();
  const 
    debug = false;
	public
		function connected()
		{
			return $this->connection;
		}
  public
    function flush() 
    {
      if (count(smtp::$messages) || count(smtp::$multimessages))
      {
				try
				{
					if (!$this->connected())
						$this->connect();
					foreach(smtp::$messages as $message)
						$this->sendSmtpMessage($message);
					smtp::$messages = array();
					foreach(smtp::$multimessages as $multimessage)
						$this->sendSmtpMultiMessage($multimessage);
					smtp::$multimessages = array();
					smtp::$onMessageSent = array();
					$this->quit();
				}
				catch (errors $e)
				{
					smtp::$messages = array();
					smtp::$multimessages = array();
					smtp::$onMessageSent = array();
					$this->quit();
					if (smtp::debug && DEBUG_MODE())
						throw $e;
				}
      }
    }
  public
    function __destruct()
    {
			$this->flush();
    }
  public static
    function getInstance() 
    {
      if (isset(smtp::$instance))
        return smtp::$instance;
      else
        return smtp::$instance = new smtp();
    }
  public static
    function destroy()
    {
      smtp::getInstance()->flush();
    }
  private
    function sendSmtpMultiMessage($multimessage)
    {
			$headers = $multimessage->getHeaders();
			unset($headers['To']);
			$this->from(ADMIN_EMAIL);
			foreach($multimessage->getTo() as $to)
			{
				$matches = array();
				preg_match('|<([^>]*)>|', $to, $matches);
				try
				{
					$this->rcpt($to);
					$success = true;
				}
				catch (errors $e)
				{
					$success = false;
				}
				if (is_callable(smtp::$onMessageSent))
					call_user_func(smtp::$onMessageSent, $success, $matches[1]);
			}
			$this->outputMessage($multimessage->getMessage(), $headers);
    }
  public
    function sendSmtpMessage(imessage $message)
    {
			if ($this->connected())
			{
				$headers = $message->getHeaders();
				$body = $message->getContent();
				$this->from(ADMIN_EMAIL);
				$this->rcpt($headers['To']);
				$this->outputMessage($message, $headers);
			}
			else
				throw new errors(g('Not connected to a mail server!'));
    }
  private
    function outputMessage(imessage $m, array $h)
    {
      $this->data();
      $body = $m->getContent();
      $headerString = '';
      foreach($h as $header => $value) 
        $headerString .= $header.": " . $value . CRLF;
			$headerString = str_replace(CRLF.'.', CRLF.'..', $headerString);
			$body = str_replace(CRLF.'.', CRLF.'..', $body);
			$body = $body[0] == '.' ? '.'.$body : $body;
			$body = str_replace(array("\r\n", "\n", "\r"), '%%CRLF%%', $body);
			$body = str_replace('%%CRLF%%', CRLF , $body);
			$this->sendData($headerString . CRLF . $body . CRLF . '.');
			if ($this->getCode() !== '250')
				throw new errors("Can't send e-mail " . $this->getData());
    }
  public static
    function sendMessage(imessage $m)
    {
      if ($m instanceof imessage)
        array_push(smtp::$messages, $m);
      else
        throw new errors("Can't send message because it's not an instance of imessage!");
    }
  public static
    function sendMultiMessage($m)
    {
	  if ($m instanceof multimessage)
        array_push(smtp::$multimessages, $m);
      else
        throw new errors("Can't send multimessage: its not a multimessage instance!");
    }
  public
    function connect()
    {
			if (function_exists("fsockopen"))
      {
        $this->connection = @fsockopen 
        (
          MAIL_HOST, 
          25,
          $errno,
          $errmessage,
          5
        );
        if ($this->connection)
        {
          if (function_exists('socket_set_timeout')) stream_set_timeout($this->connection, 5);
          $greeting = $this->getData();       
					$this->ehlo();
					$this->auth();
        }
        else
          throw new errors("Could not connect to mail server");
      }
      else
        throw new errors("fsockopen function doesn't exist!");
    }
  private
    function getCode()
    {
      return substr(trim($error = $this->getData()), 0, 3);
    }
  private
    function ehlo()
    {
      $this->sendData('EHLO ' . MAIL_HOST);
      if ($this->getCode() !== '250')
        throw new errors('EHLO command failed!');
    }
  private
    function helo()
    {
      $this->sendData('HELO '. MAIL_HOST);
      if ($this->getCode() !== '250')
        throw new errors('HELO command failed!');
    }	
  public
  	function quit()
  	{
      $this->sendData('QUIT');
			if ($this->connection)
				fclose($this->connection);
  	}
  private
    function auth()
    {
      if (defined('SMTP_ACCOUNT') && defined('SMTP_PASS') && SMTP_ACCOUNT && SMTP_PASS)
      {
        $this->sendData('AUTH LOGIN');
        if ($this->getCode() === '334')
          $this->sendData(base64_encode(SMTP_ACCOUNT));
        if ($this->getCode() === '334')
          $this->sendData(base64_encode(SMTP_PASS));
        if ($this->getCode() !== '235')
          throw new errors('AUTH command failed!');
      }
    }
  private
    function data()
    {
      $this->sendData('DATA');
      if ($this->getCode() !== '354')
        throw new errors("Invalid Data command");
    }
  private
    function rcpt($to)
    {
      $this->sendData('RCPT TO:'.$to);
      if ($this->getCode() !== '250') 
        throw new errors("Invalid Recipient (To:) Format");
    }
  private
  	function from($from)
	  {
      $this->sendData('MAIL FROM:'.$from);
      if ($this->getCode() !== '250') 
        throw new errors("Invalid From Format");
    }
  private
    function sendData($data)
    {
  		if(is_resource($this->connection))
  		{
				$data .= CRLF;
					if (smtp::debug && DEBUG_MODE()) new trace('>> ' . $data);
				return fputs($this->connection, $data);
  		}
  		else
  			return false;
    }
  private
    function getData()
    {
      $return = '';
      $line   = '';
      $loops  = 0;
      if(is_resource($this->connection))
      {
        while
        (
          (strpos($return, CRLF) === false || substr($line,3,1) !== ' ') && 
          $loops < 100
        )
        {
          $line = fgets($this->connection, 512);
          $return .= $line;
          $loops++;
        }
        if (smtp::debug && DEBUG_MODE())
          new trace('<< ' . $return);
        return $return;
      }
      else
        return false;
    }
} 
?>
