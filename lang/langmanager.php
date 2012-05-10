<?
class langmanager extends lang
{
	private	
		$lang = 'ro'; // The current language
	public
		$appended = array();
	private
		$_default = 'ro';	
	public
		$support = array
		(
			'Romana' => 'ro', 
			'English' => 'en',
			'Detusch' => 'de'
		);
	public
		function __construct($lang = null)
		{
			$this->getDefaultLang();
			$lang = first (&$lang, &$_SESSION['lang'], $this->_default);
			$this->setLang ($lang);
			$this->langinstance = new lang();
		}
	public function getDefaultLang()
	{
		if (in_array(DEFAULT_LANG, $this->support))
			$this->_default = DEFAULT_LANG;
		return $this->_default;
	}
	public
		function setLang($lang)
		{
			if ($lang)
			{
				if (in_array($lang, $this->support))
				{
					$_SESSION['lang'] = $lang;
					$this->lang = $lang;
					return true;
				}
				else
				{
					$this->setLang($this->_default);
					return false;
				}
			}
		}
	public
		function getLang()
		{
			return $this->lang;
		}
	public
		function save($lang, $file)
		{
			$l = new lang($file);
			$l->add($lang);
			$l->saveAll($file);
		}
	public static
		function editLink($phrase, $referer = null)
		{
			$value 		= $phrase->getValue();
			$label 		= $phrase->getLabel();
			if ($value) $caption = "Edit";
			else $caption = "Add";
			if ($referer === null) $referer = central()->getPath(); 
			$url = new url
			(
				INPLACE_PATH, 
				"referer=$referer&".
				"file=".central()->state->getPath()."&".
				"label=".urlencode(urlencode($label))."&"
			); 
			if (central()->state->getRequestType() == "xhtml")
				new popup
				(
					$url, 
					array
					(
						"class" => "inplace", 
						"id" => "inplace_".$label,
						"caption" => $caption,
						"disable_edit" => true, // Editing must be disabled otherwise the server gets stuck in a loop
						"refresh" => true
					),
					820, 600
				);
			else
				print $url->getTargetURL() . "\n";
		}
  public 
		function languageEditor($file, $label, $referer) 
		{
			if (USE_CENTRAL)
				if (class_exists('formset'))
				{
					$editform = new formset(central()->param["file"], null, null, array("gostateparamstr" => ""));
					$editform->hidden("referer", $referer);
					$editform->hidden("label", urlencode($label));
					$readlang = new lang($file);
					$phrase = $readlang->getPhrase($label);
					print '<p>';
					$editform->af(array
					(
						"name" => "value",
						"type" => "textarea",
						"value" => first(trim($phrase->getValue()), $label),
						"style" => "width:100%; height:300px;"
					));
					print '</p>';
					$editform->button(g("Save", false), array("name" => "close"));
					unset($editform);
				}
				else
					throw new errors(g("Can't bring up the language editor because the formset class isn't loaded"));
		}  
}
?>
