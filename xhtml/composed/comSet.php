<?
class comSet extends xmltag
{
	protected
		$length = 0;
	public
		function __construct($attributes)
		{
			$this->length = count($attributes['options']);
			if (!isset($attributes['display']))
			{
				if ($this->length < 10)
					$attributes['display'] = 'checkbox';
				else
				{
					$attributes['display'] = 'select';
				}
			}
			if ($attributes['display'] == 'checkbox')
			{
				new checkboxset($attributes);
			}
			else
			if ($attributes['display'] == 'select')
			{
				$attributes['multiple'] = 'multiple';
				$attributes['size'] = first(&$this->attributes['size'], 5);
				new select($attributes);
			}
		}
}
?>
