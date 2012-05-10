<?
class dbdeleteaction
{
	private
		$param; 
	private
		$dbo; 
	private
		$deletecount = 0;
	private
		$primaryKeys = array();
	private
		$options = array
		(
			"forcedelete" => false,
			"except_tables" => array(),
			"delete" => array()
		);
	private
		$restrictions = array();
	private
		$cascades = array();
	const debug = false;
	public
	function __construct($dbo, $param, $options = null)
	{
		$this->param = &$param[$dbo->dboName . '_del'];
		$this->dbo = $dbo;
		$this->primaryKeys = $this->dbo->idFieldNames();
		if (count($this->primaryKeys) == 0)
			throw new errors(g("Can't use dbdeleteaction for tables that don't have primary keys!"));
		if ($options)
			$this->options = $options;
		$this->perform();
	}
	public
		function result()
		{
			return $this->deletecount;
		}
	public
		function getRestrictions()
		{
			return $this->restrictions;
		}
	public
		function getCascades()
		{
			return $this->cascades;
		}
	private
	function perform()
	{
		$this->cascades = array();
		$this->restrictions = array();
		$param = $this->param[$this->dbo->dboName]; 
		$this->walk($param, array());	
		if (count($this->getCascades()) || count($this->getRestrictions()))
			throw new errors(g("Deletion was not complete"), null, null, $this);
		if ($this->deletecount == 0)
			throw new errors(g("No items were selected for deletion!"));
	}
	private
		function walk($param, $stack)
		{
			if (count($stack) == count($this->primaryKeys))
			{
				$this->dbo->select('*', $stack);
				if ($this->dbo->selected())	$this->deleteitem($stack, is_array($param) && isset($param["confirm"]));
			}
			else
			{
				if (count($param))
					foreach($param as $key => $newparam)
					{
						$idFieldName = $this->primaryKeys[count($stack)];
						$stack[$idFieldName] = $key;
						$this->walk($newparam, $stack);
						unset($stack[$idFieldName]);
					}
			}
		}
	private
		function deleteitem($stack, $confirmed)
		{
			if ($this->options["forcedelete"])
			{
				$this->deletecount++;
				$this->dbo->delete($stack);
			}
			else
			{
				$restrictions_check = $this->dependency_check($this->dbo->dboName, $stack, array("restrict"));
				if (count($restrictions_check))
				{
					$this->restrictions[] =	array (
						"row" => $stack, 
						"restrictions" => $restrictions_check
					);
				}
				else
				{
					if ($confirmed === true)
					{
						$this->deletecount++;
						$this->dbo->delete($stack);
					}
					else
					{
						$cascade_check = $this->dependency_check($this->dbo->dboName, $stack, array("cascade"));
						$this->cascades[] = array (
							"row" => $stack, 
							"restrictions" => $cascade_check
						);
					}
				}
			}
		}
	private
	function is_exception($table)
	{
		if (count($this->options['except_tables']))
			foreach($this->options['except_tables'] as $except_table)
				if ($table === $except_table)
					return true;
		return false;
	}
	private
	function dependency_check($table, $param, array $levels = array("cascade"))
	{
		if (dbdeleteaction::debug)
		{
			print "Dependency check pentru $table cu parametrii ".print_r($param, true)."\n";
		}
		$dependencies = array();
		$parent_dbo = central()->db->$table;
		if (!$this->is_exception($parent_dbo))
		{
			try
			{
				$parent_dbo->select("*", $param);
			}
			catch (errors $e){}
			if ($parent_dbo->selected())
			{
				if (dbdeleteaction::debug)
				{
					print "Am gasit record-uri in $table \n";
				}
				foreach(central()->db->objectNames as $tableString)
				{
					$dbo = central()->db->$tableString;
					$dbo->readFields();
					if (count($dbo->fields))
						foreach($dbo->fields as $field)
						{
							if (($field->getExternal()) && ($field->getRefTable() == $table))
							{
								if (dbdeleteaction::debug)
								{
									print "Am gasit " . $dbo->dboName.".".$field->getName() . "\n";
								}
								$cname = $field->getName();
								$select = $parent_dbo->id(); //array($cname => $parent_dbo->$cname);
								try
								{
									$dbo->select("*" , $select);
								}
								catch (errors $e){}
								if (dbdeleteaction::debug)
								{
									print "Selectam in $dbo->dboName campul " . print_r($select, true) ."\n";
									print "Am gasit ".$dbo->selected()." campuri \n";
								}
								$levels_plus_cascade = $levels;
								$levels_plus_cascade[] = 'cascade';
								if ($dbo->selected()) 	
									if (in_array($field->getOnDelete(), $levels_plus_cascade) && !$this->is_exception($dbo))
										foreach($dbo->getRecords() as $entry)
										{
											if (dbdeleteaction::debug)
											{
												print "Gasit din $table : $field->getOnDelete() in tabelul $dbo->dboName coloana $cname \n";
												print "Il adaugam la dependinte? " . "\n";
											}
											if (in_array($field->getOnDelete(), $levels))
												$dependencies[$dbo->dboName][$field->getName()][] = $entry;
											$tmp_dependencies = $this->dependency_check($dbo->dboName, $entry->assoc, $levels);
											foreach($tmp_dependencies as $table_name => $columns)
												foreach($columns as $column_name => $rows)
													foreach($rows as $row)
													{
															$dependencies[$table_name][$column_name][] = $row;
													}
											$tmp_dependencies = null;
										}
							}
						}
				}
			}
		}
		return $dependencies;
	}
}
?>
