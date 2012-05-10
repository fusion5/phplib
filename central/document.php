<?
class document extends resource 
{
	protected
		$docTitle = '';
	protected
		$docDescription = '';
	protected
		$docKeywords = '';
	protected
		$docType = 'xhtml';
	protected
		$includejs = array();
	public
	function __construct($path, $user, $central, $db) 
	{
		global $globalIncludeJS;
		if (isset($globalIncludeJS))
			$this->includejs = array_merge($globalIncludeJS, $this->includejs);
		parent::__construct($path, &$user, $central, $db);
		if (isset($db) && $db->hasObject('doc') && ($db->doc instanceof idocumentinfo))
		{
			$db->doc->selectDocumentInfo($path, $central->state->getLanguage());
			if ($db->doc->selected())
			{
				$this->docTitle = $db->doc->getDocTitle();
				$this->docDescription = $db->doc->getDocDescription();
				$this->docKeywords = $db->doc->getDocKeywords();
			}
		}
	}
	public		
		function setType($p) 
		{
			$this->docType = $p;
		}
	public		
		function setTitle($p) 
		{
			if ($p)
				$this->docTitle = $p;
			else
				$this->docTitle = 'Untitled Page !';
		}
	protected
		function perform() 
		{
			$this->prePerform();
			$headerFile = './docres/headers/'.$this->docType.'_header.php';
			$footerFile = './docres/footers/'.$this->docType.'_footer.php';
			if (!is_file($headerFile))
				throw new httpException(404, 'Please create a header file for ' . $this->docType . ' documents');
			if (!is_file($footerFile))
				throw new httpException(404, 'Please create a footer file for ' . $this->docType . ' documents');
			$this->display_header($headerFile);
			$this->display();
			$this->display_footer($footerFile);
		}
	protected
		function prePerform() 
		{
		} 
	protected
		function display_header($file) // Afiseaza headerul documentului	
		{
			include($file);
		}
	protected 	
		function display_footer($file) // Afiseaza footerul documentului	
		{
			include($file);
		}
	protected // Returneaza o instanta a documentului din $pathString
		function getDocumentInstance($pathString, $docType = null, $docTitle = '')
		{
			return parent::getDocumentInstance($pathString, first($docType, $this->docType), $docTitle);
		}		
}		
?>
