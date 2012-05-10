<?
class pg_time_field extends pg_databasefield
{
	public
		function renderDatabaseField($attributes)
		{
			$attributes = $this->controlAttributes($attributes);
			if (!isset($attributes['format']))
				$attributes['format'] = 'H:i';
			new comDatetime($attributes);
		}
	public
		function setValue($parameter)
		{
			if ($parameter !== null)
			{
				if (is_array($parameter))
				{
					$value = $parameter['H'] . ':' . $parameter['i'];
					if (isset($parameter['s']))
						$value .= ':' . $parameter['s'];
					$parameter = $value;
				}
				else
					$parameter = (string)$parameter;
				if (!preg_match('|^(([0-9]{1,2}:[0-9]{1,2}){1}(:[0-9]{1,2}){0,1}){0,1}$|', $parameter))
				{
					$label = first(&$this->name, 'Unkown field');
					throw new errors(gf('Invalid time read from <em>%s</em>: %s', $label, $parameter));
				}
			}
			parent::setValue($parameter);
		}
}
?>
