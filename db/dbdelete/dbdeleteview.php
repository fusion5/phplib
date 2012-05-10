<?
class dbdeleteview extends fieldset
{
	private	
		$dbo;
	private
		$rows;
	public
	function __construct($form, $dbo, $deleterows = null)
	{
		parent::__construct($dbo->dboName.'_del', $form, $dbo);
		$this->dbo = $dbo;
		$this->rows = $deleterows;
	}
	public
	function __destruct()
	{
		$this->display();
	}
	private
	function represent_elements($table_name, $columns, $type)
	{
		$table = central()->db->$table_name;
		$description = first($table->description, ucfirst($table->dboName));
		$count = 0;
		new h3($description);
			foreach($columns as $column_name => $rows)
				foreach($rows as $row)
				{
					$count++;
					if ($table->_tostring)
					print ucfirst($table->_singular) . ': <em>' . $row . '</em>;<br /> ';
				}
		if ($count == 1)
		{
			if ($type == 'restriction')
			{
				$text_m = g('nu poate fi sters');
				$text_f = g('nu poate fi stearsa');
			}
			else
			{
				$text_m = g('va fi sters');
				$text_f = g('va fi stearsa');
			}
			if ($table->_gen_singular == 'm')
				print g('Un').' '.$table->_singular.' '.$text_m;
			if ($table->_gen_singular == 'f')
				print g('O').' '.$table->_singular.' '.$text_f;
		}
		else
			print gf('Number of %s that will be deleted: %u', $table->_plural, $count);
	}
	public	
	function display($mode = 'normal', $itemclass = 'p', $useLabels = true)
	{
		if ($this->rows)
			foreach($this->rows as $row)
				$this->hidden($row->formFieldName(), 1);
		if ($this->form->active())
		{
			print '<div id="dialog">';
			$result = result();
			if (isset($result) && ($result instanceof errors) && ($result->getValue() instanceof dbdeleteaction))
			{
				$deleteAction = $result->getValue();
				foreach($deleteAction->getRestrictions() as $err)
				{
					$row = $err["row"];
					$restrictions = $err["restrictions"];
					$this->dbo->select("*", $row);
					$p = new p();
					new h2(gf("You cannot delete \"%s\" because there are the following references to it not allowing the deletion:", $this->dbo), array(
						"style" => "color:#c00"
					));
					unset ($p);
					foreach($restrictions as $table_name => $columns)
					{
						$p = new p();
						$this->represent_elements($table_name, $columns, "restriction");
						unset ($p);
					}
				}
				foreach ($deleteAction->getCascades() as $err)
				{
					$row = $err["row"];
					$restrictions = $err["restrictions"];
					$this->dbo->select("*", $row);
					new h2(gf("Are you sure you want to permanently delete %s?", $this->dbo));
					if (count($restrictions))
						new p(g("The following items will also be removed").": ");
					foreach($restrictions as $table_name => $columns)
					{
						$p = new p();
						$this->represent_elements($table_name, $columns, "confirmation");
						unset ($p);
					}
					$p = new p();
					$this->af(array
					(
						"name" => $this->dbo->formFieldName()."[confirm]",
						"type" => "checkbox", 
						"label" => g("Yes, sure"),
						"value" => 1
					));
					unset ($p);
				}
			}
			if (!($result instanceof errors))
			{
				$p = new p();
				if (is_numeric($result))
					print gf('A number of %s item(s) were successfuly removed', $result);
				else
					print g('The selected item(s) were successfuly removed');
				unset ($p);
			}
			print '</div>';
		}
	}
}
?>
