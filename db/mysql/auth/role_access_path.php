<?
class role_access_path extends abstractAccess
{
	public 
		$role;
	private
		$role_permissions = array();
	private
		$user_permissions = array();
	public 
		function roleHasPermission($id_role, path $path)
		{
			if (!count(&$this->role_permissions[$id_role]))
			{
				$this->query('SELECT path FROM role_access_path WHERE id_role = ?', $id_role);
				foreach($this as $rap)
					$this->role_permissions[$id_role][$rap->path] = 1;
			}
			return isset($this->role_permissions[$id_role][$path->getPath()]);
		}
	public
		function userHasPermission($id_user, path $path)
		{
			if ($id_user == 1)
				return true;
			if (!count(&$this->user_permissions[$id_user]))
			{
				$this->query(
					'SELECT path FROM role_access_path, role, user_has_role WHERE 
					role_access_path.id_role = role.id_role AND
					role.id_role = user_has_role.id_role AND
					user_has_role.id_user = ?', 
					$id_user
				);
				if ($this->selected())
				foreach($this as $rap)
					$this->user_permissions[$id_user][$rap->path] = 1;
			}
			return isset($this->user_permissions[$id_user][$path->getPath()]);
		}
  protected
		function listNodes($nodes, $parent) 
		{
			if ($nodes->length)
			{
				$ul = new ul(array('class' => ($parent)?'permisii':null));
				foreach ($nodes as $item) 
				{
					$current = $parent . '/' . $item->tagName;
					$show = null;
					if (($item) and ($item instanceof DOMElement) and ($item->hasAttribute('caption'))) 
						$show = $item->getAttribute('caption');
					if (!$show)	$show = $current;						
					if ($show) 
					{
						$li = new li();
						new input(array
						(
							'name' => "path[$current]",
							'type' => 'checkbox',
							'label' => $show,
							'checked' => $this->roleHasPermission($this->role->id_role, new path($current)),
							'id' => $current
						));
						$this->listNodes($item->childNodes, $current);
						unset ($li);
					}
				}
				unset ($ul);
			}
		} 								
	public function displayPermission()
	{
		if (!($this->role instanceof dbo))
			throw new errors('You haven\'t specified a role before calling displayPermission!');
		$docTree = docinfo::getInstance()->getDocTree();
		$save = new form(null, array(
			'id' => 'formular_modifica_grup_operatori',
			'autoanchor' => true
		), $this, 'savePermissions');
		$save->hidden('update', 'checkboxes');
		$save->hidden('id_role', $this->role->id_role);
		$p = new p();
			$save->button(g('Save Access to Documents'));
		unset ($p);
		$this->select('*', 'WHERE id_role=?', $this->role->id_role);
		$this->listNodes($docTree->documentElement->childNodes, '', $save);
		$p = new p();
		$save->button(g('Save Acces to Documents'));
		unset ($p);
		unset($save);
	}
	public function savePermissions($param)
	{
		$idGroup = $param['id_role'];
		if (!isset($idGroup))
			throw new errors(g('Trebuie sa specificati grupul pentru care salvati permisiile'));
		$this->query('DELETE FROM `role_access_path` WHERE id_role = ?', $idGroup);
		if (is_array($param['path']))
		{
			$param['path']['/'] = '';
			foreach ($param['path'] as $path => $value)
			{
				$insert = array(
					'id_role' => $idGroup,
					'path' => $path
				);
				$this->insert($insert);
			}
		}
	}
}
?>
