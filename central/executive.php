<?
class executive 
{
	public
		$user;
	public
		$state;
	public
		$db;
	public 
		function __construct($user = null, $db) 
		{
			if ($user instanceof iuser)
				$this->user = $user;
			$this->db = $db;
			$this->state = new state();
		}
	public
		function execute(state $s)
		{
			$this->state = $s;
			$this->performActionAndDisplayDocument();
		}
	public
		function saveAndTryLogin() 
		{
			if ($this->user instanceof iuser)
			{
				$this->state->save();
				$this->state->setCurrentPath(new path('login'));
				$this->state->setCallback(null);
				$this->performActionAndDisplayDocument();
			}
			else
			{
				print 'No <strong>iuser</strong> instance available! ';
				if (DEBUG_MODE()) print 'Is the database missing the user object?';
			}
		}
	private 
		function isLoginCallback(callback $callback)
		{
			return ($callback->getControllerName() == USER_CLASS) && in_array($callback->getMethodName(), array('login', 'logout'));
		}
	public
		function performActionAndDisplayDocument() 
		{
			if ($this->state->isAction()) 
			{
				if ($this->checkCallbackPermission($this->state->getCallback())) 
				{
					$this->state->setLastActionResult($this->performAction($this->state));
					if ($this->isLoginCallback($this->state->getCallback())) 
					{
						if ($this->user->loggedin()) 
						{ 
							if ($this->state->restore()) 
							{
								$this->performActionAndDisplayDocument();						
							}
							else
							{
								$this->displayDocument();
							}
						}
						else
						{
							$this->state->setCurrentPath(new path('/login'));
							$this->displayDocument();
						}
					}
					else
					{
						if (!$this->checkPermission($this->state->getCurrentPath())) 
						{
							$this->state->setCurrentPath(new path('/login'));
						}
						$this->displayDocument();
					}
				}
				else
				{
					$this->saveAndTryLogin();
				}
			}
			else
			{
				if ($this->checkPermission($this->state->getCurrentPath())) 
				{
					$this->displayDocument();
				}
				else
				{
					$this->saveAndTryLogin();
				}
			}
		}
	public
		function displayDocument() 
		{	
			$postParams = $this->state->postParams();
			$this->getDocument()->output($postParams);
		}
	public
		function performAction($state) 
		{
			try
			{
				$callback = $state->getCallback();
				$postParams = $state->getPostParams();
				$lastActionResult = $callback->call(&$postParams);
			}
			catch (errors $e)
			{
				$lastActionResult = $e;
			}
			$targetState = $state->getTargetState();
			$relocated = false;
			if (isset($targetState) && !($lastActionResult instanceof errors)) //Nu e eroare !
			{
				if ($targetState->getRequestType() == 'xhtml')
				{
					$targetState->setLastActionResult($lastActionResult);
					unset($postParams['target']);
					$targetState->setPostParams($postParams);
					if (!headers_sent())
					{
						$_SESSION['target'] = $targetState->getSerialized();
						$relocated = header('Location: ' . $targetState->getTargetURL());
					}
				}	//Go to the new url only if we are in xhtml mode!
			}
			if (!($lastActionResult instanceof errors) && $relocated)
				exit();
			return $lastActionResult;			
		}
	public // Incearca includerea fisierului $includeFile in cache-ul PHP pt. accesari ulterioare
		function tryInclude($includeFile) 
		{
			if (!file_exists($includeFile)) 
				return false;
			else 
			{
				include($includeFile);
				return true;
			}
		}
	public
		function getDocument($path = null, $user = null, $reqType = null, $db = null)
		{
			if ($path == null)
				$path = $this->state->getPathInstance();
			else
				if (!($path instanceof path))
					throw new errors('The path must be a path instance');
			if ($user == null)
				$user = $this->user;
			if ($db == null)
				$db = $this->db;
			if ($reqType == null)
				$reqType = $this->state->getRequestType();
			$includeFile = 'body.php'; 
			$classToInstance = $path->pathToCamelCase() . 'Document';		
			if (!class_exists($classToInstance, false))
			{
				$this->tryInclude('./doc' . $path->getPath() . '/'. $includeFile);
			}
			if (class_exists($classToInstance, false)) 
			{
				$instance = new $classToInstance($path->getPath(), &$user, $this, $db);
				if (!($instance instanceof document))
					throw new errors('The class ' . $classToInstance . ' must extend the document class');
				$instance->setType($reqType);
				if (isset($db->objects['log'])) 
				{
					$db->log->addvisit();
				}
			}
			else
			{
				throw new errors("Could not find the requested class $classToInstance");
			}
			return $instance;
		}		
	public
		function checkPermission($path) 
		{
			if (isset($this->user) && ($this->user instanceof iuser))
			{
				if ($path->getPath() == '/login')	
					return true;
				return $this->user->hasPermission($path);				
			}
			else
			{
				return true;
			}
		}
	public
		function checkCallbackPermission(callback $controllerfunction)
		{
			if (($this->user) && (($this->user instanceof iuser)))
			{
				if ($this->isLoginCallback($controllerfunction))
					return true;
				return $this->user->hasCallbackPermission($controllerfunction);
			}
			else
			{
				return $controllerfunction->hasPublicAccess();
			}
		}
	public static
		function getPath() 
		{
			trace('Executive getPath() called!');
			return central()->state->getCurrentPath()->getPath();
		}
	public static
		function getParamStr()
		{
			trace('Executive getParamStr() called!');
			return central()->state->getParamStr();
		}
	public		
	function pathPerform($path, &$param = null, $reqType = 'xhtml', $type = 'action')
	{
		throw new errors('pathPerform is deprecated');
	}
}
?>
