<?
class popupdocument extends document
{
	protected
		$docType = "xhtml";
	public		
		function setType($p) 
		{
			$this->docType = "xhtml";
		}
	protected
		function display_header($file) // Afiseaza headerul documentului	
		{
			$fileinfo = pathinfo($file);
			$file = $fileinfo['dirname'] . '/' . $fileinfo['filename'] . '_popup' . '.' . $fileinfo['extension'];
			parent::display_header($file);
		}
	protected 	
		function display_footer($file) // Afiseaza footerul documentului	
		{
			$fileinfo = pathinfo($file);
			$file = $fileinfo['dirname'] . '/' . $fileinfo['filename'] . '_popup' . '.' . $fileinfo['extension'];
			parent::display_footer($file);
		}
	protected
		function perform() 
		{
			$this->prePerform();
			$headerFile = './docres/headers/'.$this->docType.'_header.php';
			$footerFile = './docres/footers/'.$this->docType.'_footer.php';
			if (!is_file($headerFile))
				throw new httpException(404, 'Please create a header file for ' . $this->docType . ' documents');
			if (!is_file($footerFile))
				throw new httpException(404, 'Please create a footer file for ' . $this->docType . ' documents');
			$this->display_header($headerFile);
			$this->popup_content();
			$this->display();
			$this->display_footer($footerFile);
		}
	protected	
		function saveCloseButtons($f)
		{
			$f->button(g('Confirma'), array('name' => 'ok-close'));
			$f->button(g('Anuleaza operatia'), array('name' => 'close'));
		}
	protected
		function popup_content()
		{
			if (!(result() instanceof errors)) 
			{
				if (isset($this->param['ok-close']) || isset($this->param['close'])) 
				{
					?><script type="text/javascript">
							$openerHash = "<?=$this->getParam('hash')?>";
							if (opener.location.hash != $openerHash)
								opener.location.hash = $openerHash;
							<?
							if ($this->getParam('refresh')) 
							{	
								?>
								opener.location.reload();
								if (opener.location.hash != $openerHash)
									opener.location.hash = $openerHash;
								<?
							}
							?>
							window.close();<? 
					?></script><?
				}
			}
		}
}
?>
