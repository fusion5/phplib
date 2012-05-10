<?
class grid extends view
{
	protected
		$fieldset;
	private
		$mode;
	private
		$dbo;
	private
		$id;
	private
		$editstate;
	public
		function __construct(dbo $dbo, array $attributes= null)
		{
			parent::__construct(first(&$attributes['template'], $dbo), $attributes);
			$this->dbo = $dbo;
			$this->mode = &$this->attributes['mode'];
			$this->id = first(&$this->attributes['id'], $this->dbo->dboName());
			if (!isset($this->attributes['fields']))
				$this->attributes['fields'] = $this->dbo->modeFieldNames();
			else
				if (!is_array($this->attributes['fields']))
					throw new errors('The grid "fields" attribute must be an array.');
			if (isset($this->attributes['fieldset']) && $this->attributes['fieldset'] instanceof fieldset)
				$this->fieldset = $this->attributes['fieldset'];
			$this->editstate = new state(central()->state->getPath(), central()->state->getParamStr());
			$this->editstate = $this->resetEditingDeleting($this->editstate);
			$this->editstate->setHash($this->getID());
			if (empty($this->attributes['no_render']))
				$this->render();
		}
	private
		function getID()
		{
			return 'grid_' . $this->dbo->dboName() . '_' . $this->id;
		}
	public
		function render()
		{
			$method = null;
			if ($this->isInMode('edit')) $method = 'selectUpdate';
			if ($this->isInMode('delete')) $method = 'delete';
			if (!isset($method) && $this->insertAllowed()) 
				$method = 'insert';
			if (isset($method) && ($this->fieldset == null))
			{
				$this->fieldset = new form(null, array (
					'id' => $this->getID(),
					'autoanchor' => true,
					'gostateparamstr' => $this->editstate->getParamStr()
				), $this->dbo, $method);
			}
			$table = new table(array(
				'id' => $this->id
			));
			$this->renderHeading();
			if ($this->dbo->selected())
				foreach ($this->dbo as $com)
				{
					$this->displayRow($this->fieldset, $this->editingRow(), $this->deletingRow());
				}
			$this->renderFooter();
			unset($table);
		}
	private
		function isInMode($mode)
		{
			$pks = $this->dbo->idFieldNames();
			foreach($this->dbo as $item)
			{
				$equals = true;
				foreach ($pks as $pk)
					if (central()->state->getParam($mode.'_'.$pk) != $item->$pk) $equals = false;
				if ($equals)
					return true;
			}
			return false;
		}
	private
		function editingRow()
		{
			$pks = $this->dbo->idFieldNames();
			if (!count($pks))
				return false;
			foreach ($pks as $pk)
				if (central()->state->getParam('edit_'.$pk) !== $this->dbo->$pk) return false;
			return true;
		}
	private
		function deletingRow()
		{
			$pks = $this->dbo->idFieldNames();
			foreach ($pks as $pk)
			{
				if (central()->state->getParam('delete_'.$pk) !== $this->dbo->$pk) return false;
			}
			return true;
		}
	public
		function renderHeadingCell($fieldName, $fieldAttributes)
		{
			if (isset($fieldAttributes['label']))
				print $fieldAttributes['label'];
			else
				print ucfirst($fieldName);
		}
	public
		function renderCell($fieldName, $fieldAttributes, fieldset $fieldset = null)
		{
			$function_name = 'render' . ucfirst($fieldName);
			$callback = array($this->template, $function_name);
			if (is_callable($callback))
			{
				call_user_func($callback, $this->attributes + array($this->dbo->dboName() => $this->dbo));
			}
			else
			{
				$fieldInstance = $this->dbo->field($fieldName);
				if (is_callable(array($fieldInstance, 'renderValue')))
				{
					$fieldInstance->setRawValue($this->dbo->$fieldName);
					$fieldInstance->renderValue();
				}
				else
				{
					print $this->dbo->$fieldName;
				}
			}
		}
	public
		function renderInsertCell($fieldName, $fieldAttributes, fieldset $fieldset)
		{
			$function_name = 'renderInsert' . ucfirst($fieldName);
			if ((in_array($fieldName, $this->dbo->fieldNames())) && isset($this->fieldset))
			{
				$callback = array($this->template, $function_name);
				if (is_callable($callback))
					call_user_func($callback);
				else
				{
					$fieldAttributes['name'] = $fieldName;
					unset($fieldAttributes['label']);
					$fieldset->af($fieldAttributes);
				}
			}
		}
	public
		function renderFormCell($fieldName, $fieldAttributes, fieldset $fieldset = null)
		{
			$function_name = 'renderForm' . ucfirst($fieldName);
			if ((in_array($fieldName, $this->dbo->fieldNames())) && isset($this->fieldset))
			{
				$callback = array($this->template, $function_name);
				if (is_callable($callback))
				{
					call_user_func($callback);
				}
				else
				{
					$fieldAttributes['name'] = $fieldName;
					$fieldAttributes['label'] = null;
					$fieldset->af($fieldAttributes);
				}
			}
			else
			{
				$this->renderCell($fieldName, $fieldAttributes, $fieldset);
			}
		}
	public
		function renderData($containerClass = null, $method = 'renderCell', $fieldset)
		{
			if (!is_string($method))
				throw new errors('Method name must be a string');
			$formfields = $this->fields();
			if (count($formfields))
				foreach($formfields as $fieldName => $field)
				{
					if (isset($containerClass))
						$container = new $containerClass(null, &$field[$containerClass]);
					if ($fieldName)
						$this->$method($fieldName, $field, $fieldset);
					if ($container)
						unset ($container);
				}
			else
				new p('Warning: formfields() returned an empty array.');
		}
	protected
		function fields()
		{
			$return = array();
			$fields = $this->attributes['fields']; 
			foreach ($fields as $key => $value)
				$return[$value] = $this->dbo->getFieldAttributes($value);
			return $return;
		}
	private
		function renderHeading(fieldset $fieldset = null)
		{
			$tr = new tr();
			$this->renderData('th', 'renderHeadingCell', $this->fieldset);
			if ($this->deleteAllowed() || $this->updateAllowed())
				new th();
			unset ($tr);
		}
	protected	
		function displayEditingRow(state $state)
		{
			$fieldset = new fieldset('', $this->fieldset->getForm(), $this->dbo->cursor());
			$this->renderData('td', 'renderFormCell', $fieldset);
			$td = new td(null, array('style' => '', 'class' => 'controls'));
				foreach($this->dbo->idFieldNames() as $pk)
					$fieldset->hidden('old_pk['.$pk.']', $this->dbo->$pk);
				if (is_callable(array($this->dbo, 'renderHiddenFields')))
					$this->dbo->renderHiddenFields($fieldset, $this->attributes);
			$fieldset->button(g('Modifica'), array('class' => 'icon confirm'));
			$this->renderCancelLink($state);
		unset ($td);
		unset ($fieldset);
	}
	protected
		function displayDataRow()
		{
			$this->dbo->readFields();
			$this->renderData('td', 'renderCell', $this->fieldset);
			if ($this->updateAllowed() || $this->deleteAllowed())
			{
				$td = new td(null, array('style' => '', 'class' => 'controls'));
				if ($this->updateAllowed())
					$this->renderEditLink(clone $this->editstate);
				if ($this->deleteAllowed())
					$this->renderDeleteLink(clone $this->editstate);
				unset ($td);
			}
		}
	protected
		function renderEditLink($state)
		{
			foreach ($this->dbo->idFieldNames() as $pk)
				$state->addParam('edit_'.$pk, $this->dbo->$pk);
			new a($state, array(
				'caption' => g('Modifica'),
					'class' => 'icon edit'
				));
			}
	protected
		function renderCancelLink(state $state)
		{
			foreach ($this->dbo->idFieldNames() as $pk)
			{
				$state->addParam('edit_'.$pk);
				$state->addParam('delete_'.$pk);
			}
			new a($state, array(
				'caption' => g('Revoca'),
					'class' => 'icon cancel'
				));
		}
	protected
		function renderDeleteLink($state)
		{
			foreach ($this->dbo->idFieldNames() as $pk)
				$post[$pk] = $this->dbo->$pk;
			$state->setPostParams($post);
			$state->setCallback(new callback($this->dbo, 'delete'));
			new a($state, array(
				'caption' => g('Sterge'),
				'class' => 'icon delete',
				'id' => $this->id . '-' . join($post, '-')
			));
		}
	public
		function displayRow($fieldset = null, $edit, $delete)
		{
			if (is_callable(array($this->template, 'getTableRowAttributes')))
				$attributes = $this->template->getTableRowAttributes(array($this->dbo->dboName() => $this->dbo));
			else
				$attributes = array();
			$tr = new tr($this->dbo, $attributes);
			if ($edit && $this->updateAllowed())
				$this->displayEditingRow(clone $this->editstate);
			else
				if ($delete && $this->deleteAllowed())
					$this->displayDeletingRow(clone $this->editstate);
			else
				$this->displayDataRow();
			if (is_callable(array($this->template, 'displayDetailsTR')))
				$this->template->displayDetailsTR(array(
					$this->dbo->dboName() => $this->dbo,
					'colspan' => count($this->attributes['fields'])
				));
			unset ($tr);
		}
	private
		function renderFooter()
		{
			if ($this->insertAllowed() && !$this->isInMode('edit') && !$this->isInMode('delete'))
			{
				$tr = new tr(null, array('class' => 'tableinsert'));
				$this->renderData('td', 'renderInsertCell', $this->fieldset);
				$td = new td(null, array('style' => '', 'class' => 'controls'));
				if (is_callable(array($this->dbo, 'renderHiddenFields')))
					$this->dbo->renderHiddenFields($this->fieldset, $this->attributes);
				if (is_callable(array($this->template, 'renderFormSubmit')))
					$this->template->renderFormSubmit($this->fieldset);
				else
					$this->fieldset->button(g('Adauga'), array('class' => 'icon add'));
				unset ($td);
				unset($f);
				unset ($tr);
			}
		}
	public
		function resetEditingDeleting(state $state)
		{
			foreach ($this->dbo->idFieldNames() as $pk)
			{
				$state->addParam('delete_' . $pk);
				$state->addParam('edit_' . $pk);
			}
			return $state;
		}
	private
		function insertAllowed()
		{
			return $this->iduAllowed() && (strstr($this->mode, 'i') !== false);
		}
	private
		function deleteAllowed()
		{
			return $this->iduAllowed() && (strstr($this->mode, 'd') !== false);
		}
	private
		function updateAllowed()
		{
			return $this->iduAllowed() && (strstr($this->mode, 'u') !== false);
		}
	private
		function iduAllowed()
		{
			return (in_array($this->mode, array('i', 'd', 'u', 'id', 'idu', 'du', 'iu')));
		}
	protected
		function getValidDOMId($prefix)
		{
			$pks = $this->dbo->idFieldNames();
			foreach ($pks as $pk)
				$prefix .= '_'.$pk.'_'.$this->dbo->$pk;
			return $prefix;
		}
}
?>
