<?
class jsSelectrating extends jshelper
{
	public 
		function renderJsInstance()
		{
			$options = $this->attributes['options'];
			$thumbs = $this->attributes['thumbs'];
			$i = 0;
			if (count($thumbs))
			{
				$jsparam = array();
				for ($j = 1; $j <= 5; $j++) {
					$v['id_valoare'] = 6 - $j;
					$v['valoare_thumbnail'] = '';//ABS_URL.'img/images/star_active.png';//image::getRepository()->getHashedFile('valoare/valoare_thumbnail/'.$valoare_thumbnail);
					$jsparam[] = $v;				
				}
				$label = first(&$this->attributes['label'], '');
				$label = $label . ': ';
				$json = new json();
				?><script type="text/javascript">
					new SelectRating('<?=$this->attributes['id']?>', <?=$json->encode($jsparam)?>, '<?=$label?>');
				</script><?
			}
		}
}
?>
