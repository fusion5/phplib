<?
class content extends controller implements icontent
{
	protected
		$normal = array('translation', 'type');
	private
		$contentArray;
	public
		function initialize()
		{
			$translation = array(
				'name' => 'translation',
				'label' => '',
				'id' => 'translationfield'
			);
			$type = array(
				'name' => 'type',
				'id' => 'typefield',
				'labels' => array(
					'html' => 'Save as formatted text',
					'plain' => 'Save as simple text'
				)
			);
			$this->setAttributes(compact('translation', 'type'));
			if ($this->contentArray == null)
				$this->populateContentArray();
		}
	private
		function populateContentArray()
		{
			$this->select('*');
			foreach($this as $content)
				$this->contentArray[$content->id_content_identif][$content->lang] = $content;
		}
	public 
		function selectContent($identif, $lang, path $docPath = null)
		{
			if ($this->contentArray == null)
				$this->populateContentArray();
			$this->reset();
			$length = $this->fields['id_content_identif']->getLength();
			$identif = trim($identif);
			$identif = substr($identif, 0, $length);
			$identif = htmlspecialchars($identif);
			if (empty($identif))
				throw new errors('The identifier must have a value.');
			if (array_key_exists($identif, $this->contentArray) && array_key_exists($lang, $this->contentArray[$identif]))
				$this->result[] = $this->contentArray[$identif][$lang]->getAssoc();
		}
	public 
		function getTranslation()
		{
			return htmlspecialchars_decode($this->translation);
		}
	public 
		function addTranslation($identif, $lang, $translation = null, path $docPath = null)
		{
			$insert = array(
				'id_content_identif' => $identif,
				'lang' => $lang,
				'translation' => $translation,
			);
			if ($docPath != null)
				$insert['id_doc_path'] = $docPath->getPath();
			$inserted = $this->insert($insert);
			$this->select('*', array('id_content_identif' => $inserted['id_content_identif']));
			$this->contentArray[$identif][$lang] = $this->cursor();
		}
	public
		function exportAllContent()
		{
			$supportedLanguages = array('en', 'ro', 'de');
			print "Denumirea textului	Traducere Engleza	Traducere Romana	Traducere Germana" . CRLF;
			foreach($this->contentArray as $identif => $languages)
			{
				print htmlspecialchars_decode($identif) . "\t";
				foreach ($supportedLanguages as $lang)
				{
					if (isset($languages[$lang]))
						print htmlspecialchars_decode(preg_replace('/[\v	]+/','',$languages[$lang]->translation));
					print "\t"; 
				}
				print CRLF;
			}
		}
	public
		function displayEditContentForm($id_content_identif, $language)
		{
			if ($id_content_identif != null)
			{
				$this->select('*','WHERE (STRCMP(id_content_identif, ?) = 0) AND lang = ?', $id_content_identif, $language);
				if ($this->selected())
				{
					new p('Translating the text <em>'. htmlspecialchars($id_content_identif).'</em> in the language <em>'.$language.'</em>');
					$editText = new form(null, array('id' => 'editidentif'), $this->cursor(), 'selectUpdate');
					$editText->button('Save Translation');
					$editText->hidden('id_content_identif', $id_content_identif);
					$editText->hidden('lang', $language);
					$editText->display();
					$editText->button('Save Translation');
					unset($editText);
					$p = new p();
					$deleteText = new state(state()->getPath(), state()->getParamStr());
					$deleteText->setCallback(new callback($this, 'delete'));
					$deleteText->setPostParams(array('id_content_identif'=>$id_content_identif));
					new a($deleteText, array(
						'caption' => 'Delete',
						'id' => 'delete_'.$id_content_identif.'_'.$language,
						'onclick' => 'return confirm(\'The text *'.htmlspecialchars($id_content_identif).'* is about to be deleted! Select OK if you are sure you want to delete it or click Cancel to keep it\')'
					));
					print ' - Permanently remove this text from the site in all languages.';
					unset ($p);
				}
				else
					new p('The text was deleted!');
			}
		}
}
?>
