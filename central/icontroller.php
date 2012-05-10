<?
interface icontroller
{
	public function formfields($mode = null);
	public function field($name);
	public function getFieldAttributes($field);
	public function setAttributes(array $a);
	public function getAttributes();
	public function getXMLSchema();
}
?>
