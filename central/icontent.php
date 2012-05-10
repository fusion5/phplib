<?
interface icontent
{
	public function selectContent($identif, $lang);
	public function getTranslation();
	public function selected();
	public function addTranslation($identif, $lang, $translation = null, path $docPath = null);
}
?>
