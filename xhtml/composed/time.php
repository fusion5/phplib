<?
class time extends xdatetime
{
	protected
		$fieldset;
	public
		function __construct($attributes, $fieldset)
		{
			parent::__construct($attributes, $fieldset);
		}
	protected
	function displayFields($year, $month, $day, $hour, $minute, $second)
	{
		$id = $this->attributes["id"] . "_" . $this->attributes["name"];
		$this->fieldset->af(array
		(
			"type" => "text",
			"name" => $this->attributes["name"] . "[hour]",
			"maxlength" => 2,
			"size" => 2,
			"label" => first($this->attributes["label"]["hour"], "Hour:"),
			"value" => $hour,
			"id" => $id . "_hour"
		));
		$this->fieldset->af(array
		(
			"type" => "text",
			"name" => $this->attributes["name"] . "[minute]",
			"maxlength" => 2,
			"size" => 2,
			"label" => first($this->attributes["label"]["minute"], "Minute:"),
			"value" => $minute,
			"id" => $id . "_minute"
		));
		$this->fieldset->af(array
		(
			"type" => "text",
			"name" => $this->attributes["name"] . "[second]",
			"maxlength" => 2,
			"size" => 2,
			"label" => first($this->attributes["label"]["second"], "Second:"),
			"value" => $second,
			"id" => $id . "_second"
		));	
	}
	protected
	function getValue($value)
	{
	}
}
?>
