<?
	function lang($forceRefresh = false)
	{
		throw new errors('Lang called! Deprecated.');
	}
	function glang($forceRefresh = false)
	{
		trace('gLang called! Deprecated.');
	}
	function openLanguage($file)
	{
		throw new errors('openLanguage Deprecated.');
	}
	function x($identif, $edit = null, $referer = null)
	{
		trace('x deprecated');
	}
	function g($identif)
	{
		$dummy_db = null;
		$central = central($dummy_db, false);
		if (isset($central) && isset($central->db) && $central->db->hasObject('content') && ($central->db->content instanceof icontent))
		{
			$lang = $central->state->getLanguage();
			$docPath = $central->state->getCurrentPath();
			$content = $central->db->content;
			$content->selectContent($identif, $lang, $docPath);
			if (!$content->selected())
			{
				$backtrace = debug_backtrace();
				$file = $backtrace[0]['file'];
				if(preg_match('|.*body.php$|', $file))
					$insertPath = $docPath;
				else
					$insertPath = null;
				$content->addTranslation($identif, $lang, $translation = null, $insertPath);
			}
			return first($content->getTranslation(), $identif);
		}
		return $identif;
	} 	
	function gf($phrase)
	{
		$phrase = g($phrase);
		$params = func_get_args();
		array_shift($params);
		array_unshift($params, $phrase);
		return call_user_func_array("sprintf", $params);
	}
	if (!function_exists('DISPLAY_SPECIFIC_EDIT')) 
	{
		function DISPLAY_SPECIFIC_EDIT()
		{
			if (isset($_SESSION['DISPLAY_SPECIFIC_EDIT']))
			return $_SESSION['DISPLAY_SPECIFIC_EDIT'];
		}
	}
	if (!function_exists('DISPLAY_GENERAL_EDIT'))
	{
   	function DISPLAY_GENERAL_EDIT()
		{
			if (isset($_SESSION['DISPLAY_GENERAL_EDIT']))
			return $_SESSION['DISPLAY_GENERAL_EDIT'];
		}
	} 	
	if (!function_exists("INPLACE_EDIT"))
	{
		function INPLACE_EDIT()
		{
			return false;
		}
	}	
?>
