<?
abstract class xmltag 
{
	protected
		$attributes = array();
	protected
		$tagName;
	protected
		$open = false;
	protected
		$classes = array();
	protected
		$tagAttributes = array();
	protected
		$stdAttributes = array ("id", "class", "title", "style", "dir", "lang", "xml:lang", 
		"onfocus", "onblur", "onclick", "ondblclick", "onmousedown", "onmouseup", 
		"onmouseover", "onmousemove", "onmouseout", "onkeypress", "onkeydown", "onkeyup");
	public
	function __construct($tagName, $attributes = array(), $open = false)
	{
		$this->open = $open;
		$this->tagName = $tagName;
		$this->attributes = $attributes;
		if (isset($this->attributes['class']))
			$this->addClass($this->attributes['class']);
		print '<' . $this->tagName;
		$attr = '';
		if (is_array($this->attributes))
			foreach($this->attributes as $attribute => $value)
				if 
				(
					in_array($attribute, $this->tagAttributes) || 
					in_array($attribute, $this->stdAttributes) 
				)
				{
					$methodName = 'get' . ucfirst($attribute);
					if (method_exists($this, $methodName))
						call_user_func_array(array($this, $methodName), array(&$value));
					if (!is_null($value))
						$attr .= $attribute . '="'.$value.'" ';
				}
		$attr = trim($attr);
		if (strlen($attr))
			print ' ' . $attr;
		if ($this->open)
			print '>';
		else
			print ' />';
	}
	public
		function __destruct()
		{
			if ($this->open)
				print '</'.$this->tagName.'>';
			if (class_exists('javascript'))
			{
				foreach ($this->classes as $class => $value)
				javascript::getInstance()->trycreateinstanceof($class, $this->attributes);
			}
		}
	protected
		function addClass($classNames)
		{
			$classNames = trim($classNames);
			if ($classNames)
			{
				$classes = split(' ', $classNames);
				foreach($classes as $value)
					if ($value)
						$this->classes[$value] = 1;
			}
		}
	protected
		function remClass($classNames)
		{
			$classNames = trim($classNames);
			if ($classNames)
			{
				$classes = split(' ', $classNames);
				foreach($classes as $value)
					unset($this->classes[$value]);
			}
		}
	protected
		function getClass(&$value)
		{
			$r = '';
			foreach($this->classes as $class => $val)
				$r .= $class . " ";
			$r = trim($r);
			$value = $r;
		}
}
?>
