<?
class general_file implements iuploadable
{
	private
		$fileRepository;
	protected static
		$generalFileRepositoryInstance;
	public
		function __construct($generalFile, $attributes = null)
		{
			$path_parts = pathinfo($generalFile);
			$file = self::getRepository()->getHashedFile($generalFile);
			if (!isset($attributes['caption']))
				$attributes['caption'] = $path_parts['basename'];
			if (is_file($file))
			{
				append(&$attributes['class'], ' file ' . $path_parts['extension']);
				$state = null;
				if (isset($attributes['nohash']))
					$attributes['href'] = self::getRepository()->getURL($generalFile);
				else
					$attributes['href'] = self::getRepository()->getHashedURL($generalFile);
				new a($state, $attributes);
			}
			else
			{
				print '<span title="Missing file from server: '.$generalFile.'"><strong><strike>'.$attributes['caption'].'</strike></strong> (!)</span>';
			}
		}
	public static
		function getRepository()
		{
			if (!defined('DOWNLOADS_PATH'))
				define('DOWNLOADS_PATH', './downloads/');
			if (self::$generalFileRepositoryInstance == null)
				self::$generalFileRepositoryInstance = new filerepository(DOWNLOADS_PATH);
			return self::$generalFileRepositoryInstance;
		}
	public static 
		function checkFile($filename, $extension)
		{
		}
	public static
		function renderPreview($filename)
		{
			new general_file($filename, array('nohash' => true));
		}
}
?>
