<?
class docgen extends docinfo 
{
	public
	function enumNodes($docNode, $parents, $url) 
	{ 	
		if ($docNode->hasChildNodes()) {		
		foreach ($docNode->childNodes as $item)
		if ($item->nodeType != XML_TEXT_NODE)		
 		{										
					$tagName = $item->tagName;
					if ($tagName == 'doc') 
					{
						$nodePath = 'doc';
						$nodeUrl = '';
					}
					else
					{
						$nodePath = $parents . '/' . $item->nodeName;
						$nodeUrl = $url . '/' . $item->nodeName;
					}
					$path = new path($nodeUrl);
					if (@mkdir($nodePath)) {
						print "Creat $nodeUrl - " . $path->pathToCamelCase() . "<br />";
						if (!is_file($nodePath . "/body.php")) {							
							$content = $this->templateDocumentContent;
							$content = ereg_replace("%s", $path->pathToCamelCase(), $content);	
							$file = fopen($nodePath . "/body.php","w+");
							fwrite($file, $content);
							fclose($file);
						}
					}
					$this->enumNodes($item, $nodePath, $nodeUrl);
		}
		}
	}	
	public
	function generate() {
		$templateBody = fopen("docres/templates/body.php","r+");			
		$this->templateDocumentContent = fread($templateBody, filesize("docres/templates/body.php"));
		fclose($templateBody);
		var_dump($this->templateDocumentContent);
		$this->enumNodes($this->docTree, '', '');	
	}
}
?>
