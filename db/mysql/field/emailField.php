<?
class emailField extends myVarcharField
{
	public
		function renderValue()
		{
			new a(null, array(
				'href' => 'mailto:'.$this->getValue(),
				'caption' => $this->getValue() 
			));
		}
	public
		function setValue($email)
		{
			if (isset($email))
				validateEmail($email);
			parent::setValue($email);
		}
}
?>
