<?
class image implements iuploadable
{
	private
		$imgfile;
	private
		$fileRepository;
	const 
		applyHash = true;
	protected static
		$fileRepositoryInstance;
	private
		$attributes = array();
	public
		function __construct($imgfile, $attributes = null)
		{
			$this->setRepo(self::getRepository());
			$this->setImageFile($imgfile);
			$this->attributes = $attributes;
			if (!isset($attributes['no_output']) && !isset($attributes['no_render']))
				$this->render();
		}
	public
		function render()
		{
			$attributes = $this->attributes;
			$attributes["src"] = $this->getRepo()->getHashedURL($this->getImageFile());
			new img($attributes);
		}
	public
		function setImageFile($imgfile) 
		{
			$this->imgfile = $imgfile;	
		}
	public
		function getImageFile()
		{
			return $this->imgfile;
		}
	public
		function setRepo(filerepository $f)
		{
			$this->fileRepository = $f;
		}
	public
		function getRepo()
		{
			return $this->fileRepository;
		}
	protected static
		function getGdImageCreateFrom($file, $extension) 
		{
			$extension = str_ireplace('jpg', 'jpeg', $extension);
			$open_func = 'imagecreatefrom'. $extension;
			if (!function_exists($open_func)) 
				return false;
			return @$open_func($file);
		}
	protected static
		function gdImage($file, $extension, $gd_image, $quality)
		{
			$extension = str_ireplace('jpg', 'jpeg', $extension);
			$open_func = 'image'. $extension;
			if (!function_exists($open_func)) 
				return false;
			return $open_func($gd_image, $file, $quality);
		}
	public static
		function getRepository()
		{
			if (!defined('IMG_PATH'))
				define('IMG_PATH', './images/');
			if (self::$fileRepositoryInstance == null)
				self::$fileRepositoryInstance = new filerepository(IMG_PATH);
			return self::$fileRepositoryInstance;
		}
	public static 
		function checkFile($filename, $extension)
		{
			$gd_original_image = self::getGdImageCreateFrom($filename, $extension);
			if ($gd_original_image === false)
				throw new errors(g('Only valid jpg, png and gif files are allowed.'));
			$success = true;
			if (defined('MAX_UPLOAD_IMAGE_WIDTH') && defined('MAX_UPLOAD_IMAGE_HEIGHT'))
			{
				assert(MAX_UPLOAD_IMAGE_WIDTH > 0);
				assert(MAX_UPLOAD_IMAGE_HEIGHT > 0);
				$width = imagesx($gd_original_image);
				$height = imagesy($gd_original_image);
				$scale = min(MAX_UPLOAD_IMAGE_WIDTH/$width, MAX_UPLOAD_IMAGE_HEIGHT/$height);
				if ($scale < 1)
				{
					$new_width = floor($scale*$width);
					$new_height = floor($scale*$height);
					$gd_resized_image = imagecreatetruecolor($new_width, $new_height);
					imagecopyresampled($gd_resized_image, $gd_original_image, 0, 0, 0, 0,
						$new_width, $new_height, $width, $height);
					$success = self::gdImage($filename, $extension, $gd_resized_image, 90);
					imagedestroy($gd_resized_image);
				}
			}
			imagedestroy($gd_original_image);
			return $success;
		}
	public static
		function renderPreview($filename)
		{
				new image($filename, array(
					'alt' => gf('Preview of %s', $filename)
				));
				return true;
		}
}
?>
