<?
abstract class resource 
{
	protected
		$docPath; // path class instance
	protected 
		$user;
	protected
		$db; 
	protected
		$central;
	protected
		$param;
	public
	function __construct($path, $user, $central, $db) 
	{
		$this->central = $central;
		$this->db = $db;
		$this->setUser(&$user);
		$this->docPath = new path($path);
		if (!USE_DB_LANG)
			openLanguage(resource::getLanguageFilePath($this->docPath->getPath()));
		if (file_exists('include.php')) include_once('include.php');	
	}
	public
	function __get($name)
	{
		return $this->db->$name;
		if (DEBUG_MODE())
			print "Empty attribute called: resource::$$name";
	}
	public									
		function getFilePath($path)
		{
			if ($path != "/")
				$dir = './doc/'. $this->central->state->getLanguage() . $path . '/';
			else
				$dir = './doc/'. $this->central->state->getLanguage() . '/';
			return $dir;
		}
  public static
		function getLanguageFilePath($path = null)
		{
			$state = state(false);
			if ($state)
			{
				if ($path == null) $path = '/';
				$docPath = new path($path);
				$filename = $docPath->getLast();
				if (!$filename) $filename = "root";
				if (!defined("DIR_LOCALE"))	define("DIR_LOCALE", './docres/locale/');
				if ($path != "/") $dir = DIR_LOCALE . $state->getLanguage() . $docPath->getPath() . '/';
				else $dir = DIR_LOCALE . $state->getLanguage() . '/';
				$textfile = $dir . $filename . ".txt";
				return $textfile;
			}
		}
	abstract protected function perform();
	public
		function output(&$param) 
		{
			$this->param = $param;
			$k = $this->perform();
			$param = $this->param;
			return $k;
		}
	public		
	function setUser($userInstance) 
	{
		$this->user = $userInstance;
	}
	public
	function getParamArray()
	{
		return $this->central->state->getParamArray();
	}
	protected
	function getParam($paramStr) 
	{
		return $this->central->state->getParam($paramStr);
	}
	protected
	function addParam($paramStr, $val) 
	{
		return $this->central->state->addParam($paramStr, $val);
	}
	public		
	function setPath($p) 
	{
		$this->docPath->setPath($p);	
	}
	public
	function getPath() 
	{
		return $this->docPath->getPath();
	}
	protected
	function getDocumentInstance($pathString, $docType = 'xhtml', $docTitle = '')
	{
		return $this->central->getDocument(new path($pathString), $this->user, $docType, $this->db);
	}
  public static
    function sGetFilePath($path)
    {
			new trace("sGetFilePath called!");
    }
}		
?>
