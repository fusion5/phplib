<?
class fieldset 
{
	protected
		$fsid = null;
	protected
		$fields; // Lista cu toate campurile din acest fieldset
	public
		$row;
	public
		$icontroller;
	protected
		$form; 
  public
		function __construct($fsid = null, $form = null, $row = null) 
		{
			if ($form == null)
				throw new errors('The form can\'t be null');
			if (!($form instanceof formtag))
				throw new errors('The second parameter must be a formtag');
			if ($row instanceof idataaccess)
				$this->icontroller = $row->getDbo();
			else if ($row instanceof icontroller)
				$this->icontroller = $row;
			$this->fsid = $fsid;
			$this->form = $form;
			$this->row 	= $row;
		}
	public
		function __destruct()
		{
			$this->end();
		}
	public
	function display($mode = 'normal', $itemclass = 'p', $useLabels = true)
	{
		if ($this->icontroller instanceof icontroller)
		{
			$fields = $this->icontroller->formfields($mode);
			foreach($fields as $fieldName => $fieldAttributes)
			{
				if (!is_string($fieldName))
					throw new errors(g('The key of the array returned by formfields() must be a string!'));
				$field = $this->icontroller->field($fieldName);
				$use_itemclass = $itemclass;
				if (isset($fieldAttributes['itemclass']) && is_string($fieldAttributes['itemclass']))
					$use_itemclass = strtolower(trim($fieldAttributes['itemclass']));
				else if (isset($field->itemclass) && is_string($field->itemclass))
					$use_itemclass = strtolower(trim($field->itemclass));
				$itemclassParams = array();
				if (isset($fieldAttributes[$use_itemclass]))
					$itemclassParams = $fieldAttributes[$use_itemclass];
				if (is_string($use_itemclass) && $use_itemclass)
				{
					$param1 = null;
					$param2 = null;
					switch ($use_itemclass)
					{
						case 'p':
						case 'span':
							$param2 = $itemclassParams;
							break;
						case 'div':
							$param1 = $itemclassParams;
					}
					$item = new $use_itemclass($param1, $param2);
				}
				$fieldAttributes['name'] = $fieldName;
				if ($useLabels == false) 
					$fieldAttributes['label'] = null;
				$this->af($fieldAttributes);
				if (isset($item)) 
					unset ($item);
			}
		}
		else
		{
			trace($this->icontroller);
			throw new errors('Can\'t use the display() method because the row is not a controller');
		}
	}
	public 
	function end()
	{
	}
	public static
		function getFieldName($name)
		{
			if (preg_match('|\[([^\[]*)\]$|', $name, $temp))
			{
				return $temp[1];
			}
			else
				return $name;
		}
	public static
		function getFlatName($name)
		{
			if (preg_match('/[^\[]*/', $name, $temp))
				return $temp[0];
			else
				return $name;
		}
	public static
		function getSquare($name)
		{
			if (preg_match('/\[.*$/', $name, $temp))
				return $temp[0];
			else
				return '';
		}
	public
		function addFsid($name)
		{
			if ($this->fsid)
			{
				$fieldname = fieldset::getFlatName($name);
				$suffix = fieldset::getSquare($name);
				return $this->fsid . "[$fieldname]" . $suffix;
			}
			else
				return $name;		
		}
	public
		function getFsid()
		{
			return $this->fsid;
		}
	public
		function setIcontroller(icontroller $c)
		{
			$this->icontroller = $c;
		}
	public
		function getIcontroller()
		{
			return $this->icontroller;
		}
	public
		function afs($nproperties, $itemclass = 'p', $useLabels = true)
		{
			throw new errors('deprecated afs called');
		}
	public
		function afsc($object, $itemclass = 'p', $useLabels = true)
		{
			throw new errors('deprecated afsc called');
		}
	public
		function renderField($field, $properties)
		{
			$name = fieldset::getFlatName($properties['name']);
			if (!$name)
				throw new errors('You must provide a name for the renderField() function!');
			$properties['name'] = $this->addFsid($properties['name']);
			if ($this->form->active() && (result() instanceof errors))
			{
				$lastValue = $this->getLastValue(central()->state->postParams(), $properties['name']);
				if (isset($lastValue) && !(isset($properties['type']) && ($properties['type'] == 'radio')))
				{
					$properties['value'] = $lastValue;
				}
				$properties['last_value'] = $lastValue;
			}
			try
			{
				$field->render($properties, $this);
			}
			catch(errors $e) 
			{
				$properties['value'] = null;
				print gf('There were errors thrown for the field %s:', $name) . '<br />' . $e->getMessage();
				if (DEBUG_MODE())
					print $e;
			}
		}
	public
		function getForm()
		{
			return $this->form;
		}
	public
		function af($properties) 
		{
			$name = null;
			if (isset($properties['name']))
				$name = fieldset::getFlatName($properties['name']);
			if (isset($name) && isset($this->icontroller) && $this->icontroller->field($name))
			{
				$field = $this->icontroller->field($name);
				if ($this->row instanceof row)
				{
					$properties['value'] = first(&$properties['value'], $this->row->$name);
				}
				$this->renderField($field, $properties);
			}
			else
			{
				$this->renderField(controlfield::getFieldInstance($properties), $properties);
			}
		}
  private
		function getLastValue($param, $fieldname)
		{
			$lastValue = null;
			$varname = ereg_replace("\[.*\]", '', $fieldname);
			if (isset($param[$varname]))
			{
				if (is_array($param[$varname])) 
				{
					$keys = preg_split("/\[|\]\[|\]/", $fieldname, -1, PREG_SPLIT_NO_EMPTY);
					array_shift($keys);
					$tmp = $param[$varname];
					foreach($keys as $key)
					{
						if (isset($tmp[$key]) && is_array($tmp))
							$tmp = $tmp[$key];	
						else
							$tmp = null;
					}	
					$lastValue = $tmp;
				}							
				else 
					$lastValue = $param[$varname];
			}
			if (is_string($lastValue))
				$lastValue = htmlspecialchars($lastValue);
			return $lastValue;
		}
  public 
  function hidden($name, $value, $attributes = array())
  {
  	$attributes['type'] = "hidden";
  	$attributes['value'] = $value;
  	$attributes['name'] = $this->addFsid($name);
		new input($attributes);
  }
	public
		function button($caption, $attributes = array()) 
		{
			$disabled = false;
			if (isset($this->callback) && !central()->checkCallbackPermission($this->callback))
			{
				$disabled = true;
				$attributes['disabled'] = 'disabled';
			}
			$attributes['type'] = 'submit';
			append (&$attributes['class'], ' but');
			if (isset($attributes['name']))
				$attributes['name'] = $this->addFsid($attributes['name']);
			$attributes['value'] = $caption;
			print '<div>';
			$b = new button($attributes);
			if (strstr($attributes['class'], 'icon') !== false)
				new span(array(
					'class' => $attributes['class']
				));
			print $caption;
			unset ($b);
			if ($disabled)
				print ' ' . g('The account you are using doesn\'t have the required rights to submit this form!');
			print '</div>';
		}
}
?>
