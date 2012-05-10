<?
interface iuser
{
	public function login($fields);
	public function logout();
	public function loggedin();
	public function hasCallbackPermission($controllerfunction);
	public function hasPermission($path);
	public function getstreamcontext();
	public function __wakeup();
}
?>
