<?
class tab extends xmltag
{
	public
	function __construct(array $attributes)
	{
		$tabset = $attributes['tabset'];
		if (isset($attributes['class']))
			$attributes['class'] .= 'tab';
		else
			$attributes['class'] = 'tab';
		$selected = false;
		if ($tabset->isSelected($attributes['id']))
		{
			$attributes['class'] .= ' selected';
			$selected = true;
		}
		$this->attributes = $attributes;
		if (isset($attributes['formset']))
		{
			$p = new p();
			$formset = $attributes['formset'];
			$formset->af(array(
				'name' => 'v_' . $tabset->getId(),
				'type' => 'radio',
				'value' => $attributes['id'],
				'label' => $attributes['title'],
				'id' => $tabset->getId() . '_' . $attributes['id'],
				'class' => 'tab_radio',
				'checked' => $selected
			));
			unset ($p);
		}
		parent::__construct('div', $this->attributes, true);
	}
}	
?>
