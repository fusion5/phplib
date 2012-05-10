<?
class event extends controller 
{
	public
	function add($row, $method, $scheduleString)
	{
		$scheduled = date("Y-m-d H:i:s", strtotime($scheduleString));
		$insert = array
		(
			"object" => $row->dboName(),
			"parameters" => $row->idCondition(),
			"method" => $method,
			"scheduled" => $scheduled
		);
		$this->insert($insert);
	}
	public
	function rem($row, $method)
	{
		$delete = array
		(
			"object" => $row->dboName(),
			"parameters" => $row->idCondition(),
			"method" => $method
		);
		$this->delete($delete);
	}
	public
	function cycle()
	{
		$this->select("*");
		$delete = array();
		foreach($this->getRecords() as $event)
		{
			$scheduled = strtotime($event->scheduled);
			$now = mktime();
			if ($scheduled <= $now) // The time has elapsed
			{
				$name = $event->object;
				$select = array();
				parse_str($event->parameters, $select);
				$method = $event->method;
				$this->delete($event);
				$dbo = $this->db->objects[$name];
				$dbo->select("*", $select);
				$call = array($dbo, $method);
				call_user_func($call, $select);
			}
		}
	}
}
?>
