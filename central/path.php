<? 
class path 
{
	private
		$path = '';
	private
		$cachepath = '';
	private
		$components = array();
	public
		function __construct($p = '/') 
		{
			if ($p)
				$this->setPath($p);
		}
	public
		function __toString() 
		{
			return $this->getPath();
		}
	public
		function getFirst()
		{
			$this->populateComponents();
			if (isset($this->components[0]))
				return $this->components[0];
		}
	public
		function remFirst()
		{
			$this->populateComponents();
			array_shift($this->components);
			$this->populatePath();
		}
	public
		function isRoot()
		{
			return $this->path == '';
		}
	public	
		function setPath($p) 
		{
			if (!is_string($p))
				throw new errors('setPath() argument must be a string ' . $p . ' given!');
			if (preg_match('|[a-z0-1\/_]*|', $p) === false)
				throw new errors('The path contains illegal characters !');
			while(ereg('//', $p))
				$p = ereg_replace('//', '/', $p);			
			while (substr($p, 0, 1) == '/') 
				$p = substr($p, 1);
			if (substr($p, strlen($p) - 1) == '/') 
				$p = substr($p, 0, strlen($p) - 1);
			if ($p == 'index')
				$p = '';
			$this->path = $p;
		}
	public
		function hasPublicAccess()
		{
			return docinfo::getInstance()->isPublic($this);
		}
	public
		function getPath() 
		{
			return '/' . $this->path;
		}
	public
		function getSuperiorPath() 
		{
			return $this->getParentPath($this->path);
		}
	public
		function getComponents()
		{
			$this->populateComponents();
			return $this->components;
		}
	public	
		function getParent() 
		{
			return $this->getParentPath($this->path);
		}
	public
		function pathToCamelCase() 
		{
			$tempStr = '';
			$words = split('/', $this->getPath());
			foreach($words as $key => $value) 
			{
				if ($tempStr != '')
					$tempStr .= trim(ucfirst($value));
				else
					$tempStr .= trim($value);
			}
			if ($tempStr != '')	return $tempStr;
			else return ('root');
		}
	public	
		function getLast() 
		{
			$split = array();
			$split = split('/', $this->path);
			return $split[count($split)-1];
		}
	public static 
		function getParentPath($path) 
		{
			if ($path != "")	
			{
				if (substr($path, strlen($path) - 1) == '/') 
					$path = substr($path, 0, strlen($path) - 1);
				$arr = split('/', $path);
				array_pop($arr);
				if (count($arr))
					return join('/', $arr);
				else
					return "";
			}
			else
				return NULL;
		}
	public
		function getComponentsCount()
		{
			$this->populateComponents();
			return count($this->components);
		}
	private
		function populateComponents()
		{
			if ($this->cachepath != $this->path)
			{
				$this->components = split('/', $this->path);
				$this->cachepath = $this->path;
			}
		}
	private
		function populatePath()
		{
			$this->path = join($this->components, '/');
		}
}
?>
