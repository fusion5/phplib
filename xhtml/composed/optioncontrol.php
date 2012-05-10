<?
class optioncontrol
{
	private
		$field = null;
	private	
		$option_value;
	private
		$dbo;
	private
		$param;
	public
		function __construct($field = null, array $attributes, array $param, $dbo = null)
		{
			$this->field = $field;
			$this->dbo = $dbo;
			$this->param = $param;
			$this->process($attributes);
		}
	public
		function process($attributes)
		{
			$this->options = $attributes['options'];
			$control = new form(null, array('id' => $this->field.'_form'));
			if ($control->active())
			{
				$this->setValue($this->param[$this->field]);
			}
			else
			{
				if ($attributes['options'])
					foreach($attributes['options'] as $value => $option_label) break;
				$this->setValue(first(&$_SESSION["options"][$this->field], $value));
			}
			$attributes['name'] = $this->field;
			$attributes['value'] = $this->option_value;
			$attributes['display'] = 'select';
			new radioset($attributes);
			$control->button(g("Alege"));
		}
	public
		function getRepresentation()
		{
			if (isset($this->options[$this->option_value]))
				return $this->options[$this->option_value];
			else
				return 'Undefined representation ' . $this->option_value;
		}
	public
	function setValue($value)
	{
		if (!$value)
		{
			unset($_SESSION["options"][$this->field]);
			$this->option_value = null;
		}
		else 
		{
			$_SESSION["options"][$this->field] = $value;
			$this->option_value = $value;
		}
	}
	public
	function getValue()
	{
		return $this->option_value;
	}
}	
?>
