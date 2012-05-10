<?
class exchange_message extends xmldata
{
	public
		function __construct(DOMElement $message)
		{
			$prop = $message->getElementsByTagNameNS('DAV:', 'propstat')->item(0)->getElementsByTagNameNS('DAV:', 'prop')->item(0);
			parent::__construct($prop);
		}
	public
		function setSubject($subject)
		{
			$this->set('DAV:', 'subject', $subject);
		}
	public
		function getSubject()
		{
			return $this->get('urn:schemas:httpmail:', 'subject');
		}
	public
		function setHref($href)
		{
			$this->set('DAV:', 'href', $href);
		}
	public
		function getHref()
		{
			return $this->get('DAV:', 'href');
		}
}
?>
