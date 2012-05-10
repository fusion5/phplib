<?
class langentry
{
	private
		$comment = "";
	private
		$label= "";
	private
		$value = null;
	private
		$offset = 0;
	public
		function __construct($text = '', $offset = 0)
		{
			$this->offset = $offset;
			if ($text)		
				$this->parse($text);
		}
	public
		function setComment($val)
		{
			$this->comment = $val;
		}
	public
		function copy($entry)
		{
			$this->label = $entry->label;
			$this->comment = $entry->comment;
			$this->value = $entry->value;
		}
	public
		function getComment($notrim = false)
		{
			if ($notrim)
				return $this->comment;
			else
				return trim($this->comment);
		}
	public
		function setLabel($val)
		{
			$this->label = $val;
		}
	public
		function getLabel($notrim = false)
		{
			if ($notrim)
				return $this->label;
			else
				return trim($this->label);
		}
	public
		function setValue($val)
		{
			$this->value = $val;
		}
	public
		function getValue($notrim = false)
		{
			if ($this->value)
			{
				if ($notrim) return $this->value;
				else return trim($this->value);
			}
			else
				return null;
		}
	public
		function parse($text)
		{
			$text = trim($text);
			if (preg_match("|<!--(.*?)-->(.*)|ms", $text, $matches) !== false)
			{
				$header = $matches[1];
				$this->setValue($matches[2]);
				if (preg_match("|#|ms", $header))
				{
					$header = split("#", $header);
					$this->setComment(array_shift($header));
					$this->setLabel(join("#", $header));
				}
				else
					$this->setLabel($header);
			}
			else
				throw new errors("Error parsing language file - parse error");
		}	
	public
		function __toString()
		{
			$retval = "<!--";
			if ($this->comment)
				$retval .= $this->comment."#";
			$retval .= $this->label;
			$retval .= "-->".$this->value."\r\n\r\n";
			return $retval;
		}
	public
		function eq(lang $entry)
		{
			return $this->identif() == $entry->identif();
		}
	public
		function identif()
		{
			$identif = strtolower($this->getLabel());
			$identif = preg_replace('/[^a-z]/m', '', $identif);
			return $identif;
		}
}
?>
