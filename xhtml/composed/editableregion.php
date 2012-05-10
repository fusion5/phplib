<?
class editableregion 
{
	public
	function __construct(idataaccess $row, $editable, $hidden, $forceRefresh = false) 
	{
		$forceRefreshStr = '';
		if ($forceRefresh) 
			$forceRefreshStr = ' forceRefresh';
		$control = new form(null, array(
			'class' => 'editableregion' . $forceRefreshStr
		), $row, 'update');
		foreach($hidden as $name => $value)
			$control->hidden($name, $value);
		foreach($editable as $name => $value) 
		{
			$control->af(array
			(
				'name' => $name,
				'type' => 'text',
				'value' => $value,
				'id' => 'in_place_' . $name 
			));
		}
		$control->button("Salvează", array("name" => "save"));
		$control->button("Anulează", array("name" => "cancel"));
		unset($control);
	}
}
?>
