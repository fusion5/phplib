<?
class tabset extends xmltag
{
	private
		$id;
	private
		$param;
	private
		$selected;
	public
		function __construct(array $attributes, $param = null)
		{
			$this->id = $attributes['id'];
			$this->param = $param;
			parent::__construct('div', array('id' => $this->id . '_tabset', 'class' => 'tabset'), true);
			if (isset($attributes['selected']))
				$this->selected = $attributes['selected'];
			if (isset($this->param['v_' . $this->id]))
				$this->selected = $this->param['v_' . $this->id];
		}
	public
		function getId()
		{
			return $this->id;
		}
	public
		function isSelected($id)
		{
			return $this->selected == $id;
		}
	private
		function getHiddenVarName()
		{
			return $this->id."_value";
		}
	public
		function nextbutton($caption)
		{
			print "<input type=\"button\" value=\"$caption\" class=\"next\" >";
		}
}
?>
