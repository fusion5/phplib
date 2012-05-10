<?
class arrayMapper implements Iterator
{
	private
		$reference;
	private
		$path = '';
	private
		$children;
	public
		function __construct($array, $path = '')
		{
			if (!is_array($array))
				throw new errors('The mapper constructor first parameter must be an array!');
			$this->path = $path;
			$this->mapArray (&$array);
		}
	private
		function mapArray($array)
		{
			$this->reference = &$array;
			$this->children = $array;
			if (is_array($this->reference))
				foreach ($this->reference as $key => &$value)
				{
					if ($this->path)
						$subpath = $this->path . '[' . $key . ']';
					else
						$subpath = $key;
					if (is_array($value))
						$this->children[$key] = new arrayMapper (&$value, $subpath);
					else
						$this->children[$key] = new valueMapper (&$value, $subpath);
				}
		}
	public
		function __get($var)
		{
			return $this->children[$var];
		}
	public
		function __set($var, $value)
		{
			if ($this->children[$var] instanceof valueMapper)
				$this->children[$var]->setValue($value);
		}
	public
		function current()
		{
			return current ($this->children);
		}
	public
		function next()
		{
			return next ($this->children);
		}
	public
		function key()
		{
			return key ($this->children);
		}
	public
		function valid()
		{
			return $this->current() !== false;
		}
	public
		function rewind ()
		{
			reset ($this->children);
		}
}
class valueMapper
{
	private
		$reference;
	private
		$path;
	public
		function __construct($value, $path)
		{
			$this->reference = &$value;
			$this->path = $path;
		}
	public
		function __toString()
		{
			return (string) $this->reference;
		}
	public
		function getValue()
		{
			return $this->reference;
		}
	public
		function setValue($value)
		{
			$this->reference = $value;
		}
	public
		function getPath()
		{
			return $this->path;
		}
}
?>
