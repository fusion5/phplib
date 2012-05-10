<?
abstract class abstractAccess extends controller
{
	public static $ignoreClasses = array ('dbo', 'controller');
	public static $allowedMethods = array ('insert', 'delete', 'update', 'selectUpdate');
	public static $disallowedMethods = array (
		'__get', '__set', '__construct', '__destruct', '__toString', 'initialize', 'formfields'
	);
	abstract public function displayPermission();
	abstract public function savePermissions($param);
}
?>
