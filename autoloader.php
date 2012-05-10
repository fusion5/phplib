<?php
	class autoloader
	{
		private $classDirectories = array();
		private $cacheFilename = 'autoload_cache.php';
		private $classIndex = array();
		private $classFileEndings = array();
		private $ignoreHiddenFiles = true;
		public function __construct(array $dirs) 
		{
			if (is_array($dirs) && count($dirs))
				foreach($dirs as $dir)
					$this->addDir($dir);
		}
		public function setCacheFilename($cacheFilename) {
			$this->cacheFilename = $cacheFilename;
		}
		public function setClassFileEndings($classFileEndings) {
			$this->classFileEndings = $classFileEndings;
		}
		public function setIgnoreHiddenFiles($value) 
		{
			$this->ignoreHiddenFiles = $value;
		}
		public function addDir($directory_path) 
		{
			$this->classDirectories[] = $directory_path;
		}
		public function loadClass($class_name, $retry = false) 
		{
			$class_name = strtolower($class_name);
			if($retry || !is_readable($this->cacheFilename)) 
			{
				$this->createCache();
			}
			if(!include_once($this->cacheFilename)) 
			{
				trigger_error("smartloader: Cannot include cache file from '".$this->cacheFilename."'", E_USER_ERROR);
			}
			$file = &$GLOBALS['smartloader_classes'][$class_name];
			if (isset($file))
				if (include($file))
					return true;
			if($retry) 
				return false;
			else 
				return $this->loadClass($class_name, true);
		}
		public function createCache() 
		{
			foreach($this->classDirectories as $dir) 
			{
				$this->parseDir($dir);
			}
			$cache_content = "<?\n\t// this is a automatically generated cache file.\n"
				."\t// it serves as 'class name' / 'class file' association index for the smartloader\n";
			foreach ($this->classIndex as $class_name => $class_file)
			{
				$cache_content .= "\$GLOBALS['smartloader_classes']['$class_name'] = '$class_file';\n";
			}
			$cache_content .= "?>";
			{
				file_put_contents($this->cacheFilename, $cache_content);
				@chmod($this->cacheFilename, 0777);
			}
		}
		private function parseDir($directory_path, $relativePath = '') 
		{
			$directory_path = $directory_path;
			if(is_dir($directory_path)) 
			{
				if($dh = opendir($directory_path)) 
				{
					while(($file = readdir($dh)) !== false) 
					{
						$file_path = $directory_path . '/' . $file;
						if(!$this->ignoreHiddenFiles || $file{0} != '.') 
						{
							if ((filetype($file_path) == 'file') && is_readable($file_path))
							{
								$found = false;
								foreach($this->classFileEndings as $extension)
								{
									$regexp = '|.*\.('.addcslashes($extension, '\.').')$|';
									preg_match ($regexp, $file_path, $matches);
									if (!count($matches))
										continue;
									$filename = strtolower(substr($file, 0, strlen($file) - strlen($extension) - 1));
									if (!isset($this->classIndex[$filename]))
										$this->classIndex[$filename] = $relativePath . $file;
									break;
								}
							}
							else
								if (filetype($file_path) == 'dir' && $file != '.' && $file != '..')
									$this->parseDir($directory_path . '/' . $file, $relativePath . $file . '/');
						}
					}
					return true;
				}
			}
			return false;
		}
	}
?>
