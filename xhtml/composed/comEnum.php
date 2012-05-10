<?
class comEnum extends xmltag
{
	protected
		$length = 0;
	public
		function __construct($attributes)
		{
			if (empty($attributes['notnull']))
				$attributes['options'] = array('' => first(&$attributes['emptylabel'], g('Empty value'))) + $attributes['options'] ;
			$this->length = count($attributes['options']);
			if (!isset($attributes['display']))
			{
				if ($this->length <= 5)
					$attributes['display'] = 'radio';
				else
					$attributes['display'] = 'select';
			}
			if ($attributes['display'] == 'radio')
			{
				new radioset($attributes);
			}
			else
			if ($attributes['display'] == 'select')
			{
				new select($attributes);
			}
		}
}
?>
