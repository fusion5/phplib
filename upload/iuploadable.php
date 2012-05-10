<?
interface iuploadable
{
	public static function getRepository();
	public static function checkFile($fileName, $extension);
	public static function renderPreview($fileName);
}
?>
