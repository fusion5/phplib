<?
class row implements idataaccess
{
	private
		$assoc;
	private
		$extraValues = array();
	private
		$dbo;
	public
		$index = 0;
	public
		$rowcount = 0;
	public
	function __construct(array $assoc = null, dbo $dbo, $index = null) 
	{
		$this->dbo = $dbo;
		$this->assoc = $assoc;
		if (isset($index)) 
			$this->index = $index;
	}
	public
		function setAssoc($assoc, $fullcheck = false)
		{
			$success = true;
			if ($assoc)
			{
				$this->assoc = array();
				foreach ($assoc as $key => &$value)
				{
					try
					{
						$this->__set($key, &$value);
					}
					catch(errors $e)
					{
						if (DEBUG_MODE())
							print $e;
						$success = false;
					}
				}
				if ($fullcheck)
					foreach($this->fields() as $name => $field)
						if (!in_array($name, array_keys($assoc)))
						{
							try
							{
								$this->__set($name, null);
							}
							catch(errors $e)
							{
								if (DEBUG_MODE())
									print $e;
								$success = false;
							}
						}
			}
			if (!$success)
				throw new errors(g('There were errors while submitting this request'));
			return $success;
		}
	public
		function parity()
		{
			if ($this->odd())
				return 'odd';
			else
				return 'even';
		}
	public 
		function __set($name, $value) 
		{
			if ($value instanceof sqlfunction)
			{
				$this->assoc[$name] = $value;
			}
			else
			{
				if (isset($this->dbo->fields[$name]))
				{
					$field = $this->dbo->fields[$name];
					$field->setValue($value);
					$value = $field->getValue();
					if (is_null($value) || is_string($value) || is_numeric($value))
					{
						$this->assoc[$name] = $value;
					}
				}
			}
		}
	public 
		function __get($name)
		{
			if (isset($this->assoc[$name]))
			{
				if (is_string($this->assoc[$name]))
					return htmlspecialchars($this->assoc[$name]);
				else
					return $this->assoc[$name];
			}
		}
	public
		function __toString() 
		{
			$this->dbo->cursor_override = $this;
			$string = $this->dbo->__toString();
			return $string;
		}
	public
		function id() 
		{
			$pkeys = $this->dbo->idFieldNames();
			$return = array();
			foreach($pkeys as $pkey)
				$return[$pkey] = &$this->assoc[$pkey];
			return $return;
		}	
	public
		function idFieldNames()
		{
			return $this->dbo->idFieldNames();
		}
	public
		function formFieldName()
		{
			$values = array();
			foreach($this->idFieldNames() as $fieldName)
			{
				$values[] = $this->$fieldName;
			}
			return $this->dbo->dboName.'['.join('][', $values).']';
		}
	public
		function fieldNames()
		{
			return $this->dbo->fieldNames();
		}
	public
		function idFieldName()
		{
			return $this->dbo->idFieldName();
		}
	public
	function fields()
	{
		return $this->dbo->fields();
	}
	public function idCondition()
	{
		$id_array = array();
		$assoc = $this->getAssoc();
		foreach($this->fields() as $field) 
			if ($field->getPrimary())
				array_push($id_array, ($field->name . '=' . $assoc[$field->name]));
		if (count($id_array))
			return $this->idStr = join($id_array, '&');
		else
			throw new errors('Cannot determine id field for ' . $this->tableName);
	}
	public 
	function getAssoc() 
	{
		return $this->assoc;
	}
	public
	function first() 
	{
		return $this->index == 0;
	}
	public
	function last() 
	{
		return $this->index == ($this->dbo->selected() - 1);
	}
	public
	function odd() 
	{
		return ($this->index % 2 == 0);
	}
	public
	function db()
	{
		return $this->dbo->db();
	}
	public
	function dboName()
	{
		return $this->dbo->dboName;
	}
	public
		function getDbo()
		{
			return $this->dbo;
		}
}
?>
