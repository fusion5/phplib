<?
abstract class field 
{
	abstract public
		function render($attributes, fieldset $fieldset = null);
	protected
		$value;
	public
		$unsigned = false;
	public
		$name;
	public
		$type;
	protected
		$length = 0;
	public
		$mayBeNull = true;
	public
		$hasDefault = '';
	public
		$label = '';
	public
		function renderValue()
		{
			print $this->value;
		}
	public
		function setRawValue($value)
		{
			if (is_null($value))
				$this->value = null;
			else
				$this->value = $value;
		}
	public
		function setValue($value)
		{
			$this->setRawValue($value);
		}
	public
		function getValue()
		{
			return $this->value;
		}
	public
		function getDefault()
		{
			return $this->hasDefault;
		}
	public
		function setDefault($d)
		{
			$this->hasDefault = trim($d);
		}
	public
		function getHasDefault() 
		{
			return $this->hasDefault || ($this->hasDefault === '0');
		}
	public
		function setLength($length = 0) 
		{
			$this->length = $length;
		}
	public
		function getLength()
		{
			return $this->length;
		}
	public
		function setName($name = '') 
		{
			$this->name = $name;
		}
	public
		function getName()
		{
			return $this->name;
		}
	protected
		function controlAttributes($attributes)
		{
			if (!isset($attributes['value']))
			{
				if (isset($attributes['default']))
					$attributes['value'] = $attributes['default'];
				else
				if ($this->getHasDefault() && strtolower($this->getDefault()) != 'null')
					$attributes['value'] = $this->getDefault();
			}
			try
			{
				$this->setValue(&$attributes['value']);
				$attributes['value'] = $this->getValue();
			}
			catch(errors $e)
			{
				if (isset($attributes['last_value']))
				{
					append(&$attributes['class'], ' error');
					$attributes['error'] = $e->getMessage();
				}
			}
			return $attributes;
		}
	protected
		function getCSSClasses()
		{
			$class = array();
			if ($this->mayBeNull != true)
				$class[] = 'notnull';
			if ($this->unsigned == true)
				$class[] = 'unsigned';
			return $class;
		}
}	
?>
