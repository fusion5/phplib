<?
abstract class controller extends dbo
{
	protected
		$event;
	protected
		$mode = 'normal';
	public 
		function formfields($mode = null)
		{
			return $this->addAttributes(array_flip($this->modeFieldNames($mode)));
		}
	public
		function modeFieldNames($mode = null)
		{
			$mode = first($mode, $this->mode);
			$do_reset = false;
			if ($this->selected() == false)
				$do_reset = true;
			if (isset($this->$mode) && count($this->$mode))
				$return = $this->$mode;
			if ($do_reset)
				$this->reset();
			if (!isset($return))
			{
				new p(gf('Inexistent controller mode (%s.%s) - falling back to dbo::formfields()', $this->dboName(), $mode));
				$return = parent::fieldNames();
			}
			return $return;
		}
	public
		function __construct($db)
		{
			parent::__construct(get_class($this), $db);
			if (isset($this->db->objects['event']))
				$this->event = $this->db->objects['event'];
		}
	public
		function setMode($mode)
		{
			$this->mode = $mode;
		}
	public
		function getMode()
		{
			return $this->mode;
		}
	protected
		function get($propertyName)
		{
			$cursor = $this->cursor();
			return $cursor->assoc[$propertyName];
		}
	protected
		function set($name, $value)
		{
			$c = $this->cursor();
			$c->assoc[$name] = $value;	 	
		}
	public
		function insert(array $parameter = null)
		{
			$args = func_get_args();
			$inserted = call_user_func_array(array(&$this, 'parent::insert'), $args);
			$this->onAfterInsert($inserted);
			return $inserted;
		}
	public
	function delete(array $parameter = null)
	{
		$args = func_get_args();
		$this->onBeforeDelete($parameter);
		call_user_func_array(array(&$this, 'parent::delete'), $args);
		$this->onAfterDelete($parameter);
	}
	protected 
		function onBeforeQuery($parameters){return $parameters;}
	protected
	function onAfterInsert($parameters){}
	protected
	function onBeforeDelete($parameters){}
	protected
	function onBeforeInsert($parameters){return $parameters;}
	protected
	function onBeforeUpdate($parameters){return $parameters;}
	protected
	function onAfterUpdate($parameters){}
	protected
	function onAfterDelete($parameters){}
}
?>
