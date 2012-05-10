<?
class role_access_callback extends abstractAccess
{
	public 
		$role;
	private
		$role_permissions = array();
	private
		$user_permissions = array();
	public 
		function roleHasPermission($id_role, callback $callback)
		{
			if (!count(&$this->role_permissions[$id_role]))
			{
				$this->query('SELECT object_name, method_name FROM role_access_callback WHERE id_role = ?', $id_role);
				foreach($this as $rac)
					$this->role_permissions[$id_role][$rac->object_name][$rac->method_name] = 1;
			}
			return isset($this->role_permissions[$id_role][$callback->getControllerName()][$callback->getMethodName()]);
		}
	public
		function userHasPermission($id_user, callback $callback)
		{
			if ($id_user == 1)
				return true;
			if (!count(&$this->user_permissions[$id_user]))
			{
				$this->query(
					'SELECT object_name, method_name FROM role_access_callback, role, user_has_role WHERE 
					role_access_callback.id_role = role.id_role AND
					role.id_role = user_has_role.id_role AND
					user_has_role.id_user = ?', 
					$id_user
				);
				if ($this->selected())
				foreach($this as $rac)
					$this->user_permissions[$id_user][$rac->object_name][$rac->method_name] = 1;
			}
			return isset($this->user_permissions[$id_user][$callback->getControllerName()][$callback->getMethodName()]);
		}
	public 
		function displayPermission()
		{
			if (!($this->role instanceof dbo))
				throw new errors('You haven\'t specified a role before calling displayPermission');
			if (count($this->db->objects))
			{
				$fs = new form(null, array(
					'id' => 'displayObjectPermissions',
					'autoanchor' => 1
				), $this, 'savePermissions');
				$p = new p();
				$fs->button(g('Save Access to Methods'));
				$fs->hidden('id_role', $this->role->id_role);
				unset ($p);
				$this->select('*', array(
					'id_role' => $this->role->id_role
				));
				?><script type="text/javascript">
					function setIdsValues($ids, checked)
					{
						for(id in $ids)
						{
							$id_value= $ids[id];
							if (typeof $id_value == 'string')
							{
								$checkbox = document.getElementById($id_value);
								if ($checkbox)
									$checkbox.checked = checked;
							}
						}
					}
				</script><?			
				foreach ($this->db->objects as $object_name => $instance)
				{
					new h2(first($instance->description, ucfirst($object_name)));
					$reflectionClass = new ReflectionClass($instance);
					$methods = $reflectionClass->getMethods();
					$ids = array();
					foreach ($methods as $method)
					{
						$declaringClass = $method->getDeclaringClass()->getName();
						if ($method->isPublic() && $method->isUserDefined())
							if 
							(
								(!in_array ($declaringClass, abstractAccess::$ignoreClasses) || 
								(in_array  ($method->getName(), abstractAccess::$allowedMethods))) && 
								(!in_array ($method->getName(), abstractAccess::$disallowedMethods))
							)
						{
							$descriptionText = '';
							$comment = $method->getDocComment();
							preg_match('|@description (.+)$|m', $comment, $matches);
							if (count($matches))
								$descriptionText = $matches[1];
							$checked = false;
							foreach ($this as $groupAccessObject)
								if ($groupAccessObject->object_name == $object_name && $groupAccessObject->method_name == $method->getName())
									$checked = true;
							$p = new p();
							new input(array(
								'name' => 'method_name[' . $object_name . '][' . $method->getName() . ']', 
								'value' => $method->getName(),
								'label' => ucfirst($method->getName()),
								'type' => 'checkbox',
								'id' => $ids[]='permission_'.$this->role->id_role . '_' . $method->getName() .'_' . $object_name,
								'checked' => $checked
							));
							if ($descriptionText) print ' - ' . $descriptionText;
							unset ($p);
						}
					}
					$p = new p();
					$json = new json();
					$ids_json = htmlspecialchars($json->encode($ids));
					new input(array(
						'onclick' => 'setIdsValues('.$ids_json.', true)',
						'value' => g('Check All above'),
						'type' => 'button'
					));
					new input(array(
						'onclick' => 'setIdsValues('.$ids_json.', false)',
						'value' => g('Uncheck All above'),
						'type' => 'button'
					));
					unset($p);
				}
				$p = new p();
				$fs->button(g('Save Access to Methods'));
				new input(array('type' => 'reset', 'value' => g('Reset values')));
				unset ($p);
				unset ($fs);
			}
		}
	public 
		function savePermissions($param)
		{
			if (!isset($param['id_role']))
				throw new errors('No role specified for saving the permissions');
			$this->query('DELETE FROM `role_access_callback` WHERE id_role = ?', $param['id_role']);
			if (count($param['method_name']))
			foreach($param['method_name'] as $object => $fields)
				if (count($fields))
				foreach ($fields as $field => $on)
				{
					$this->insert(array(
						'id_role' => $param['id_role'],
						'method_name' => $field,
						'object_name' => $object
					));
				}
		}
}
?>
