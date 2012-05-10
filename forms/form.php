<?
class form extends fieldset
{
	private
		$goState;
	public
		$form;
	private
		$attributes;
	protected
		$callback;
	public
		function __construct($go = null, $attributes = array(), $controller = null, $methodName = null)
		{
			$this->goState = new state();
			$this->goState->initialize(REQUEST, ABS_URL, array());
			$this->goState->setTargetState(null);
			$this->goState->setPostParams(array());
			if (isset($attributes['goStatePath']))
				$this->goState->setPath($attributes['goStatePath']);
			if (isset($attributes['requestType']))
				$this->goState->setRequestType($attributes['requestType']);
			$targetState = new state();
			$targetState->copy($this->goState);
			if ($go)
				$targetState->setPath($go);
			if ($methodName)
			{
				if (!is_string($methodName))
					throw new errors('The callback must be a string!');
				if ($controller == null)
					throw new errors('You must supply an object with the callback');
				if ($controller instanceof row)
					$co = $controller->getDbo();
				else
					$co = $controller;
				$this->callback = new callback($co, $methodName);
				$this->goState->setCallback($this->callback);
			}
			else
			{
				if ($go)
				{
					$this->goState->setPath($go);
					$this->goState->setParamStr(first(&$attributes['goStateParamStr'], ''));
				}
			}
			if (isset($attributes['autoanchor']) && isset($attributes['id']))
			{
				new a(null, array('name' => $attributes['id']));
				$attributes['hash'] = $attributes['id'];
			}
			if (isset($attributes['hash']))
			{
				$this->goState->setHash($attributes['hash']);
				$targetState->setHash($attributes['hash']);
			}
			$this->attributes = $attributes;
			$this->attributes['action'] = first(&$this->attributes['action'], $this->getAction());
			$this->attributes['id'] = first(&$this->attributes['id'], 'frm' . rand(1000, 9999));
			if (isset($attributes['gostateparamstr']))
				$targetState->setParamStr($attributes['gostateparamstr']);
			$this->form = new formtag($this->attributes);
			parent::__construct(null, $this->form, $controller);
			if (
				array_key_exists('targetState', $this->attributes) &&
				$targetState instanceof state
			)
			{
				$targetState = $this->attributes['targetState'];
			}
			$this->goState->setTargetState($targetState);
			$this->hidden('target', $this->goState->getSerialized());
			$this->hidden('id_form', $this->attributes['id']);
			if ($this->errors()) 
				$this->displayErrors();
		}
	public
		function active()
		{
			return $this->form->active(); 
		}
	public
		function successful()
		{
			return $this->form->successful();
		}
	public
		function errors()
		{
			return($this->active() && (result() instanceof errors));
		}
	public
		function displayErrors()
		{
			if (result() instanceof errors)
			{
				result()->display();
				if (DEBUG_MODE())
					print result();
			}
		}
	private
		function getAction()
		{
			return htmlspecialchars_decode($this->goState->getTargetURL());
		}
}
?>
