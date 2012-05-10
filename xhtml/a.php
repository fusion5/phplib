<?
class a extends xmltag
{
	protected
		$tagAttributes = array('href', 'charset', 'coords', 'hreflang', 'name',
		'rel', 'rev', 'shape', 'type', 'target');
	public
	function __construct(state $url = null, $attributes = null)
	{
		if ($url != null && !isset($attributes['href']))
		{
			if ($url->isAction())
			{
				if ($this->active(&$attributes['id']) && (result() instanceof errors))
					result()->display();
				if (isset($attributes['hash']) && !$url->getHash())
					$url->setHash($attributes['hash']);
				if ($url->getTargetState() == null)
				{
					$targetState = new state();
					$targetState->copy(central()->state);
					$targetState->setPostParams(null);
					$targetState->setLastActionResult(null);
					$url->setTargetState($targetState);
				}
				if (isset ($attributes['id']))
				{
					$postParams = $url->postParams();
					$postParams['aid'] = $attributes['id'];
					$url->setPostParams($postParams);
				}
			}
			$attributes['href'] = $url->getTargetURL(true);
		}
		parent::__construct('a', $attributes, true);
		if (isset($attributes['caption']))
			print $attributes['caption'];
	}
	public static
		function active($aid)
		{
			$postParams = central()->state->postParams();
			return ((isset($postParams['aid'])) && ($postParams['aid'] == $aid));
		}
	public
		function name()
		{
			return $this->attributes['name'];
		}
}
?>
