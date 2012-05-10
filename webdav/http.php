<?php
class http {
    var $log;
    var $dir;
    var $name;
    var $filename;
    var $url;
    var $port;
    var $verb;
    var $status;
    var $header;
    var $body;
    var $ttl;
    var $headers;
    var $postvars;
    var $xmlrequest;
    var $connect_timeout;
    var $data_ts;
    function http() {
        $this->log = "New http() object instantiated.<br />\n";
        $this->connect_timeout = 30; 
        $this->dir = realpath("./")."/"; //Default to current dir.
        $this->clean();               
        return true;
    }
		function fetch($url="", $ttl=0, $name="", $user="", $pwd="", $verb="GET") 
		{
        $this->log .= "--------------------------------<br />fetch() called<br />\n";
        $this->log .= "url: ".$url."<br />\n";
        $this->status = "";
        $this->header = "";
        $this->body = "";
        if (!$url) {
            $this->log .= "OOPS: You need to pass a URL!<br />";
            return false;
        }
        $this->url = $url;
        $this->ttl = $ttl;
        $this->name = $name;
        $need_to_save = false;
        if ($this->ttl == "0") {
            if (!$fh = $this->getFromUrl($url, $user, $pwd, $verb)) {
                return false;
            }
        } else {
            if (strlen(trim($this->name)) == 0) { $this->name = MD5($url); }
            $this->filename = $this->dir."http_".$this->name;
            $this->log .= "Filename: ".$this->filename."<br />";
            $this->getFile_ts();
            if ($this->ttl == "daily") {
                if (date('Y-m-d',$this->data_ts) != date('Y-m-d',time())) {
                    $this->log .= "cache has expired<br />";
                    if (!$fh = $this->getFromUrl($url, $user, $pwd, $verb)) {
                        return false;
                    }
                    $need_to_save = true;
                    if ($this->getFromUrl()) { return $this->saveToCache(); }
                    } else {
                        if (!$fh = $this->getFromCache()) {
                        return false;
                    }
                }
            } else {
                if ((time() - $this->data_ts) >= $this->ttl) {
                    $this->log .= "cache has expired<br />";
                    if (!$fh = $this->getFromUrl($url, $user, $pwd)) {
                        return false;
                    }
                    $need_to_save = true;
                } else {
                    if (!$fh = $this->getFromCache()) {
                        return false;
                    }
                }
            }
        }
        $this->header = fgets($fh, 1024);
        $this->status = substr($this->header,9,3);
        while ((trim($line = fgets($fh, 1024)) != "") && (!feof($fh))) {
            $this->header .= $line;
            if ($this->status=="401" and strpos($line,"WWW-Authenticate: Basic realm=\"")===0) {
                fclose($fh);
                $this->log .= "Could not authenticate<br />\n";
                return FALSE;
            }
        }
        while (!feof($fh)) {
            $this->body .= fgets($fh, 1024);
        }
        fclose($fh);
        if ($need_to_save) { $this->saveToCache(); }
        return $this->status;
    }
		function getFromUrl($url, $user="", $pwd="", $verb="GET") 
		{
        $this->log .= "getFromUrl() called<br />";
        preg_match("~([a-z]*://)?([^:^/]*)(:([0-9]{1,5}))?(/.*)?~i", $url, $parts);
        $protocol = $parts[1];
        $server = $parts[2];
        $port = $parts[4];
        $path = $parts[5];
        if ($port == "") {
            if (strtolower($protocol) == "https://") {
                $port = "443";
            } else {
                $port = "80";
            }
        }
        if ($path == "") { $path = "/"; }
        if (!$sock = fsockopen(((strtolower($protocol) == "https://")?"ssl://":"").$server, $port, $errno, $errstr, $this->connect_timeout)) {
            $this->log .= "Could not open connection. Error "
                .$errno.": ".$errstr."<br />\n";
            return false;
        }
        $this->headers["Host"] = $server.":".$port;
        if ($user != "" && $pwd != "") {
            $this->log .= "Authentication will be attempted<br />\n";
            $this->headers["Authorization"] = "Basic ".base64_encode($user.":".$pwd);
        }
        if (count($this->postvars) > 0) {
            $this->log .= "Variables will be POSTed<br />\n";
            $request = "POST ".$path." HTTP/1.0\r\n";
            $post_string = "";
            foreach ($this->postvars as $key=>$value) {
                $post_string .= "&".urlencode($key)."=".urlencode($value);
            }
            $post_string = substr($post_string,1);
            $this->headers["Content-Type"] = "application/x-www-form-urlencoded";
            $this->headers["Content-Length"] = strlen($post_string);
        } elseif (strlen($this->xmlrequest) > 0) {
            $this->log .= "XML request will be sent<br />\n";
            $request = $verb." ".$path." HTTP/1.0\r\n";
            $this->headers["Content-Length"] = strlen($this->xmlrequest);
        } else {
            $request = $verb." ".$path." HTTP/1.0\r\n";
        }
        if (fwrite($sock, $request) === FALSE) {
            fclose($sock);
            $this->log .= "Error writing request type to socket<br />\n";
            return false;
        }
        foreach ($this->headers as $key=>$value) {
            if (fwrite($sock, $key.": ".$value."\r\n") === FALSE) {
                fclose($sock);
                $this->log .= "Error writing headers to socket<br />\n";
                return false;
            }
        }
        if (fwrite($sock, "\r\n") === FALSE) {
            fclose($sock);
            $this->log .= "Error writing end-of-line to socket<br />\n";
            return false;
        }
        if (count($this->postvars) > 0) {
            if (fwrite($sock, $post_string."\r\n") === FALSE) {
                fclose($sock);
                $this->log .= "Error writing POST string to socket<br />\n";
                return false;
            }
        } elseif (strlen($this->xmlrequest) > 0) {
            if (fwrite($sock, $this->xmlrequest."\r\n") === FALSE) {
                fclose($sock);
                $this->log .= "Error writing xml request string to socket<br />\n";
                return false;
            }
        }
        return $sock;
    }
    function clean()
    {
        $this->status = "";
        $this->header = "";
        $this->body = "";
        $this->headers = array();
        $this->postvars = array();
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $this->headers["User-Agent"] = $_SERVER['HTTP_USER_AGENT'];
        } else {
            $this->headers["User-Agent"] = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)";
        }
        if (substr($_SERVER['SERVER_PROTOCOL'],0,5) == "HTTPS") {
            $this->headers["Referer"] = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        } else {
            $this->headers["Referer"] = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        }
    }
    function getFromCache() {
        $this->log .= "getFromCache() called<br />";
        if (!$fp=@fopen($this->filename,"r")) {
            $this->log .= "Could not open ".$this->filename."<br />";
            return false;
        }
        return $fp;
    }
    function saveToCache() {
        $this->log .= "saveToCache() called<br />";
        if (!$fp=@fopen($this->filename,"w")) {
            $this->log .= "Could not open ".$this->filename."<br />";
            return false;
        }
        if (!@fwrite($fp,$this->header."\r\n".$this->body)) {
            $this->log .= "Could not write to ".$this->filename."<br />";
            fclose($fp);
            return false;
        }
        fclose($fp);
        return true;
    }
    function getFile_ts() {
        $this->log .= "getFile_ts() called<br />";
        if (!file_exists($this->filename)) {
            $this->data_ts = 0;
            $this->log .= $this->filename." does not exist<br />";
            return false;
        }
        $this->data_ts = filemtime($this->filename);
        return true;
    }
    function table_into_array($rawHTML,$needle="",$needle_within=0,$allowed_tags="") {
        $upperHTML = strtoupper($rawHTML);
        $idx = 0;
        if (strlen($needle) > 0) {
            $needle = strtoupper($needle);
            $idx = strpos($upperHTML,$needle);
            if ($idx === false) { return false; }
            if ($needle_within == 1) {
                $cnt = 0;
                while(($cnt < 100) && (substr($upperHTML,$idx,6) != "<TABLE")) {
                    $idx = strrpos(substr($upperHTML,0,$idx-1),"<");
                    $cnt++;
                }
            }
        }
        $aryData = array();
        $rowIdx = 0;
        $tmp = strpos($upperHTML,"<TR",$idx);
        if ($tmp === false) { return false; }
        $tmp2 = strpos($upperHTML,"</TR>",$tmp);
        if ($tmp2 === false) { return false; }
        $row = substr($rawHTML,$tmp,$tmp2-$tmp);
        $pattern = "/<TH>|<TH\ |<TD>|<TD\ /";
        preg_match($pattern,strtoupper($row),$matches);
        $hdrTag = $matches[0];
        while ($tmp = strpos(strtoupper($row),$hdrTag) !== false) {
            $tmp = strpos(strtoupper($row),">",$tmp);
            if ($tmp === false) { return false; }
            $tmp++;
            $tmp2 = strpos(strtoupper($row),"</T");
            $aryData[$rowIdx][] = trim(strip_tags(substr($row,$tmp,$tmp2-$tmp),$allowed_tags));
            $row = substr($row,$tmp2+5);
            preg_match($pattern,strtoupper($row),$matches);
            $hdrTag = $matches[0];
        }
        $idx = strpos($upperHTML,"</TR>",$idx)+5;
        $rowIdx++;
        $tmp = strpos($upperHTML,"<TR",$idx);
        if ($tmp === false) { return false; }
        $tmp2 = strpos($upperHTML,"</TABLE>",$idx);
        if ($tmp2 === false) { return false; }
        $table = substr($rawHTML,$tmp,$tmp2-$tmp);
        while ($tmp = strpos(strtoupper($table),"<TR") !== false) {
            $tmp2 = strpos(strtoupper($table),"</TR");
            if ($tmp2 === false) { return false; }
            $row = substr($table,$tmp,$tmp2-$tmp);
            while ($tmp = strpos(strtoupper($row),"<TD") !== false) {
            $tmp = strpos(strtoupper($row),">",$tmp);
            if ($tmp === false) { return false; }
            $tmp++;
            $tmp2 = strpos(strtoupper($row),"</TD");
            $aryData[$rowIdx][] = trim(strip_tags(substr($row,$tmp,$tmp2-$tmp),$allowed_tags));
            $row = substr($row,$tmp2+5);
            }
            $table = substr($table,strpos(strtoupper($table),"</TR>")+5);
            $rowIdx++;
        }
        return $aryData;
    }
    function table_into_xml($rawHTML,$needle="",$needle_within=0,$allowedTags="") {
        if (!$aryTable = http::table_into_array($rawHTML,$needle,$needle_within,$allowedTags)) { return false; }
        $xml = "<?xml version=\"1.0\" standalone=\"yes\" \?\>\n";
        $xml .= "<TABLE>\n";
        $rowIdx = 0;
        foreach ($aryTable as $row) {
            $xml .= "\t<ROW id=\"".$rowIdx."\">\n";
            $colIdx = 0;
            foreach ($row as $col) {
                $xml .= "\t\t<COL id=\"".$colIdx."\">".trim(utf8_encode(htmlspecialchars($col)))."</COL>\n";
                $colIdx++;
            }
            $xml .= "\t</ROW>\n";
            $rowIdx++;
        }
        $xml .= "</TABLE>";
        return $xml;
    }
}
?>
