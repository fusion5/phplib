<?
	class trace 
	{
		public
			function __construct()
			{
				$args = func_get_args();
				$arg = $args[0];
				if (DEBUG_MODE())
				{
					if (defined('TRACE_FILE'))
						ob_start();
					$state = state(false);
					if ($state != NULL)
					{
						if (in_array($state->getRequestType(), array('xhtml', 'html')))
						{
							print '<pre>';
							var_export($arg);
							print '</pre>';
						}
						else
						if (in_array($state->getRequestType(), array('xml', 'wsdl')))
						{
							print '<!--' . CRLF;
							print_r($arg);
							print '-->' . CRLF;
						}
					}
					else
					{
						print '<pre>';
						print_r($arg);
						print '</pre>';
					}
					if (defined('TRACE_FILE'))
					{
						$contents = ob_get_contents();
						file_put_contents(TRACE_FILE, $contents . "\n", FILE_APPEND);
						ob_end_clean();
					}
				}
			}
	}	
?>
