<?
class javascript
{
	public
		$jscache;
	private
		$classMapping = array 
		(
			'prototype' => 'frameworks/prototype/prototype.js',
			'popup' => 'dom/Popup.js',
			'swf' => 'dom/swf/SwfObj.js',
			'swfobject' => 'dom/swfobject/swfobject.js',
			'map_' => 'api/yahoo/map/Map.js',
			'mapinput' => 'api/yahoo/map/MapInput.js',
			'editabletree' => 'dom/formcomponents/EditableTree.js',
			'tabset' => 'dom/formcomponents/TabSet.js',
			'date' => 'dom/calendar/DateSelection.js',
			'lightbox' => 'dom/lightbox/lightbox.js',
			'fckeditor' => 'dom/fckeditor/fckeditor.js',
			'selectcolor' => 'dom/formcomponents/SelectColor.js',
			'lightview' => 'dom/lightview/js/lightview.js',
			'relative_select' => 'dom/formcomponents/RelativeSelect.js',
			'sortablelist' => 'dom/formcomponents/SortableList.js',
			'selectrating' => 'dom/formcomponents/SelectRating.js'
		);
	private 
		$embeded = array();
	private
		$lastchange = 0;
	private
		$path = '';
	private static 
		$instance;
	public static
		function getInstance()
		{
			if (javascript::$instance == null)
				javascript::$instance = new javascript();
			return javascript::$instance;
		}
	public
		function __construct()
		{
			if (!defined('JSLIB'))
				exit ('JSLIB not defined!');
			$this->path = new path(central()->state->getPathInstance()->getPath());
		}
	public 
		function trycreateinstanceof($class, $attributes)
		{
			if (isset($this->classMapping[$class]) && isset($this->embeded[$this->classMapping[$class]]))
			{
				if (!isset($attributes['id']))
					sprintf('<strong>Warning</strong> You have to specify an ID value for the class %s', $class);
				$file = $this->classMapping[$class];
				$var = split('/', $file);
				$fileName = array_pop($var);
				$objectName = basename($fileName, '.js');
				$helperClassName = 'js' . ucfirst(strtolower($objectName));
				if (classdef_exists($helperClassName))
				{
					$instance = new $helperClassName($objectName, $attributes, central()->db);
					if (!($instance instanceof jshelper))
						throw new errors(sprintf('A class with the name %s was found but it\'s not a jshelper subclass', $helperClassName));
					$instance->renderJsInstance();
				}
				else
				{
					$json = new json();
					$script = new script(array('type' => 'text/javascript'));
					print 'new '. $objectName . '("' . $attributes['id'] . '", ' . $json->encode($attributes) . ');';
					unset ($script);
				}
			}
		}
	public static
		function _use($class)
		{
			javascript::getInstance()->useClass($class);
		}
	public
		function useClass($class)
		{
			$class = trim($class);
			if (isset($this->classMapping[$class]))
				$this->incl($this->classMapping[$class]);
		}
	public
		function displayIncludeTags(array $include = array())
		{
			global $globalIncludejs;
			if (!isset($globalIncludejs))
				$globalIncludejs = array();
			if (is_array($globalIncludejs))
				$include = $include + $globalIncludejs;
			try
			{
				if (isset($include) && count($include))
				{
					foreach ($include as $class)
						javascript::_use($class);
				} 
				if (is_array($this->embeded))
				{
					foreach ($this->embeded as $file => $value)
					{
						if (is_file(JSLIB . $file))
						{
							$script = javascript::getJsLibPath() . $file;
							new script(array(
								'src' => $script,
								'type' => 'text/javascript'
							));
						}
						print CRLF;
					}
					print CRLF;
				}
			}
			catch(errors $e)
			{
				print '<!-- Could not include javascript tags from ' . JSLIB . '-->';
			}
		}
	public static
		function getJsLibPath()
		{
			return ABS_URL . JSLIB;
		}
	private
		function incl($file)
		{
			if ($file)
			{
				if (!isset($this->embeded[$file]))
				{
					if ($jsbody = $this->getFirstLine(JSLIB . $file))
					{
						preg_match ('|//\s*use\s+(.*)|', $jsbody, $match);
						if (isset($match[1]))
							$includes = split(',', $match[1]);
						else
							$includes = array();
						foreach($includes as $include)
							$this->incl(trim($include));
						$this->embeded[$file] = 1;
					}
					else
					{
						throw new errors('Can\'t read content from the file ' . JSLIB . $file);
					}
				}
			}
		}
	private
		function getFirstLine($file)
		{
			$return = '';
			if (is_file($file))
			{
				$handle = fopen($file, "r");
				if ($handle) 
				{
					$buffer = fgets($handle, 4096);
					$return = $buffer;
					fclose($handle);
				}
			}
			return $return;
		}
}
?>
