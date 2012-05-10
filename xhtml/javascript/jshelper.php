<?
abstract class jshelper
{
	protected
		$objectName;
	protected
		$attributes = array();
	protected
		$db;
	public
		function __construct($objectName, $attributes, $db = null)
		{
			if ($db)
				$this->db = $db;
			$this->objectName = $objectName;
			$this->attributes = $attributes;
		}
	abstract
		public function renderJsInstance();
}
?>
