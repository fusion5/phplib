<?
class xmldata implements idataaccess
{
	private
		$domelement;
	private
		$defaultnamespace = '';
	public
		function __construct(DOMElement $domelement, $defaultnamespace = '')
		{
			$this->defaultnamespace = $defaultnamespace;
			$this->domelement = $domelement;
		}
	public
		function set($ns, $property, $value, $item = 0)
		{
			print 'Not implemented yet';
			$tags = $this->domelement->getElementsByTagNameNS($ns, $property);
			if (!$tags)
				return;
			$node = $tags->item($item);
			if (!$node)
				return null;
			$node->textContent = $property;
		}
	public
		function get($ns, $property, $item = 0)
		{
			$tags = $this->domelement->getElementsByTagNameNS($ns, $property);
			if (!$tags)
				return null;
			$node = $tags->item($item);
			if (!$node)
				return null;
			return $node->textContent;
		}
	public function __set($name, $value)
	{
		$this->set($this->defaultnamespace, $name, $value);
	}
	public function __get($name)
	{
		return $this->get($this->defaultnamespace, $name);
	}
	public function __toString()
	{
		return null;
	}
	public function id()
	{
		return null;
	}
	public function idFieldNames()
	{
		return null;
	}
	public function formFieldName()
	{
		return null;
	}
	public function fields()
	{
		return null;
	}
	public function fieldNames()
	{
		return null;
	}
	public function idCondition()
	{
		return null;
	}
	public function getAssoc()
	{
		return null;
	}
	public function first()
	{
		return null;
	}
	public function last()
	{
		return null;
	}
	public function odd()
	{
		return null;
	}
	public function db()
	{
		return null;
	}
	public function dboName()
	{
		return null;
	}
	public function getDbo()
	{
		return null;
	}
}
?>
