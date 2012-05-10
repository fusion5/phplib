<?
function a($caption, $path, $paramstr = null, $options = null, $lang = '')
{
	$options["caption"] = $caption;
	$url = new state($path, $paramstr);
	if (isset($options['hash']))
		$url->setHash($options['hash']);
	new a ($url, $options);
}
function p($caption)
{
	new p($caption);
}
function anchor($name)
{
	return new a(null, array("name" => $name));	
}
?>
