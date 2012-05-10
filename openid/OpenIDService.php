<?php
class OpenIDService 
{
	var $openid_url_identity;
	var $URLs = array();
	var $error = array();
	var $fields = array();
	function & getInstance(& $db) 
	{
		static $instance;
		if (!isset ($instance))
		$instance = new OpenIDService($db);
		return $instance;
	}
	function OpenIDService()
	{
		if (!function_exists('curl_exec')) 
		{
			die('Error: Class OpenIDService requires curl extension to work');
		}
	}
	function SetOpenIDServer($a)
	{
		$this->URLs['openid_server'] = $a;
	}
	function SetTrustRoot($a)
	{
		$this->URLs['trust_root'] = $a;
	}
	function SetCancelURL($a)
	{
		$this->URLs['cancel'] = $a;
	}
	function SetApprovedURL($a)
	{
		$this->URLs['approved'] = $a;
	}
	function SetRequiredFields($a)
	{
		if (is_array($a)){
			$this->fields['required'] = $a;
		}else{
			$this->fields['required'][] = $a;
		}
	}
	function SetOptionalFields($a)
	{
		if (is_array($a))
		{
			$this->fields['optional'] = $a;
		}else{
		$this->fields['optional'][] = $a;
		}
	}
	function SetIdentity($a)
	{
		if ((strpos($a, 'http://') === false) && (strpos($a, 'https://') === false)) {
		if (strpos($a, '/') === false) {
		$a = $a . '/';
		} else {
		}
		$a = 'http://'.$a;
		} else {
		if ((strpos($a, '/', strlen('http://')) === false) || (strpos($a, '/', strlen('https://')) === false)) {
		$a = $a . '/';
		} else {
		}
		}
		$this->openid_url_identity = $this->GlueURL(parse_url($a)); // Should do this because part after the domain shouldnt be lowercase! strtolower($a);
	}
	function GetIdentity()
	{ // Get Identity
		return $this->openid_url_identity;
	}
	function GetError()
	{
		$e = $this->error;
		return @array('code'=>$e[0],'description'=>$e[1]);
	}
	function ErrorStore($code, $desc = null)
	{
		$errs['OPENID_NOSERVERSFOUND'] = 'Cannot find OpenID Server TAG on Identity page.';
		if ($desc == null)
		{
			$desc = $errs[$code];
		}
		$this->error = array($code,$desc);
	}
	function IsError()
	{
		return (count($this->error) > 0);
	}
	function splitResponse($response) 
	{
		$r = array();
		$response = explode("\n", $response);
		foreach($response as $line) 
		{
			$line = trim($line);
			if ($line != "") 
			{
				@list($key, $value) = explode(":", $line, 2);
				$r[trim($key)] = trim($value);
			}
		}
		return $r;
	}
	function OpenID_Standarize($openid_identity)
	{
		$u = parse_url(strtolower(trim($openid_identity)));
		if ($u['path'] == '/'){
		$u['path'] = '';
		}
		if(substr($u['path'],-1,1) == '/'){
		$u['path'] = substr($u['path'], 0, strlen($u['path'])-1);
		}
		if (isset($u['host']) && isset($u['path']))
		{
			if (isset($u['query']))
			{ // If there is a query string, then use identity as is
				return $u['host'] . $u['path'] . '?' . $u['query'];
			}else{
				return $u['host'] . $u['path'];
			}
		}
	}
	function CURL_Request($url, $method="GET", $params = "") 
	{ // Remember, SSL MUST BE SUPPORTED
		if (is_array($params)) 
			$params = http_build_query($params);
		if (strpos($url, "?") === FALSE) 
		{
			$theURL = $url . ($method == "GET" && $params != "" ? "?" . $params : "");
			$curl = curl_init($theURL);
		} 
		else 
		{
			$theURL = $url . ($method == "GET" && $params != "" ? "&" . $params : "");
			$curl = curl_init($theURL);
		}
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_HTTPGET, ($method == "GET"));
		curl_setopt($curl, CURLOPT_POST, ($method == "POST"));
		if ($method == "POST") curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$response = $this->curl_redir_exec($curl);
		if (curl_errno($curl) == 0)
		{
			$response;
		}
		else
		{
			$this->ErrorStore('OPENID_CURL', curl_error($curl));
		}
		$header = curl_getinfo( $curl ); // Want so see if anything else maybe went wrong.
		curl_close($curl); // Free resources
		return $response;
	}
	function HTML2OpenIDServer($content) 
	{
		$get = array();
		preg_match_all('/<link[^>]*rel=["|\']openid.server["|\'][^>]*href="([^"]+)"[^>]*\/?>/i', $content, $matches1);
		preg_match_all('/<link[^>]*href="([^"]+)"[^>]*rel=["|\']openid.server["|\'][^>]*\/?>/i', $content, $matches2);
		$servers = array_merge($matches1[1], $matches2[1]);
		preg_match_all('/<link[^>]*rel=["|\']openid.delegate["|\'][^>]*href="([^"]+)"[^>]*\/?>/i', $content, $matches1);
		preg_match_all('/<link[^>]*href="([^"]+)"[^>]*rel=["|\']openid.delegate["|\'][^>]*\/?>/i', $content, $matches2);
		$delegates = array_merge($matches1[1], $matches2[1]);
		$ret = array($servers, $delegates);
		return $ret;
	}
	function GetOpenIDServer()
	{
		$response = $this->CURL_Request($this->openid_url_identity);
		list($servers, $delegates) = $this->HTML2OpenIDServer($response);
		if (count($servers) == 0)
		{
			$this->ErrorStore('OPENID_NOSERVERSFOUND');
			return false;
		} else {
		}
		if (isset($delegates[0]) && ($delegates[0] != ""))
		{
			$this->openid_url_identity = $delegates[0];
		}
		$this->SetOpenIDServer($servers[0]);
		return $servers[0];
	}
	function GetRedirectURL()
	{
		$params = array();
		$params['openid.return_to'] = $this->URLs['approved'];
		$params['openid.mode'] = 'checkid_setup';
		$params['openid.identity'] = $this->openid_url_identity;
		$params['openid.trust_root'] = $this->URLs['trust_root'];
		if (count($this->fields['required']) > 0)
			$params['openid.sreg.required'] = implode(',',$this->fields['required']);
		if (count($this->fields['optional']) > 0)
			$params['openid.sreg.optional'] = implode(',',$this->fields['optional']);
		if (strpos($this->URLs['openid_server'], "?") === FALSE) 
			return $this->URLs['openid_server'] . "?". http_build_query($params);
		else 
			return $this->URLs['openid_server'] . "&". http_build_query($params);
	}
	function Redirect()
	{
		$redirect_to = $this->GetRedirectURL();
		if (headers_sent())
		{ // Use JavaScript to redirect if content has been previously sent (not recommended, but safe)
			echo '<script language="JavaScript" type="text/javascript">window.location=\'';
			echo $redirect_to;
			echo '\';</script>';
		}else{ // Default Header Redirect
			header('Location: ' . $redirect_to);
		}
	}
	function ValidateWithServer($serverResponse)
	{
		$params = array(
			'openid.assoc_handle' => ($serverResponse['openid_assoc_handle']),
			'openid.signed' => ($serverResponse['openid_signed']),
			'openid.sig' => ($serverResponse['openid_sig'])
		);
		$arr_signed = explode(",",str_replace('sreg.','sreg_',$serverResponse['openid_signed']));
		for ($i=0; $i<count($arr_signed); $i++)
		{
			$s = str_replace('sreg_','sreg.', $arr_signed[$i]);
			$c = $serverResponse['openid_' . $arr_signed[$i]];
			$params['openid.' . $s] = ($c);
		}
		$params['openid.mode'] = "check_authentication";
		$openid_server = $this->GetOpenIDServer();
		if ($openid_server == false)
		{
			return false;
		}
		$response = $this->CURL_Request($openid_server,'POST',$params);
		$data = $this->splitResponse($response);
		if ($data['is_valid'] == "true") 
			return true;
		else
			return false;
	}
	function CreateNonce($username) 
	{
		return $username . '' . time();
	}
	function GlueURL($parsed)
	{
		if (! is_array($parsed)) return false;
		$uri = isset($parsed['scheme']) ? $parsed['scheme'].':'.((strtolower($parsed['scheme']) == 'mailto') ? '':'//'): '';
		$uri .= isset($parsed['user']) ? $parsed['user'].($parsed['pass']? ':'.$parsed['pass']:'').'@':'';
		$uri .= isset($parsed['host']) ? strtolower($parsed['host']) : '';
		$uri .= isset($parsed['port']) ? ':'.$parsed['port'] : '';
		$uri .= isset($parsed['path']) ? $parsed['path'] : '';
		$uri .= isset($parsed['query']) ? '?'.$parsed['query'] : '';
		$uri .= isset($parsed['fragment']) ? '#'.$parsed['fragment'] : '';
		return $uri;
	}
	function curl_redir_exec($ch)
	{
		static $curl_loops = 0;
		static $curl_max_loops = 20;
		if ($curl_loops++ >= $curl_max_loops)
		{
		$curl_loops = 0;
		return FALSE;
		}
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		@list($header, $data) = split("\n\n", $response, 2);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($http_code == 301 || $http_code == 302)
		{
		$matches = array();
		preg_match('/Location:(.*?)\n/', $header, $matches);
		$url = @parse_url(trim(array_pop($matches)));
		if (!$url)
		{
			$curl_loops = 0;
			return $response; // was: return $data;
		}
		$last_url = parse_url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
		if (!$url['scheme'])
		$url['scheme'] = $last_url['scheme'];
		if (!$url['host'])
		$url['host'] = $last_url['host'];
		if (!$url['path'])
		$url['path'] = $last_url['path'];
		$new_url = $url['scheme'] . '://' . $url['host'] . $url['path'] . ($url['query']?'?'.$url['query']:'');
		curl_setopt($ch, CURLOPT_URL, $new_url);
		return $this->curl_redir_exec($ch);
		} else {
		$curl_loops=0;
		return $response; // was: return $data;
		}
	}
}
?>
