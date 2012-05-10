<?
class multimessage
{
  private
    $to = array();
  private
    $message;
	public
		$onMessageSent = array();	
  public
    function __construct($message, $to = null)
    {
      $this->setTo($to);
      $this->setMessage($message);
    }
  public
    function setMessage($message)
    {
      $this->message = $message;
    }
  public
    function setTo($to)
    {
      $newto = array();
      foreach($to as $item)
        $newto[] = htmlspecialchars_decode($item);
      $this->to = $newto;
    }
  public
    function getMessage()
    {
      return $this->message; 
    }
  public
    function getTo()
    {
      return $this->to;
    }
  public
    function getHeaders()
    {
      return $this->message->getHeaders();
    }
  public
    function getContent()
    {
      return $this->message->getContent();
    }
  public
    function send()
    {      
			smtp::$onMessageSent = $this->onMessageSent;
	  	smtp::sendMultiMessage($this);
   	}
}
?>
