<?
class wsserver 
{
	private
		$db;
	private
		$wsdlFile; 
	private
		$user;
	public
		function __construct($wsd, db $db, user $user)
		{
			$this->wsdlFile = $wsd;
			$this->db = $db;
			$this->user = $user;
			$classmap = $this->getClassMap();
			$typemap = $this->getTypeMap();
			$server = new SoapServer($this->wsdlFile, array(
			));
			$server->setClass('wsinterpretor', $db, $user);
			$server->handle(); 
		}
	private
		function getClassMap()
		{
			$return = array();
			foreach($this->db->objectNames as $objectName)
				if ($this->db->$objectName instanceof dbo)
				{
					$return[$objectName.'_type'] = $objectName;
				}
			return $return;
		}
	private
		function getTypeMap()
		{
			$return = array();
			foreach($this->db->objectNames as $objectName)
				if ($this->db->$objectName instanceof dbo)
				{
					$return[] = array(
						'type_name' => $objectName.'_type',
						'type_ns' => 'factronic',
						'from_xml' => array($this, 'fromXMLTest'),
						'to_xml' => array($this, 'toXMLTest')
					);
				}
			return $return;
		}
	public
		function fromXMLTest(string $arguments)
		{
			return new stdClass();
		}
	public
		function toXMLTest(object $arguments)
		{
			return '<stuff />';
		}
}
?>
