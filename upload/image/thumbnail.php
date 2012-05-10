<?
class thumbnail extends image
{
	private
		$thumb_width;
	private
		$thumb_height;
	private
		$originalFile;
	public
		function __construct($imgfile, $thumb_width = 160, $thumb_height = 120, $attributes = null)
		{
			$this->setRepo(image::getRepository());
			$this->setOriginalFile($imgfile);
			$this->thumb_width = $thumb_width;
			$this->thumb_height = $thumb_height;			
			$this->setImageFile($this->getTransformed($this->getOriginalFile()));
			$attributes["src"] = $this->getRepo()->getURL($this->getImageFile());
			if (!isset($attributes['no_output']))
				new img($attributes);
		}
	public
		function setOriginalFile($file)
		{
			$this->originalFile = $file;
		}
	public
		function getOriginalFile()
		{
			return $this->originalFile;
		}
	private
		function generateThumbnail()
		{
			$pathinfo = pathinfo($this->getOriginalFile());
			$original_src = $this->getRepo()->getHashedFile($this->getOriginalFile());
			$thumbdir = dirname($this->getOriginalFile()) . '/_thumbnails/' . $this->thumb_width . 'by' . $this->thumb_height . '/';
			$new_src = $this->getRepo()->getHashedFile($thumbdir . basename($this->getOriginalFile()));
			if (is_file($original_src))
			{
				if (is_file($new_src) && (filemtime($new_src) >= filemtime($original_src))) // Thumbnail-ul existent este mai nou decat originalul
					return $new_src; // Nu mai regeneram thumbnail-ul
			}
			else
			{
				$original_src = $this->getRepo()->applyFilenameHash($this->getOriginalFile());
				if (!is_file($original_src))
					throw new errors(gf('The image file (%s) does not exist!', $original_src));
			}
			$original_image = false;
			if (isset($pathinfo['extension']))
				$original_image = image::getGdImageCreateFrom($original_src, $pathinfo['extension']);
			if ($original_image == false) 
				throw new errors("Invalid image read! " . $this->getImageFile());
			$width = imagesx($original_image);
			$height = imagesy($original_image);
			$scale = min($this->thumb_width/$width, $this->thumb_height/$height);
			if ($scale < 1)
			{
				$new_width = floor($scale * $width);
				$new_height = floor($scale * $height);
				$resized_image = imagecreatetruecolor($new_width, $new_height);
				imagecopyresampled($resized_image, $original_image, 0, 0, 0, 0,
					$new_width, $new_height, $width, $height);
				$this->applyfilter($resized_image, $new_width, $new_height);
				imagedestroy($original_image);
				$pathinfo = pathinfo($new_src);
				assertDir($pathinfo['dirname'], 0777);
				$success = false;
				$success = self::gdImage($new_src, $pathinfo['extension'], $resized_image, 90);
				if ($success)
					chmod($new_src, 0777);
				else
					throw new errors('Could not resample image at ' . $new_src . '! ' . $resized_image);
			}
			else
			{
				$new_src = $this->getRepo()->getHashedFile($this->getOriginalFile());
				$resized_image = $original_image;
			}
			return $new_src;			
		}
	private
		function getTransformed() 
		{
			$tempsrc = null;
			try 
			{
				$tempsrc = $this->generateThumbnail();
			}		
			catch (errors $e)
			{
				$tempsrc = $this->generateErrorImage();
			}
			if ($tempsrc) return $this->getRepo()->removeRelPath($tempsrc);
		}
	public
		function generateErrorImage() 
		{
			$thumbfile = $this->getRepo()->getFile('_thumbnails/' . $this->thumb_width . 'by' . $this->thumb_height . '.jpg');
			if (!file_exists($thumbfile))
			{
				$error_image = imagecreate($this->thumb_width, $this->thumb_height);
				imagecolorallocate($error_image,200,200,200);
				$c = imagecolorallocate($error_image,255,255,255);
				imageline($error_image,0,0,$this->thumb_width,$this->thumb_height,$c);
				imageline($error_image,$this->thumb_width,0,0,$this->thumb_height,$c);
				assertDir(dirname($thumbfile), 0777);
				imagejpeg($error_image, $thumbfile, 90);
				chmod($thumbfile, 0777);
			}
			return $thumbfile;	
		}
	public
	function applyfilter($img, $width, $height) {
	}
	public
	function _xhtml() {
		print 'metoda _xhtml nu mai e folosita!';
	}
}
?>
