<?
class leftjoin
{
	private
		$dbo;
	public
	function __construct(dbo $dbo, $wilcard, array $tables, array $fields = null, $operator = ' AND', $extra = null)
	{
		$this->dbo = $dbo;
		if ($wilcard == '*') 
		{
			$wilcards = array($dbo->getWilcards());
		}
		else
			$wilcards = array();
		$name = $dbo->dboName;
		$queryString = '';
		foreach($tables as $table) 
		{
			array_push($wilcards, $table->getWilcards($dbo->primaryKeyNames));
			foreach($table->fields as $field) 
			{
				if ($field->getExternal()) 
				{
					if ($field->getRefTable() == $name) 
					{
						$queryString .= "LEFT JOIN `$table->dboName` ON `$table->dboName`.`$field->name` = `".$field->getRefTable()."`.`".$field->getRefColumn()."` ";
					}
				}
			}
		}
		if ($wilcard = "*") 
			$wilcard = join($wilcards, ", ");
		$queryString = "SELECT $wilcard FROM $name " . $queryString;
		$where_array = array();
		$args = array();
		if ($fields)
			foreach($fields as $key => $value) 
				if ($value) 
				{
					array_push($where_array, $name.'.'.$key.'=?');
					array_push($args, $value);
				}			
		$where = join($where_array, ''.$operator.' ');
		if ($where) $where = " WHERE " . $where;
		$queryString .= $where . " ". $extra;
		array_unshift($args, $queryString);
		call_user_func_array
		(
			array($dbo, 'query'),
			$args
		);
	}
}
?>
