<?
class httpException extends Exception 
{
	public 
		function __construct($code, $text = 'HTTP Error') 
		{
			switch($code)
			{
				case 200:
					$text = 'OK';
				break;
				case 304:
					$text = 'Not Modified';
				break;
				case 401:
					$text = 'Unauthorized';
				break;
				case 404:
					$text = 'Not Found';
				break;
			}
			parent::__construct($text, $code);
		}
	public
		function getHeader()
		{
			$code = $this->code;
			$text = $this->message;
    	if (substr(php_sapi_name(), 0, 3) == 'cgi')
    	{
    		return "Status: {$code} {$text}";
    	}
    	elseif ($_SERVER['SERVER_PROTOCOL'] == 'HTTP/1.1' OR $_SERVER['SERVER_PROTOCOL'] == 'HTTP/1.0')
    	{
    		return $_SERVER['SERVER_PROTOCOL']." {$code} {$text}";
    	}
    	else
    	{
    		return "HTTP/1.1 {$code} {$text}";
    	}
		}
	public
		function __toString()
		{
			$string = parent::__toString();
			$string = preg_replace('/(mysql|pgsql)->__construct\([^\)]*\)/m', '***', $string);
			return $string;
		}
}
?>
