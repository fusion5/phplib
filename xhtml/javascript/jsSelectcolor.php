<?
class jsSelectcolor extends jshelper
{
	public 
		function renderJsInstance()
		{
			$valoare = $this->db->valoare;
			$id_caracteristica = $this->attributes['id_caracteristica'];
			$options = $this->attributes['options'];
			$thumbs = $this->attributes['thumbs'];
			if (count($thumbs))
			{
				$jsparam = array();
				foreach($thumbs as $id_valoare => $valoare_thumbnail)
				{
					$v['id_valoare'] = $id_valoare;
					$v['valoare_thumbnail'] = ABS_URL.image::getRepository()->getHashedFile('valoare/valoare_thumbnail/'.$valoare_thumbnail);
					$jsparam[] = $v;
				}
				$label = first(&$this->attributes['caracteristica_nume'], '');
				$json = new json();
				?><script type="text/javascript">
					new SelectColor('<?=$this->attributes['id']?>', <?=$json->encode($jsparam)?>, '<?=$label?>');
				</script><?
			}
		}
}
?>
