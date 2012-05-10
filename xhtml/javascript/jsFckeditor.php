<?
class jsFckeditor extends jshelper
{
	public 
		function renderJsInstance()
		{
			$configuration = first(&$this->attributes['configuration'], 'beopenconfig.js');
			?><script type="text/javascript">
				var oFCKeditor = new FCKeditor( '<?=$this->attributes['name']?>' ) ;
				oFCKeditor.BasePath = "<?=javascript::getJsLibPath()?>dom/fckeditor/" ;
				<?if (isset($this->attributes['height'])):?>
				oFCKeditor.Height	= '<?=$this->attributes['height']?>';
				<?endif?>
				<?if (isset($this->attributes['width'])):?>
				oFCKeditor.Width = '<?=$this->attributes['width']?>';
				<?endif?>
				oFCKeditor.EnableSafari	= true;
				oFCKeditor.Config['CustomConfigurationsPath'] = '<?=$configuration?>' ;		
				oFCKeditor.ToolbarSet = 'BeOpenCustom';
				oFCKeditor.ReplaceTextarea();
			</script><?
		}
}
?>
