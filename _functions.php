<?
	include_once('_constants.php');
	if (USE_DB_LANG === true)
	{
		include_once('localize/_constants.php');
		include_once('localize/_functions.php');
	}
	else
	{
		include_once('lang/_constants.php');
		include_once('lang/_functions.php');
	}
	include_once('xhtml/_shortcuts.php');
	function first() // Returneaza primul element din argumente cu valoare sau null daca nici unul nu are
	{
		$args = func_get_args();
		foreach($args as $arg)
			if (isset($arg) && ($arg || $arg === "0"))
				return $arg;
		return null;
	}
	function strtonumber($str, $dec_point=null, $thousands_sep=null)
	{
		if( is_null($dec_point) || is_null($thousands_sep) ) 
		{
			$locale = localeconv();
			if (is_null($dec_point) ) 
				$dec_point = $locale['decimal_point'];
			if (is_null($thousands_sep)) 
				$thousands_sep = $locale['thousands_sep'];
		}
		$number = str_replace($dec_point, '.', str_replace($thousands_sep, '', $str));
		if (is_numeric($number))
			return (float)$number;
		return false;
	}
	function is_reference($var1, $var2)
	{
		if ($var2 !== $var1)
			return false;
		$return = true;
		$oldValue = $var1;
		if ($var1 === 1)
			$var1 = 2;
		else
			$var1 = 1;
		if ($var2 !== $var1)
			$return = false;
		$var1 = $oldValue;
		return $return;
	}
	function replace($string, array $values)
	{
		if (count($values))
			foreach($values as $key => $value)
				if (is_string($value))
					$string = str_replace('{'.$key.'}', $value, $string);
		return $string;
	}
	function equals($variable, $value)
	{
		if (isset($variable))
			return $variable == $value;
		else
			return false;
	}
	function append(&$variable, $string)
	{
		if (isset($variable))
			$variable .= $string;
		else
			$variable = $string;
	}
	function trace($arg)
	{
		new trace($arg);
	}
	function clearQuotes(&$a)
	{
		if (count($a))
			foreach ($a as $item => &$value)
				if (is_string($value)) $value = stripslashes($value);
				else if (is_array($value)) clearQuotes($value);
	}
	function randomPassword($totalChar = 7)
	{
		$salt = "123456789ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz";  
		srand((double)microtime()*1000000); 
		$password=""; 
		for ($i=0;$i<$totalChar;$i++)  
			$password = $password . substr ($salt, rand() % strlen($salt), 1);
		return $password;
	}
	include_once(CORE_PHPLIB . 'autoloader.php');
	function __autoload($class_name) 
	{
		if ($class_name != 'parent')
		{
			static $ldr;
			if (!$ldr)
			{
				$dirs = split(PATH_SEPARATOR, get_include_path());
				array_shift($dirs);
				$ldr = new autoloader($dirs);
				if (!assertDir(CACHE_DIR))
					exit('Could not create cache directory ' . CACHE_DIR .'. Please create it manually and set the appropriate permissions for it!');
				$ldr->setCacheFilename(CACHE_DIR . 'autoload_cache.php');
				$ldr->setClassFileEndings(array('cls.php', 'php'));
				$ldr->setIgnoreHiddenFiles(true);
				if (DEBUG_MODE)
					$ldr->createCache();
				global $smartloader;
				$smartloader = $ldr;
			}
			if (!$ldr->loadClass($class_name)) 
			{
				if (DEBUG_MODE === TRUE)
					print "autoloader: Cannot load the class called " . $class_name;
			}
		}
	}
	function is_includable($file)
	{
		$r = fopen($file, 'r', true);
		if ($r !== false) 
		{
			fclose($r);
			return true;
		}
		return false;
	}
	function classdef_exists($class_name, $library = null)
	{
		return class_exists($class_name, false) || isset($GLOBALS['smartloader_classes'][strtolower($class_name)]);
	}
	if (!function_exists("DEBUG_MODE"))
	{
		function DEBUG_MODE()
		{
			return (DEBUG_MODE === true);
		}
	}
	function result()
	{
		return central()->state->getLastActionResult();		 
	}
	if ( !function_exists('htmlspecialchars_decode') )
	{
		function htmlspecialchars_decode($text)
		{
				return strtr($text, array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
		}
	}
	function assertDir($dir, $requiredPermissions = 0777) // Primeste argument fara basedir!!!
	{
		if (is_dir($dir))
		{
			$currentPermissions = fileperms($dir) & 511;
			if ($currentPermissions !== $requiredPermissions)
			{
				@chmod($dir, $requiredPermissions);
				clearstatcache();
			}
			return true;
		}
		else
		{
			return (mkdir($dir, $requiredPermissions, true));
		}
	}
	function validateEmail($email) 
	{
		$email = trim($email);
		if ($email == '')
			throw new errors(g('You must provide an e-mail address'));
		if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) 
		{
			throw new errors(g("Wrong e-mail format"));
			return;
		}
		$email_array = explode("@", $email);
		$local_array = explode(".", $email_array[0]);
		for ($i = 0; $i < sizeof($local_array); $i++) 
			if (!ereg("^(([A-Za-z0-9!#$%&#038;'*+/=?^_`{|}~-][A-Za-z0-9!#$%&#038;'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) 
			{
				throw new errors(g("Wrong e-mail format"));
				return;
			}
		if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) 
		{ 
			$domain_array = explode(".", $email_array[1]);
			if (sizeof($domain_array) < 2) 
			{
				throw new errors(g("Wrong e-mail domain"));
				return;
			}
			for ($i = 0; $i < sizeof($domain_array); $i++) 
				if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) 
				{
					throw new errors(g("Wrong e-mail domain"));
					return;
				}
		}
	} 	
	function phpbb_hash($password)
	{
		$itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		$random_state = unique_id();
		$random = '';
		$count = 6;
		if (($fh = @fopen('/dev/urandom', 'rb')))
		{
			$random = fread($fh, $count);
			fclose($fh);
		}
		if (strlen($random) < $count)
		{
			$random = '';
			for ($i = 0; $i < $count; $i += 16)
			{
				$random_state = md5(unique_id() . $random_state);
				$random .= pack('H*', md5($random_state));
			}
			$random = substr($random, 0, $count);
		}
		$hash = _hash_crypt_private($password, _hash_gensalt_private($random, $itoa64), $itoa64);
		if (strlen($hash) == 34)
		{
			return $hash;
		}
		return md5($password);
	}
	function phpbb_check_hash($password, $hash)
	{
		$itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		if (strlen($hash) == 34)
		{
			return (_hash_crypt_private($password, $hash, $itoa64) === $hash) ? true : false;
		}
		return (md5($password) === $hash) ? true : false;
	}
	function _hash_gensalt_private($input, &$itoa64, $iteration_count_log2 = 6)
	{
		if ($iteration_count_log2 < 4 || $iteration_count_log2 > 31)
		{
			$iteration_count_log2 = 8;
		}
		$output = '$H$';
		$output .= $itoa64[min($iteration_count_log2 + ((PHP_VERSION >= 5) ? 5 : 3), 30)];
		$output .= _hash_encode64($input, 6, $itoa64);
		return $output;
	}
	function _hash_encode64($input, $count, &$itoa64)
	{
		$output = '';
		$i = 0;
		do
		{
			$value = ord($input[$i++]);
			$output .= $itoa64[$value & 0x3f];
			if ($i < $count)
			{
				$value |= ord($input[$i]) << 8;
			}
			$output .= $itoa64[($value >> 6) & 0x3f];
			if ($i++ >= $count)
			{
				break;
			}
			if ($i < $count)
			{
				$value |= ord($input[$i]) << 16;
			}
			$output .= $itoa64[($value >> 12) & 0x3f];
			if ($i++ >= $count)
			{
				break;
			}
			$output .= $itoa64[($value >> 18) & 0x3f];
		}
		while ($i < $count);
		return $output;
	}
	function _hash_crypt_private($password, $setting, &$itoa64)
	{
		$output = '*';
		if (substr($setting, 0, 3) != '$H$')
		{
			return $output;
		}
		$count_log2 = strpos($itoa64, $setting[3]);
		if ($count_log2 < 7 || $count_log2 > 30)
		{
			return $output;
		}
		$count = 1 << $count_log2;
		$salt = substr($setting, 4, 8);
		if (strlen($salt) != 8)
		{
			return $output;
		}
		if (PHP_VERSION >= 5)
		{
			$hash = md5($salt . $password, true);
			do
			{
				$hash = md5($hash . $password, true);
			}
			while (--$count);
		}
		else
		{
			$hash = pack('H*', md5($salt . $password));
			do
			{
				$hash = pack('H*', md5($hash . $password));
			}
			while (--$count);
		}
		$output = substr($setting, 0, 12);
		$output .= _hash_encode64($hash, 16, $itoa64);
		return $output;
	}
	function unique_id($extra = 'c')
	{
		static $dss_seeded = false;
		global $config;
		if (!isset($config['rand_seed']))
			$config['rand_seed'] = 'jhfd2390rtu2j';
		if (!isset($config['rand_seed_last_update']))
			$config['rand_seed_last_update'] = time();
		$val = $config['rand_seed'] . microtime();
		$val = md5($val);
		$config['rand_seed'] = md5($config['rand_seed'] . $val . $extra);
		if ($dss_seeded !== true && ($config['rand_seed_last_update'] < time() - rand(1,10)))
		{
			set_config('rand_seed', $config['rand_seed'], true);
			set_config('rand_seed_last_update', time(), true);
			$dss_seeded = true;
		}
		return substr($val, 4, 16);
	}
?>
