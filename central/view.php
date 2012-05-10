<?
abstract class view 
{
	protected
		$db; 
	protected
		$attributes;
	protected
		$template;
	public
		function __construct($template, array $attributes = null) 
		{
			$this->db = central()->db;
			if (!$attributes)
				$attributes = array();
			$this->attributes = $attributes;
			$this->template = $template;
		}
	abstract public 
		function render();
}		
?>
