<?
class join
{
	public
	function __construct($dbo, $wilcard, $objects, array $fields = null, $operator = ' AND', $extra = null)
	{
		$temp = array('`'.$dbo->dboName.'`');
		foreach($objects as $object) 
		{ 
			if (is_null($object))
				throw new errors('A passed object was null.');
			array_push($temp, "`$object->dboName`"); 
		}
		$tableString = join($temp, ', ');
		$whereArray = array();
		$args = array();
		foreach($objects as $object) 
			foreach($object->fields as $field) 
				if ($field->getExternal()) 
					if ($field->getRefTable() == $dbo->dboName) 
						array_push
						(
							$whereArray, 
							"`".$object->dboName."`.`".$field->name.'`=`'.$field->getRefTable()."`.`".$field->getRefColumn().'`'
						);
		foreach($dbo->fields as $field) 
			if ($field->getExternal()) 
				foreach($objects as $object)
					if ($field->getRefTable() == $object->dboName) 
						array_push
						(
							$whereArray, 
							'`'.$dbo->dboName."`.`".$field->name.'`=`'.$field->getRefTable()."`.`".$field->getRefColumn().'`'
						);
		if ($fields)
			foreach($fields as $key => $value) 
				if ($value) 
				{
					if (strstr($key, '.') === false)
					{
						foreach($dbo->fields as $field) 
							if ($key == $field->name)
							{
								array_push($whereArray, '`'.$dbo->dboName.'`.`'.$key.'`=?');
								array_push($args, $value);
							}
					}
					else
					{
						array_push($whereArray, $key . '=?');
						array_push($args, $value);
					}
				}
		$where = join($whereArray, ' ' . $operator . ' ');
		if ($where) $where = " WHERE " . $where;
		$qs = 'SELECT '. $wilcard .' FROM '. $tableString.' '.$where.' '.$extra.';';
		array_unshift($args, $qs);
		call_user_func_array
		(
			array($dbo, 'query'),
			$args
		);
	}
}
?>
