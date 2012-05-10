<?
class docinfo 
{
	protected static
		$instances = array();
	private	
		$docTree;
	private
		$dirReal;
	private
		$dirAliases;
	public
		function __construct($documentFile) 
		{	
			$this->docTree = new DOMDocument();
			$this->docTree->preserveWhiteSpace = false;
			if (is_readable($documentFile))
				$this->docTree->load($documentFile);
			else
				throw new errors(sprintf('The document paths file %s was not found!', $documentFile));
		}
	public static 
		function getInstance($documentFile = 'doc.xml') 
		{
			if (!isset(self::$instances[$documentFile])) self::$instances[$documentFile] = new self($documentFile);
			return self::$instances[$documentFile];
		}
	public
		function getDocTree()
		{
			return $this->docTree;
		}
	private
		function getPathAttributes(path $path) 
		{
			$xpath = new DOMXPath($this->docTree);
			if ($path->isRoot())
				$path = '/doc';
			else
				$path = '/doc' . $path->getPath();
			$currentPage = $xpath->query($path);
			if ($currentPage->length > 1)
				throw new errors('Ambiguity error: multiple match in doc.xml for entry ' . $path . ' ' . $currentPage->length . ' entries found!');
			if ($currentPage->length < 1)
				throw new errors('No match found for ' . $path . ' in doc.xml');
			return $currentPage->item(0);
		}
	private
		function getNodeFromAlias($node, $language)
		{
			$found = false;
			if ((count($this->dirAliases) > 0) && ($node->childNodes != NULL))
			{
				foreach ($node->childNodes as $docItem) 
				{
					if (count($this->dirAliases) > 0) 
					{
						$hasAttributes = $docItem->hasAttributes();
						if (
							($hasAttributes) && ($docItem->getAttribute('alias_' . $language) == $this->dirAliases[0])	|| 
							($docItem->nodeName == $this->dirAliases[0])
						) 
						{
							$found = true;
							$this->dirReal[] = $docItem->nodeName;
							array_shift($this->dirAliases);
							return $this->getNodeFromAlias($docItem, $language);
						}
					}
				}
			}
			else
				$found = true;
			return $found;
		}
	private
		function getAliasFromPath($node, $language) 
		{
			if ((count($this->dirReal) > 0) && ($node->childNodes != NULL)) 			
				foreach ($node->childNodes as $docItem) 
				{
					if (count($this->dirReal) > 0) 
					{
						$hasAttributes = $docItem->hasAttributes();
						if (
							($hasAttributes) && ($docItem->getAttribute('alias_' . $language) == $this->dirReal[0])	|| 
							($docItem->nodeName == $this->dirReal[0])
						) 
						{
								$newAlias = $docItem->getAttribute('alias_' . $language);
							if (!$newAlias)
								$newAlias = $docItem->nodeName;
							$this->dirAliases[] = $newAlias;
							array_shift($this->dirReal);
							$this->getAliasFromPath($docItem, $language);
						}
					}
				}
		}
	public
		function getFullPathFromAliases($aliases, $language) 
		{
			if (!$aliases->isRoot())
			{
				$this->dirAliases = $aliases->getComponents();
				$this->dirReal = array();
				if ($this->getNodeFromAlias($this->docTree->firstChild, $language))
					return new path(join('/', $this->dirReal));
			}
			else
				return $aliases;
			return false;
		}
	public
		function getAliasesFromFullPath($path, $language) 
		{
			if (!$path->isRoot())
			{
				$this->dirReal = $path->getComponents();
				$this->dirAliases = array();
				$this->getAliasFromPath($this->docTree->firstChild, $language);
				return new path(join('/', $this->dirAliases));
			}
			return $path;
		}		
	public
		function isPublic(path $path) 
		{
			return $this->getPathAttributes($path, state()->getLanguage())->getAttribute('public') == 'true';
		}
}
?>
