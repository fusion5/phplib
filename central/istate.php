<?
interface istate 
{
	public function initialize($getQuery, $requestURI, $postParams = null, $getParams = null);
	public function setLastActionResult($actionResult);
	public function getLastActionResult();
	public function setPostParams($params);
	public function postParams();
	public function isAction();
	public function save();
	public function restore();
	public function setCurrentPath ($targetPath);
	public function getCurrentPath();
	public function getRequestType();
	public function setCallback(callback $callback = null);
	public function getCallback();
	public function deserialize($string);
	public function getSerialized();
	public function &getTargetState();
	public function setTargetState(state $s = null);
	public function getTargetURL();
}
?>
