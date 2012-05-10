<?
class li extends xmltag
{
	public 
	function __construct($iterator = null, $attributes = array())
	{
		if ($iterator != null)
		{
			$class = '';
			if ($iterator instanceof row)
				$iterator = $iterator->getDbo();
			if ($iterator instanceof Iterator)
			{
				$key = $iterator->key();
				if ($key == 0)
					$class .= ' first';
				if ($key % 2 == 1)
					$class .= ' odd';
				if (($iterator instanceof dbo) && ($key == ($iterator->selected() - 1)))
					$class .= ' last';
			}
			else
			{
				$key = key($iterator);
				if (!is_int($key))
					new p('Internal error the array key must be numeric. The array key was ' . $key);
				if (($key == 1) || (($key == 0) && (count($iterator) == 1)))
					$class .= ' first';
				if ($key == 0)
				{
					if (count($iterator) % 2 == 0)
						$class .= ' odd';
					$class .= ' last';
				}
				else
					if ($key % 2 == 0)
						$class .= ' odd';
			} 
			append(&$attributes['class'], ' '.$class);
		}
		parent::__construct('li', $attributes, true);
	}
}
?>
