<?
class filerepository
{
	private
		$relPath = '';
	public
		function __construct($relPath)
		{
			$this->relPath = $relPath;
			assertDir($this->relPath, 0777);
		}
	public
		function upload($tmpFileName, $destinationFileName, $destinationFolder)
		{
			if (assertDir($this->getFile($destinationFolder), 0777))
			{
				$file = $this->getHashedFile($destinationFolder . $destinationFileName);
				if (!is_file($tmpFileName))
					throw new errors("The temporary file $tmpFileName is not a file!");
				if (copy($tmpFileName, $file))
				{
					if (is_file($file) && chmod($file, 0777))
						return true;
					else
						throw new errors("Cannot change the mod of the file! You do not have write permissions set for files by default?");
				}
				else
					throw new errors("Cannot move uploaded file $tmpFileName to $file");
			}
			else
				throw new errors("Cannot create directory for the upload of this file! No write permissions on " . $this->getFile($destinationFolder));
		}
	public
		function getFile($file)
		{
			return $this->relPath . $this->filterFileName($file);
		}
	private
		function filterFileName($file)
		{
			$file = htmlspecialchars_decode($file);
			$info = pathinfo($file);
			if (isset($info['extension']) && preg_match('/(php|pl|py|cgi|plx|ppl|perl|asp|js|vbs)$/i', $info['extension']))
				$file = $info['dirname'] . '/' . $info['filename'] . '.txt';
			return $file;
		}
	public
		function getURL($file)
		{
			return ABS_URL . $this->getFile($file);
		}
	public
		function getHashedFile($file)
		{
			return $this->relPath . $this->hashFilename($file);
		}
	public
		function getHashedURL($file)
		{
			return ABS_URL . $this->getHashedFile($file);
		}
	public
		function removeRelPath($file)
		{
			if (strpos($file, $this->relPath) === 0)
				return substr($file, strlen($this->relPath));
			return false;
		}
	private
		function hashFilename($fileName)
		{
			$pathinfo = pathinfo($this->filterFileName($fileName));
			$return = '';
			if (isset($pathinfo['dirname']))
				$return .= $pathinfo['dirname'] . '/';
			$return .= md5($pathinfo['basename']);
			if (isset($pathinfo['extension']))
				$return .= '.' . strtolower($pathinfo['extension']);
			return $return;
		}
	public
		function applyFilenameHash($file)
		{
			$filePath = $this->getFile($file);
			$hashedFilePath = $this->getHashedFile($file);
			if (is_file($filePath) && is_readable($filePath))
			{
				if (copy($filePath, $hashedFilePath))
				{
					unlink ($filePath);
					return $hashedFilePath;
				}
			}
			return false;
		}
	public
		function is_file($file)
		{
			return is_file($this->getHashedFile($file));
		}
	public
		function unlink($file)
		{
			return unlink($this->getHashedFile($file));
		}
}
?>
