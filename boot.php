<?
	if(!ini_get('session.auto_start'))
		session_start();
	if (!defined('LOCAL_PHPLIB'))
		exit('LOCAL_PHPLIB constant not defined!');
	if (!defined('CORE_PHPLIB'))
		exit('CORE_PHPLIB constant not defined!');
	if (!defined('LIB_EXTENSION'))
		define('LIB_EXTENSION', '.php');	
	if (!defined('DEBUG_MODE'))
		define('DEBUG_MODE', false);
	if (!defined('USE_EXECUTIVE'))
		define('USE_EXECUTIVE', true);
	if (!defined('USE_DB_CLASS'))
		define('USE_DB_CLASS', defined('DB_CLASS'));
	if (!defined('CRLF'))
		define('CRLF', "\r\n", true);
	if (!defined('USER_CLASS')) 
		define('USER_CLASS', 'user');	
	if (defined('MYSQL_HOST'))
		exit('MYSQL_HOST deprecated! Replace with DB_HOST');
	if (defined('MYSQL_USERNAME'))
		exit('MYSQL_USERNAME deprecated! Replace with DB_USERNAME');
	if (defined('MYSQL_PASSWORD'))
		exit('MYSQL_PASSWORD deprecated! Replace with DB_PASSWORD');
	if (defined('MYSQL_SCHEMATA'))
		exit('MYSQL_SCHEMATA deprecated! Replace with DB_SCHEMATA');
	define('DB_EMPTYSTRING', '$EMPTY$');
	if (!defined('CACHE_DIR'))
		define('CACHE_DIR', './.cache/');
	if (!defined('USE_DB_LANG'))
		define('USE_DB_LANG', false);
	if (!defined('TMP_DIR'))
		define('TMP_DIR', '.tmp/');
	if (DEBUG_MODE)
	{
		ini_set('display_startup_errors', true);
		ini_set('display_errors', true);
		error_reporting(E_ALL | E_STRICT);
	}
	else
	{
		ini_set('display_errors', false);
	}
	global $locations;
	if (LOCAL_PHPLIB != CORE_PHPLIB)
		$locations = array(LOCAL_PHPLIB, CORE_PHPLIB);
	else
		$locations = array(CORE_PHPLIB); 	
	if (!include_once(CORE_PHPLIB . '_constants.php'))
		exit('Couldn\'t find _constants.php in the CORE_PHPLIB');			
	if (!include_once(CORE_PHPLIB . '_functions.php'))
		exit('Couldn\'t find _functions.php in the CORE_PHPLIB');
	$script_name = first(&$_SERVER['ORIG_SCRIPT_NAME'], &$_SERVER['SCRIPT_NAME']);
	$path = pathinfo($script_name);
	if ($path['dirname'] == '/')
		$dir = '/';
	else
		$dir = $path['dirname'] . '/';
	define('ABS_URL', 'http://' . $_SERVER['HTTP_HOST'] . $dir); 	
	define('REQUEST', substr($_SERVER['REQUEST_URI'], strlen($dir)));
	set_magic_quotes_runtime(0);
	if (get_magic_quotes_gpc())
	{
		clearQuotes($_POST);
		clearQuotes($_GET);
	}
	if (!defined('DB_CLASS')) define('DB_CLASS', 'mysql');	
	function state($doInstance = true)
	{
		static $state;
		if (!isset($state) && $doInstance)
		{
			$state = new state();
			if (isset($_SESSION['target']))
			{
				$state->deserialize($_SESSION['target']);
				unset ($_SESSION['target']);
			}
			else
			{
				$state->initialize(REQUEST, ABS_URL, $_POST, $_GET);
			}
		}
		return $state;
	}
	function central(&$db = null, $doInstance = true)
	{
		static $central;
		if (!isset($central) && $doInstance)
		{
			if ($db)
				$central = new executive($db->getUser(), $db);
			else
				$central = new executive(null, null);
		}	
		return $central;
	}
	$db = null;
	try
	{
		if (USE_DB_CLASS)
		{
			$dbClassName = DB_CLASS;
			$db = new $dbClassName(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_SCHEMATA); 
		}
		if (USE_EXECUTIVE)
			central($db)->execute(state());
		if (class_exists('smtp', false))
			smtp::destroy();
		if (isset($db) && isset($db->event))
			$db->event->cycle();
	}
	catch (errors $e) 
	{
		if (!headers_sent())
			header ('HTTP/1.0 500 Internal Server Error');
		print '<h1>Application error</h1>';
		if (DEBUG_MODE())
		{
			print '<pre>';
				print $e->getMessage();
			print '</pre>';
			print '<pre>';
				print $e;
			print '</pre>';
		}
		exit();
	}
	catch (httpException $httpe)
	{
		if (!headers_sent())
			header($httpe->getHeader());
		print '<h1>HTTP Error - ' . $httpe->getCode() . '</h1>';
		if (DEBUG_MODE())
		{
			print '<p><strong>'.$httpe->getMessage().'</strong></p>';
			print '<pre>';
				print $httpe;
			print '</pre>';
		}
	}
?>
