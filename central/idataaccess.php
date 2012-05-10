<?
interface idataaccess
{
	public function __set($name, $value);
	public function __get($name);
	public function __toString();
	public function id();
	public function idFieldNames();
	public function formFieldName();	
	public function fields();
	public function fieldNames();
	public function idCondition();
	public function getAssoc();
	public function first();
	public function last();
	public function odd();
	public function db();
	public function dboName();
	public function getDbo();
}
?>
