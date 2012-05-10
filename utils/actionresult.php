<?
class actionresult
{
	public	
		$result;
	public	
		$code;
	const
		prefix = 'rez-e3';
	private
		$encr;
	public
		function __construct($code, $result = null)
		{
			$this->encr = actionresult::getEncryption();
			$this->result = $result;
			$this->code = $code;
		}
	private static
		function getEncryption()
		{
			$encr = new encryption(md5($_SERVER["REMOTE_ADDR"] . $_SERVER["HTTP_USER_AGENT"]));
			return $encr;		
		}
	public
		function get()
		{
			return $this->result;
		}
	public
		function getCode()
		{
			return $this->code;
		}
	public
		function setCode($val)
		{
			$this->code = $val;
		}
	public
		function is ($code)
		{
			return $this->getCode() == $code;
		}
	public static
		function parse($str)
		{
			$str = actionresult::getEncryption()->decrypt($str);
			if (substr($str, 0, strlen(actionresult::prefix)) == actionresult::prefix)
			{
				$serialized = substr($str, strlen(actionresult::prefix));
				$o = unserialize($serialized);
				return $o;
			}
			return false;
		}
	public
		function getString()
		{
			return $this->encr->encrypt(actionresult::prefix . serialize($this));
		}
}
?>
