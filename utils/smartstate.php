<?
class smartstate extends state
{
	public
		function __construct($path = '/', $source)
		{
			parent::__construct($path);
			if ($source instanceof idataaccess)
				$this->readFromIDataAccess($source);
			else
				new p('Internal error: Unkown smartstate object');
		}
	private
		function readFromIDataAccess(idataaccess $source)
		{
			$text = $source->__toString();
			$encoding = mb_detect_encoding($text);
			$text = mb_strtolower($text, $encoding);
			$r["unicode"]['tz']= "\xC5\xA3";
			$r["unicode"]['Tz']= "\xC5\xA2";
			$r["unicode"]['AM']= "\xC3\x82";
			$r["unicode"]['aM']= "\xC3\xA2";
			$r["unicode"]['IM']= "\xC3\x8E";
			$r["unicode"]['iM']= "\xC3\xAE";
			$r["unicode"]['A_']= "\xC4\x82";
			$r["unicode"]['a_']= "\xC4\x83";
			$r["unicode"]['Sh']= "\xC5\x9E";
			$r["unicode"]['sh']= "\xC5\x9F";
			$r["st"]['tz']="t";
			$r["st"]['Tz']="T";
			$r["st"]['AM']="A";
			$r["st"]['aM']="a";
			$r["st"]['IM']="I";
			$r["st"]['iM']="i";
			$r["st"]['A_']="A";
			$r["st"]['a_']="a";
			$r["st"]['Sh']="S";
			$r["st"]['sh']="s";
			$text = str_replace($r['unicode'], $r['st'], $text);
			$text = preg_replace('/[^a-zA-Z0-9 _.]/', '', $text);
			$this->setParamStr($source->idCondition().'&'.$text);
		}
}
?>
