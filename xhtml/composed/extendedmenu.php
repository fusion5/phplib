<?
class extendedmenu extends ul 
{
	public
	function __construct(array $attributes = array())
	{
		if (isset($attributes['class']))
			$attributes['class'] .= ' menu';
		else
			$attributes['class'] = 'menu';
		parent::__construct($attributes);
		$i = 0;
		$items = $attributes['items'];
		foreach($items as $address => $identif)
		{
			preg_match('/[a-zA-Z]+/', $address, $word);
			$word = $word[0];
			$options = array();
			if ($i == 0)
				$options['class'] = 'first';
			if ($i == count($items) - 1)
				$options['class'] = 'last';
			if (strstr(central()->state->getPath(), $word) || (central()->state->getPath() == $address))
				$options['class'] .= " selected";
			$li = new li(null, $options);
				foreach($identif as $title => $param)
					a(x($title, false), $address, $param);
			unset($li);
			$i++;
		}
	}
}
?>
