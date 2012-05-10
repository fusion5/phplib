<?
class formtag extends xmltag
{
	private
		$fieldset;
	protected
		$tagAttributes = array("action", "accept", "accept-charset", "enctype", 
		"method");
	public
	function __construct($attributes = array())
	{
		$this->attributes = $attributes;
		if (isset($this->attributes['anchor']) && $this->attributes['anchor'] instanceof a)
			$this->attributes['anchor'] = $this->attributes['anchor']->name();
		$this->attributes["method"] = first(&$this->attributes["method"], "post");
		$this->attributes["accept-charset"] = "utf-8";
		$this->attributes["enctype"] = "multipart/form-data";
		parent::__construct('form', $this->attributes, true);
	}
	public 
		function active()
		{
			return formtag::activeId($this->attributes["id"]);
		}
	public
		static function activeId($id_form)
		{
			$postParams = central()->state->postParams();
			return (isset($postParams['id_form']) && ($postParams['id_form'] == $id_form));
		}
	public
		static function hasErrorCodeId($errCode, $id_form)
		{
			if (formtag::activeId($id_form))
			{
				$errors = &result()->errArray;
				if (count($errors))
					foreach($errors as $key => $error) 
						if ($error['code'] == $errCode) return $error;
			}
			return false;
		}
	public
		function hasErrorCode($errCode)
		{
			return formtag::hasErrorCodeId($errCode, $this->attributes['id']);
		}
	public
		function successful()
		{
			return($this->active() && !(result() instanceof errors));	
		}
}
?>
