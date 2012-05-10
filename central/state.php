<?
class state implements istate 
{
	private
		$language = 'ro';
	private	
		$currentPath = null;
	private
		$translatedPath;
	private
		$targetState;	
	private
		$document;
	private	
		$stateGetParams = array();
	private
		$lastActionResult;
	private	
		$requestType = 'xhtml';
	private	
		$hashAnchor = '';
	private
		$callback;
	private
		$docXMLFilePath = 'doc.xml';
	private
		$postParams = array();
	private
		$requestURI = '';
	public
    function __construct($path = '/', $parameters = '', $lang = '', $docXMLFilePath = '') 
    {
			$currentLanguage = null;
			$currentDocXMLFilePath = null;
			$state = state(false);
			if ($state != null)
			{
				$currentLanguage = $state->getLanguage();
				$currentDocXMLFilePath = $state->getDocXMLFilePath();
				$this->setRequestURI($state->getRequestURI());
			}
			$this->language = first($lang, $currentLanguage, $this->getDefaultLanguage());
			$this->setDocXMLFilePath(first($docXMLFilePath, $currentDocXMLFilePath, 'doc.xml'));
			$this->setCurrentPath(new path($path));
			if ($parameters) $this->setParamStr($parameters);
		}
	public
		function setCallback(callback $callback = null)
		{
			$this->callback = $callback;
		}
	public
		function getCallback()
		{
			return $this->callback;
		}
	public
		function setRequestURI($uri)
		{
			$this->requestURI = $uri;
		}
	public
		function getRequestURI()
		{
			return $this->requestURI;
		}
	public
		function setPostParams($params)
		{
			if ($params)
			{
				if (is_array($_FILES))
				{
					foreach($_FILES as $key => $arr)
						if (isset($params[$key]) && is_array($params[$key]))
							$params[$key] += $arr;
						else
							$params[$key] = $arr;
				}
				if (isset($params['target']))
					$this->deserialize($params['target']);
			}
			else
			{
			}
			$this->postParams = &$params;
		}
	public
		function &getPostParams()
		{
			return $this->postParams;
		}
	public
		function getDefaultLanguage()
		{
			if (defined('DEFAULT_LANG'))
				return DEFAULT_LANG;
			else
				return 'ro';
		}
	public
		function setTargetState(state $s = null)
		{
			$this->targetState = $s;
		}
	public
		function &getTargetState()
		{
			return $this->targetState;
		}
	public
		function initialize($query, $requestURI, $postParams = null, $getParams = null) 
		{
			$this->setRequestURI($requestURI);
			$query = first($query, '');
			extract($this->extractLanguage($query));
			if ($new_language != '')
				$this->setLanguage($new_language);
			$query = $new_query;
			$parameters = split('\?', $query);
			$this->initializeRequest($parameters[0]);
			if (isset($parameters[1]))
			{
				$questionMarkParameters = $parameters[1];
				if (!$this->deserialize($questionMarkParameters))
					$this->appendParamStr($questionMarkParameters);
			}
			if (count($postParams))
				$this->setPostParams($postParams);
		}
	private
		function initializeRequest($query)
		{
			$pointPosition = strrpos($query, '.');
			if ($pointPosition) 
			{
				$this->requestType = substr($query, $pointPosition + 1, strlen($query));
				$query = substr($query, 0, $pointPosition);
			}
			$tempPath = new path(); // The path which contains the parameters is not yet translated
			$components = split("-", $query);
			$tempPath->setPath(array_shift($components));
			if (in_array($this->requestType, array('', 'htm', 'html'))) $this->requestType = 'xhtml';
			$paramComponents = array();
			for ($i=0;$i<count($components);$i+=2)
			{
				$key = &$components[$i];
				$value = &$components[$i+1];
				if (isset($key))
					$paramComponents[] = $this->paramConvertInner($key).'='.$this->paramConvertInner($value);
			}
			$this->setParamStr(join('&', $paramComponents));			
			$fullPath = docinfo::getInstance($this->getDocXMLFilePath())->getFullPathFromAliases($tempPath, $this->language);
			if ($fullPath === false)
				throw new httpException(404);
			else
				$this->setCurrentPath($fullPath);
		}
	public
		function setDocXMLFilePath($fp)
		{
			if (is_file($fp) && is_readable($fp))
				$this->docXMLFilePath = $fp;
			else
				throw new httpException(500, 'Cannot find the document paths file in ' . $fp);
		}
	public
		function getDocXMLFilePath()
		{
			return $this->docXMLFilePath;	
		}
	public
		function extractLanguage($query)
		{
			preg_match('/\/?(en|de|ro)?\/?(.*)/', $query, $matches);
			return array('new_language' => $matches[1], 'new_query' => $matches[2]);
		}
	public
		function copy(state $state)
		{
			$this->setPostParams($state->getPostParams());
			$this->setCallback($state->getCallback());
			$this->setPath($state->getPath());
			$this->setParamStr($state->getParamStr());
			$this->setLastActionResult($state->getLastActionResult());
			$this->setRequestType($state->getRequestType());
			$this->setLanguage($state->getLanguage());
			$this->setRequestURI($state->getRequestURI());
			$this->setDocXMLFilePath($state->getDocXMLFilePath());
			if ($state->getTargetState())
			{
				$targetState = new state();
				$targetState->copy($state->getTargetState());
				$this->setTargetState($targetState);
			}
		}
	public
		function deserialize($parameters)
		{
			if ($parameters)
			{
				$state = @unserialize(gzuncompress(base64_decode($parameters)));
				if ($state !== false)
					$this->copy($state);
				return $state;
			}
			return false;
		}
	public
		function getSerialized()
		{
			return base64_encode(gzcompress(serialize($this)));
		}
	public
		function getRequestType()
		{
			return $this->requestType;
		}
	public
		function setRequestType($rq)
		{
			$this->requestType = $rq;
		}
	public
		function getCallbackString()
		{
			if ($this->callback)
				return $this->callback->getSerialized();
			else
				return '';
		}
	public
		function setCallbackString($string)
		{
			if ($string)
				$this->setCallback(callback::getUnserialized($string));
		}
	public
		function getLanguage()
		{
			return $this->language;
		}
	public
		function setLanguage($lang)
		{
			$this->language = $lang;
		}
	public
		function postParams() 
		{
			return $this->postParams;
		}
	public
		function isAction() 
		{
			return $this->callback != null;
		}
	public	
		function sessionParams() 
		{
			return $_SESSION;
		}
	public
		function getLastActionResult() 
		{
			return $this->lastActionResult;	
		}
	public
		function setLastActionResult($a)
		{
			$this->lastActionResult = $a;
		}
	public
		function save() 
		{
			$_SESSION['lock'] = true;
			$_SESSION['state'] = serialize($this);
		}
	public
		function restore() 
		{
			if (isset($_SESSION['lock'])) 
			{
				central()->state = unserialize($_SESSION['state']);
				unset($_SESSION['lock']);
				unset($_SESSION['state']);
				return true;				
			}
			else
				return false;
		}
	public	
		function setPath($val)
		{
			if (!(is_string($val) || is_null($val)))
				throw new errors('setPath only accepts string arguments!');
			$this->setCurrentPath(new path($val));
		}
	public
		function getPath() 
		{
			return $this->getCurrentPath()->getPath();
		}
	public
		function getPathInstance()
		{
			return $this->currentPath;
		}
	public
		function pathToCamelCase() 
		{
			return $this->currentPath->pathToCamelCase();
		}
	public
		function getCurrentPath()
		{
			if ($this->currentPath == null)
			{
				$this->currentPath = $this->updateTranslatedPath(new path());
				$this->translatedPath = $this->getTranslatedPath($this->currentPath);
			}
			return $this->currentPath;
		}
	public
		function setCurrentPath ($path)
		{
			$this->currentPath = $path;
			$this->translatedPath = $this->getTranslatedPath($this->currentPath);
		}
	private
		function getTranslatedPath($path)
		{
			return docinfo::getInstance($this->getDocXMLFilePath())->getAliasesFromFullPath($path, $this->getLanguage());
		}
	public
		function addParam($index, $value = null) 
		{
			if ($value != null)
				$this->stateGetParams[$index] = $value;
			else 
				unset($this->stateGetParams[$index]);
		}
	public	
		function getParam($index) 
		{
			$params = $this->stateGetParams;
			if (isset($params[$index]))
				return $params[$index];
			else
				return null;
		}
	public
		function getParamArray()
	 	{
			return $this->stateGetParams;
		}
	public
		function setHashAnchor($a) 
		{
			$this->setHash($a->name);
		}
	public
		function setHash($val) 
		{
			$this->hashAnchor = $val;
		}
	public
		function getHash() 
		{
			return $this->hashAnchor;
		}
	public
		function appendParamStr($parameters)
		{
			$newParams = '';
			if (count($this->stateGetParams))
			{
				$newParams .= $this->getParamStr() . '&';
			}
			$newParams .= $parameters;
			$this->setParamStr($newParams);
		}
	public
		function setParamStr($parameters)
		{
			parse_str($parameters, $this->stateGetParams);
		}
	public 
		function getParamStr() 
		{
			$params = '';
			$components = array();
			if ($this->stateGetParams != null)
				foreach($this->stateGetParams as $key => $value)
					$components[] = $key . "=" . $value;
			return join('&', $components);
		} 
	public
		function getTargetURL($withTarget = false) 
		{
			if ($this->hashAnchor) 
				$hash = "#".$this->hashAnchor;
			else 
				$hash = '';
			$abs_url = substr($this->getRequestURI(), 0, strlen($this->getRequestURI()) - 1); // The absolute url without the slash at the end.
			$t = array();
			$c = 0;
			if (count($this->stateGetParams))
				foreach($this->stateGetParams as $key => $value)
				{
					$t[] = $this->paramConvertOuter($key);
					if ($value || ($c != count($this->stateGetParams) - 1))
						$t[] = $this->paramConvertOuter($value);
					$c++;
				}
			$param_str = join('-', $t);
			$lang = $this->language;
			if ($lang != $this->getDefaultLanguage())
				$abs_url .= '/'. $lang;
			if ($param_str)
				$param_str = '-' . $param_str; 
			$rq = first($this->requestType, 'xhtml');
			if ($this->translatedPath->getPath() == '/')
				$addr = $abs_url . '/index' . $param_str . '.' . $rq;
			else
				$addr = $abs_url . $this->translatedPath->getPath() . $param_str . '.' . $rq;
			$addr = preg_replace('/&/', '&amp;', $addr);
			if ($withTarget && isset($this->callback))
				$target = '?' . $this->getSerialized();
			else
				$target = '';
			return $addr . $target . $hash;
		}
	private	
		function paramConvertInner($s) 
		{
			$s = urldecode(urldecode($s));
			return $s;
		}
	private	
		function paramConvertOuter($s) 
		{
			$s = urlencode(urlencode($s));
			return $s;
		}
}
?>
