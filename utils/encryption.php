<?
class encryption 
{
    private
    $hash_key;
    private
    $hash_lenth;
    private
    $base64;
    private
    $salt = 'gh453h5jnyt54554ef54jtyr4t45';
    function __construct($key,$base64 = true) {
        $this->base64 = $base64;
        $this->hash_key = $this->_hash($key);
        $this->hash_length = strlen($this->hash_key);
    }
    function encrypt($string) {
        $iv = $this->_generate_iv();
        $out = '';
        for($c=0;$c < $this->hash_length;$c++) {
            $out .= chr(ord($iv[$c]) ^ ord($this->hash_key[$c]));
        }
        $key = $iv;
        $c = 0;
        while($c < strlen($string)) {
            if(($c != 0) and ($c % $this->hash_length == 0)) {
                $key = $this->_hash($key . substr($string,$c - $this->hash_length,$this->hash_length));
            }
            $out .= chr(ord($key[$c % $this->hash_length]) ^ ord($string[$c]));
            $c++;
        }
        if($this->base64) $out = base64_encode($out);
        return $out;
    }
    function decrypt($string) {
        if($this->base64) $string = base64_decode($string);
        $tmp_iv = substr($string,0,$this->hash_length);
        $string = substr($string,$this->hash_length,strlen($string) - $this->hash_length);
        $iv = $out = '';
        for($c=0;$c < $this->hash_length;$c++) {
            $iv .= chr(ord($tmp_iv[$c]) ^ ord($this->hash_key[$c]));
        }
        $key = $iv;
        $c = 0;
        while($c < strlen($string)) {
            if(($c != 0) and ($c % $this->hash_length == 0)) {
                $key = $this->_hash($key . substr($out,$c - $this->hash_length,$this->hash_length));
            }
            $out .= chr(ord($key[$c % $this->hash_length]) ^ ord($string[$c]));
            $c++;
        }
        return $out;
    }
    function _hash($string) {
        if(function_exists('sha1')) {
            $hash = sha1($string);
        } else {
            $hash = md5($string);
        }
        $out ='';
        for($c=0;$c<strlen($hash);$c+=2) {
            $out .= $this->_hex2chr($hash[$c] . $hash[$c+1]);
        }
        return $out;
    }
    function _generate_iv() {
        srand ((double)microtime()*1000000);
        $iv  = $this->salt;
        $iv .= rand(0,getrandmax());
        $iv .= serialize($GLOBALS);
        return $this->_hash($iv);
    }
    function _hex2chr($num) {
        return chr(hexdec($num));
    }
}
?>
