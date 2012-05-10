<?
class exchange 
{
	private
		$url;
	private
		$username;
	private
		$password;
	private
		$http;
	public
		function __construct($url, $username, $password)
		{
			$this->url = $url;
			$this->username = $username;
			$this->password = $password;
			$this->http = new http();
			$this->http->headers["Content-Type"] = 'text/xml; charset="UTF-8"';
			$this->http->headers["Depth"] = "0";
			$this->http->headers["Translate"] = "f";
		}
	public
		function getMessages($folder)
		{
			$this->http->xmlrequest = '<?xml version="1.0"?>';
			$this->http->xmlrequest .= <<<END
			<a:searchrequest xmlns:a="DAV:" xmlns:s="http://schemas.microsoft.com/exchange/security/">
				 <a:sql>
				 SELECT "DAV:href", "urn:schemas:httpmail:subject", 
				 "urn:schemas:httpmail:from", "urn:schemas:mailheader:message-id"
				 FROM "{$this->url}exchange/{$this->username}/{$folder}"
				 WHERE "DAV:isfolder" = False
				 AND "DAV:ishidden" = False
				 </a:sql>
			</a:searchrequest>
END;
			if (!$this->http->fetch($this->url."/exchange/$this->username/$folder", 0, null, $this->username, $this->password, "SEARCH")) 
				throw new errors('I encountered a http request problem!');
			$exchange_response = new DOMDocument();
			$exchange_response->formatOutput = true;
			if (!$exchange_response->loadXML($this->http->body))
				return false;
			if (!($exchange_response->firstChild instanceof DOMElement))
				return false;
			$messageArray = array();
			foreach($exchange_response->firstChild->childNodes as $message)
				$messageArray[] = new exchange_message($message);
			return $messageArray;
		}
	public
		function getProperties($message_href)
		{
			$this->http->xmlrequest = '<?xml version="1.0"?>';
			$this->http->xmlrequest .= <<<END
			<a:propfind xmlns:a="DAV:">
					<a:allprop/>
			</a:propfind>
END;
			if (!$this->http->fetch($message_href, 0, null, $this->username, $this->password, "PROPFIND")) 
				return false;
			$exchange_response = new DOMDocument();
			$exchange_response->formatOutput = true;
			if (!$exchange_response->loadXML($this->http->body))
				return false;
			trace(htmlspecialchars($exchange_response->saveXML()));
		}
	public
		function getAttachements($message_href, $folder = 'Inbox')
		{
			$this->http->clean();
			$this->http->xmlrequest = '';
			trace($this->http->header);
			if (!$this->http->fetch($message_href, 0, null, $this->username, $this->password, "X-MS-ENUMATTS")) 
				throw new errors('I encountered a http request problem!');
			$exchange_response = new DOMDocument();
			$exchange_response->formatOutput = true;
			if (!$exchange_response->loadXML($this->http->body))
			{
				trace(htmlspecialchars($this->http->body));
				return false;
			}
			if (!($exchange_response->firstChild instanceof DOMElement))
				return false;
			return htmlspecialchars($exchange_response->saveXML());
		}
}
?>
