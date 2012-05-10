<?
class menu extends ul 
{
	protected
		$strict = false;
	public
	function __construct($attributes = array())
	{
		if (!isset($attributes['noclass']))
		{
			if (isset($attributes['class']))
				$attributes['class'] .= ' menu';
			else
				$attributes['class'] = 'menu';
		}
		if (!empty($attributes['strict']))
			$this->strict = true;
		parent::__construct($attributes);
		$i = 0;
		$items = $attributes['items'];
		foreach($items as $address => $identif)
		{
			preg_match('/[a-zA-Z]*$/', $address, $word);
			$word = $word[0];
			$options = array();
			if ($i == 0)
				$options['class'] = 'first';
			if ($i == count($items) - 1)
				$options['class'] = 'last';
			$strictmatch = (state()->getPath() == $address);
			$loosematch = $word && strstr(central()->state->getPath(), $word);
			if (($loosematch && !$this->strict) || $strictmatch)
				append($options['class'], ' selected');
			$li = new li(null, $options);
				$getparams = '';
				if (!empty($attributes['with_get_params']))
					$getparams = state()->getParamStr();
				a($identif, $address, $getparams);
			unset($li);
			$i++;
		}
	}
}
?>
