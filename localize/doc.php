<?
class doc extends controller implements idocumentinfo
{
	protected
		$normal = array('title', 'doc_description', 'keywords');
	public
		function initialize()
		{
			$title = array(
				'name' => 'title',
				'label' => g('Titlul paginii'),
				'id' => 'doctitle'
			);
			$doc_description = array(
				'name' => 'doc_description',
				'label' => g('Descrierea paginii'),
				'id' => 'docdescription'
			);
			$keywords = array(
				'name' => 'keywords',
				'label' => g('Cuvinte cheie'),
				'id' => 'dockeywords'
			);
			$this->setAttributes(compact('title', 'doc_description', 'keywords'));
		}
	public function selectDocumentInfo($path, $lang)
	{
		$this->select('*', 'WHERE id_doc_path = ? AND lang = ?', $path, $lang);
		if (!$this->selected())
		{
			$this->insert(array('id_doc_path' => $path, 'lang' => $lang));
			$this->selectDocumentInfo($path, $lang);
		}
	}
	public function getDocTitle()
	{
		return $this->title;
	}
	public function getDocDescription()
	{
		return $this->doc_description;
	}
	public function getDocKeywords()
	{
		return $this->keywords;
	}
	public function insertUpdateDocument(array $param)
	{
			 $this->update($param);
	}
	public function displayEditDocForm($lang)
	{
		if ($this->selected())
		{
			$editPage = new form(null, array('id' => 'editdoc'), $this->cursor(), 'insertUpdateDocument');
			$editPage->hidden('lang', $lang);
			$editPage->hidden('id_doc_path', $this->id_doc_path);
			$editPage->display();
			$editPage->button(g('Salveaza'));
			unset($editPage);
		}
		else
			new p('No document selected for editing!');
	}
}
?>
