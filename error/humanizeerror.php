<?
class humanizeerror extends errors
{
	public
		function __construct($heading, $description)
		{
			parent::__construct();
			$this->setDescription($description);
			$this->setHeading($heading);
		}
}
?>
