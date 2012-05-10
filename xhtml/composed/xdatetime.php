<?
class xdatetime extends xmltag
{
	protected
		$fieldset;
	public
		function __construct(array $attributes, fieldset $fieldset)
		{
		if (!$attributes["id"])
			$attributes["id"] = rand(1000, 9999);
		$this->attributes = $attributes;
		$this->fieldset = $fieldset;
		if (is_string($this->attributes["label"]))
		{
			new label($this->attributes["label"]);
			$this->attributes["label"] = array
			(
				"year" => "Year", 
				"month" => "Month",
				"day" => "Day",
				"hour" => "Hour",
				"minute" => "Minute",
				"second" => "Second"
			);
		}
		parent::__construct
		(
			"div", 
			array	("id" => $attributes["id"], "class" => "i"), 
			true
		);
		$value = $this->attributes["value"];
		if (is_string($value))
		{
			$value = strtotime($value);
		}
		if (is_null($value))
		{
			$value = mktime();
		}
		if (is_numeric($value))
		{
			$year 	= date("Y", $value);
			$month 	= date("m", $value);
			$day 		= date("d", $value);
			$hour 	= date("H", $value);
			$minute	= date("i", $value);
			$second	= date("s", $value);
		}	
		if (is_array($value))
		{
			$year 	= $value["year"];
			$month 	= $value["month"];
			$day	 	= $value["day"];
			$hour	 	= $value["hour"];
			$minute	= $value["minute"];
			$second	= $value["second"];
		}
		$this->displayFields($year, $month, $day, $hour, $minute, $second);
	}
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
