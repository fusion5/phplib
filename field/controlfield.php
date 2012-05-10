<?
abstract class controlfield extends field
{
	public static
		function getFieldInstance($properties)
		{
			if (!is_array($properties))
				throw new errors('The getFieldInstance parameter has to be an array!');
			if (!isset($properties['type']))
				throw new errors('No type has been specified for the field at getFieldInstance');
			if (!isset($properties['name']))
				throw new errors('No name has been specified for the field at getFieldInstance');
			$name = $properties['name'];
			$type = $properties['type'];
			$class_name = 'ct' . ucfirst($name) . 'Field';
			$general_class_name = 'ct' . ucfirst($type) . 'Field';
			if (classdef_exists($class_name))
				$return = new $class_name($dbo);
			else
			if (classdef_exists($general_class_name))
				$return = new $general_class_name($dbo);
			else
				throw new errors(gf('Cannot find class definition for the field %s (Looked after %s and then %s)', $name, $class_name, $general_class_name));
			if (!($return instanceof controlfield))
				throw new errors(gf('The class definition for the field %s (%s) was found, but it is not an instance of field!', $name, get_class($return)));
			return $return;
		}
	protected
		function controlAttributes($attributes)
		{
			$attributes = parent::controlAttributes($attributes);
			append(&$attributes['class'], ' '. join($this->getCSSClasses(), ' '));
			return $attributes;
		}
}	
?>
