<?
class date extends xdatetime
{
	protected
		$fieldset;
	protected
	function displayFields($year, $month, $day, $hour, $minute, $second)
	{
		$id = $this->attributes["id"] . "_" . $this->attributes["name"];
		$this->fieldset->af(array
		(
			"type" => "text",
			"name" => $this->attributes["name"] . "[year]",
			"maxlength" => 4,
			"size" => 4,
			"label" => first($this->attributes["label"]["year"], "Year:"),
			"value" => $year,
			"id" => $id . "_year"
		));
		$this->fieldset->af(array
		(
			"type" => "text",
			"name" => $this->attributes["name"] . "[month]",
			"maxlength" => 2,
			"size" => 2,
			"label" => first($this->attributes["label"]["month"], "Month:"),
			"value" => $month,
			"id" => $id . "_month"
		));
		$this->fieldset->af(array
		(
			"type" => "text",
			"name" => $this->attributes["name"] . "[day]",
			"maxlength" => 2,
			"size" => 2,
			"label" => first($this->attributes["label"]["day"], "Day:"),
			"value" => $day,
			"id" => $id . "_day"
		));
	}
	protected
	function getValue($value)
	{
	}
}
?>
