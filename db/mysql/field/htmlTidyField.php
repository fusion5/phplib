<?
define("TAG_WHITELIST",0);
define("TAG_BLACKLIST",1);
define("ATTRIB_WHITELIST",0);
define("ATTRIB_BLACKLIST",1); 
class htmlTidyField extends myTextField
{
	private
		$whitelist = '<table><tbody><strike><thead><tfoot><tr><th><td><colgroup><col><p><br><hr><blockquote><b><i><u><sub><sup><strong><em><tt><var><code><xmp><cite><pre><abbr><acronym><address><samp><fieldset><legend><a><img><h1><h2><h3><h4><h4><h5><h6><ul><ol><li><dl><dt>';
	public
		function setValue($html)
		{
			$html = str_replace('&nbsp;', '', $html);
			$html = strip_tags($html, $this->whitelist);
			$config = array (
				'input-encoding' => 'utf-8',
				'output-encoding' => 'raw',
				'char-encoding' => 'utf-8',
				'output-xhtml' => true,
				'drop-font-tags' => false, // Don't drop the font tags because they replace tags like <u> with <span>!
				'doctype' => 'omit',
			 	'word-2000' => true, //Removes all proprietary data when an MS Word document has been saved as HTML
			 	'clean' => false, // Don't clean because it replaces <strike> with styles!
			 	'drop-proprietary-attributes' =>true, //Removes all attributes that are not part of a web standard
			 	'drop-empty-paras' => true, //Removes <P> tags that contain no data
			 	'hide-comments' => false, //Strips all comments
				'merge-divs' => true,
				'show-body-only' => true,
				'logical-emphasis' => true,
				'merge-spans' => true,
				'preserve-entities' => true
			); //Sets the number of characters allowed before a line is soft-wrapped 
			if (function_exists('tidy_repair_string'))
				$html = tidy_repair_string($html, $config);
			parent::setValue($html);
		}
}
?>
