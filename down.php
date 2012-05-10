<?
$request = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$absolute_url = ABS_URL;
$path = new path(substr($request, strlen($absolute_url))); 
$repositoryPath = $path->getFirst();
$repository = new filerepository('./'.$repositoryPath);
try
{
	$path->remFirst();
	$file = $repository->getHashedFile(rawurldecode($path->__toString()));
	if (is_file($file) && is_readable($file))
	{
		header("Content-type: application/force-download");
		header("Content-length: ".filesize($file));
		header("Cache-Control: max_age=0");
		header("Content-Transfer-Encoding: Binary");
		header("Content-Disposition: attachment; filename=\"".basename(rawurldecode($path->__toString()))."\"");
		header("Pragma: public");
		readfile($file); 
	}
	else
	{
		throw new httpException(404);
	}
}
catch (httpException $e)
{
	header($e->getHeader());
	print '<h1>HTTP Error - ' . $e->getCode() . '</h1>';
	if (DEBUG_MODE())
	{
		print '<p><strong>'.$e->getMessage().'</strong></p>';
		print '<pre>';
			print $e;
		print '</pre>';
	}
}
?>
