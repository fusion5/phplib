<?
	function lang($forceRefresh = false)
	{
		static $lang;
		$state = state(false);
		if ($state)
			if (!$lang || $forceRefresh) $lang = new langmanager($state->getLanguage());
		return $lang;
	}
	function glang($forceRefresh = false)
	{
		static $glang;
	  if (!$glang || $forceRefresh) 
			$glang = new lang(resource::getLanguageFilePath('_general'));
		return $glang;
	}
	function openLanguage($file)
	{
		$lang = lang();
		if ($lang)
			$lang->open($file);
	}
	function x($identif, $edit = null, $referer = null)
	{
		if ($edit === null) $edit = DISPLAY_SPECIFIC_EDIT();
		$phrase = lang()->getPhrase($identif);
		if ($edit)
			langmanager::editLink($phrase, $referer); // Afisam dispozitiv de editare.
		return first($phrase->getValue(), $identif);
	}
	function g($identif, $edit = null)
	{
		if ($edit === null) $edit = DISPLAY_GENERAL_EDIT();
		$phrase = glang()->getPhrase($identif);
		if ($edit)
			langmanager::editLink($phrase, '_general');
		return first($phrase->getValue(), $identif);
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
