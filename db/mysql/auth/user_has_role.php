<?
class user_has_role extends controller
{
	public
		function saveroles($param)
		{
			foreach($param['user_has_role'] as $id_user => $roles)
			{
				$this->query('DELETE FROM user_has_role WHERE id_user = ?', $id_user);
				foreach ($roles as $id_role => $on)
				{
					$this->insert(array('id_user' => $id_user, 'id_role' => $id_role));
				}
			}
		}
	public
		function hasKey($key)
		{
			foreach($this as $user_has_role)
				if (($user_has_role->id_user == $key['id_user']) && ($user_has_role->id_role == $key['id_role']))
					return true;
			return false;
		}
}
?>
