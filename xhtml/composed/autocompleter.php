<?
class autocompleter 
{
	public
		function __construct($id, $path) 
		{
			$a = new state($path);
			$a->requestType = "ajax";
			$url = htmlspecialchars_decode($a->getTargetURL());
			print "<div id=\"$id-update\" class=\"autocompleter\" style=\"display:none;\"></div>";
			print '<script type="text/javascript">';
			?>
				new Ajax.Autocompleter('<?=$id?>','<?=$id."-update"?>','<?=$url?>',{});				
			<?
			print '</script>';
		}
}
?>
