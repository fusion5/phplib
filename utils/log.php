<?
class log extends controller 
{
	public
	function addvisit()
	{
		$insert = array
		(
			"ip" => &$_SERVER['REMOTE_ADDR'],
			"uri" => &$_SERVER['REQUEST_URI'],
			"session" => print_r($_SESSION, true),
			"post" => print_r($_POST, true),
			"referer" => &$_SERVER['HTTP_REFERER']
		);
		$this->insert($insert);
	}
}
?>
