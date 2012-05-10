<?
class cursvalutar
{
	private
		$moneda = 'EUR';
	private
		$value = 1;
	private
		$url_bnr;
	private
		$cache_file;
	private
		$xmlCurs;
	public
		function __construct($url_bnr)
		{
			if (!defined('CACHE_DIR'))
				define('CACHE_DIR', './.cache/');
			$this->url_bnr = $url_bnr;
			$this->cache_file = CACHE_DIR . 'nbrfxrates.xml';
			if (!is_file($this->cache_file) || filemtime($this->cache_file) < mktime(0, 0, 0))
				$this->preluareFisierBnr();
			$this->xmlCurs = new DOMDocument();
			$this->xmlCurs->preserveWhiteSpace = false;
			if (@$this->xmlCurs->load($this->cache_file))
			{
				$cursuriNode = $this->xmlCurs->firstChild->childNodes->item(1)->childNodes->item(2);
				foreach($cursuriNode->childNodes as $node)
					$node->setIdAttribute('currency', true);
			}
			else
				$this->xmlCurs = null;
		}
	private
		function preluareFisierBnr()
		{
			assertDir(CACHE_DIR);
			$curs = @file_get_contents($this->url_bnr);
			if (strlen($curs) > 0)
				file_put_contents($this->cache_file, $curs);
		}
	public
		function getValue($moneda = 'EUR')
		{
			if ($this->xmlCurs)
			{
				$nodCurs = $this->xmlCurs->getElementById($moneda);
				return $nodCurs->textContent;
			}
			return false;
		}
}
?>
