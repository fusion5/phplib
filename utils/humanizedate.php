<?
class humanizedate
{
	private
		$seconds = array
		(
			"second" => 1,
			"minute" => 60,
			"hour" => 3600,
			"day" => 86400,
			"week" => 604800
		);
	private
		$timestamp;
	public
	function __construct($date_time)
	{
		$this->timestamp = strtotime($date_time);
	}
	public
		function __toString()
		{
			return date('j M y', $this->timestamp) . ' - ' . $this->displayyear();
		}
	private
	function displayseconds()
	{
		$dif = $this->unitdiff("second");
		if ($dif == 0)
		{
			return g("În această secundă");
		}
		if ($dif == 1)
		{
			return g("Acum o secundă");
		}
		if ($dif > 1)
		{
			return gf("Acum %s secunde", $dif);
		}
	}
	private
	function displayminutes()
	{
		$dif = $this->unitdiff("minute");
		if ($dif == 0)
		{
			return $this->displayseconds();
		}
		if ($dif == 1)
		{
			return g("Acum un minut");
		}
		if ($dif > 1)
		{
			return gf("Acum %s minute", $dif);
		}
	}
	private
	function displayhours()
	{
		$dif = $this->unitdiff("hour");
		if ($dif == 0)
		{
			return $this->displayminutes();
		}
		if ($dif == 1)
		{
			return g("Acum o oră");
		}
		if ($dif > 1)
		{
			return gf("În urmă cu %s ore", $dif);
		}
	}
	private
	function displaydays()
	{
		$dif = $this->unitdiff("day");
		if ($dif == 0)
		{
			return $this->displayhours();
		}
		if ($dif == 1)
		{
			return g("Acum o zi");
		}
		if ($dif > 1)
		{
			return gf("În urmă cu %s zile", $dif);
		}
	}
	private
	function displayweek()
	{
		$dif = $this->unitdiff("week");
		if ($dif == 0)
		{
			return $this->displaydays();
		}
		if ($dif == 1)
		{
			return g("Acum o săptămână");
		}
		else
		if ($dif > 1)
		{
			return gf("În urmă cu %s săptămâni", $dif);
		}
	}
	private
	function displaymonth()
	{
		$currentmonth = date("n");
		$thatmonth = date("n", $this->timestamp);
		$dif = $currentmonth - $thatmonth;
		if ($dif == 0)
		{
			return $this->displayweek();
		}
		if ($dif == 1)
		{
			return g("Luna trecută");
		}
		if ($dif > 1)
		{
			return gf("În urmă cu %s luni", $dif);
		}
	}
	private
	function displayyear()
	{
		$currentyear = date("Y");
		$thatyear = date("Y", $this->timestamp);
		$dif = $currentyear - $thatyear;
		if ($dif == 0)
		{
			return $this->displaymonth();
		}
		if ($dif == 1)
		{
			return g("Anul trecut");
		}
		if ($dif > 1)
		{
			return gf("În urmă cu %s ani", $dif);
		}
	}
	private
	function unitdiff($unit) 
	{
		$diff = time() - $this->timestamp;
		return floor($diff / $this->seconds[$unit]);  
	}  	
}
?>
