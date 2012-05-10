<?
class role extends controller
{
	protected
		$normal = array('role_name');
	public
		function initialize()
		{
			$this->setAttributes(array 
			(
				'role_name' => array (
					'label' => g('Role Name')
				)
			));
		}
	public
		function __toString()
		{
			return "$this->role_name";
		}
	public
		function deleteRole($param)
		{
			new dbdeleteaction($this, $param);
			central()->state->addParam('id_role');
		}
}
?>
