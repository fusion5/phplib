<?
class lang
{
	public
		$appended = array();
	public
		$langentry = array();
	public
		function __construct($file = null)
		{
			if ($file) $this->open($file);
		}
	public
		function reset()
		{
			$this->langentry = array();
		}	
	public
		function add($entry)
		{
			$existing = $this->search($entry);
			if ($existing)
			{
				if ($entry->getComment())	
					$existing->copy($entry);
				else
					$existing->setValue($entry->getValue(true));	
			}
			else
				$this->langentry[$entry->identif()] = $entry;
		}
	public
		function merge($entry)
		{
			$existing = $this->search($entry);
			if (!$existing)
				$this->langentry[$entry->identif()] = $entry;
		}
	public
		function getall()
		{
			$get = '';
			foreach($this->langentry as $langentry)
			{
				$get .= $langentry;
			}
			return $get;
		}	
	public
		function saveall($textfile)
		{
			assertDir(dirname($textfile));
			file_put_contents($textfile, $this->getall());
		}
	public
		function open($textfile)
		{
			if (is_file($textfile))
			{
				$appended[] = $textfile;
				$content = file_get_contents($textfile);
				$this->parse($content);
			}
		}
	public
		function parse($content)
		{
			preg_match_all('|<!--.*-->|Ums', $content, $entries, PREG_OFFSET_CAPTURE);
			$entries = $entries[0];
			for ($i = 0; $i < count($entries); $i++)
			{
				$offset = $entries[$i][1];
				if (isset($entries[$i+1][1]))
					$nextOffset = $entries[$i+1][1];
				else
					$nextOffset = strlen($content);
				$slice = substr($content, $offset, $nextOffset - $offset);
				$new = new langentry($slice, $offset);
				$this->langentry[$new->identif()] = $new;
			}
		}
	public
		function search(langentry $entry)
		{
			if (isset($this->langentry[$entry->identif()]))
				return $this->langentry[$entry->identif()];
			else
				return null;
		}
	public
		function getPhrase($label)
		{
			$search = new langentry();
			$search->setLabel($label);
			$return = $this->search($search);
			if ($return == null) 
				return $search;
			else 
				return $return;
		}
}
?>
