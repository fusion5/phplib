<?
define('SUCCES_FILE_UPLOADED', 107);
define('ERR_FILE_EXISTS', 106);
define('ERR_NEWFILE_EXISTS', 102);
define('ERR_REPORTED', 103);
define('ERR_FILE_REQUIRED', 104);
define('ERR_INVALID_FILE', 105);
class spUploadableField extends databasefield
{
	protected
		$result;
	protected
		$uploadable;
	public
		$itemclass = 'div';
	private
		$repository;
	private static
		$tmpRepository;
	private
		function getRepository()
		{
			if (isset($this->repository))
				return $this->repository;
			else
				if (isset($this->uploadable))
				{
					$callback = array($this->uploadable, 'getRepository');
					return $this->repository = call_user_func($callback);
				}
		}
	private
		function getTempRepository()
		{
			if(isset($this->tmpRepository))
				return $this->tmpRepository;
			else
			{
				return $this->tmpRepository = new filerepository(TMP_DIR);
			}
		}
	public
		function renderDatabaseField($attributes)
		{
			$attributes = $this->controlAttributes($attributes);
			$this->interpretResult($attributes['name'], $this->result, $attributes);
			$displayPreview = is_string($attributes['value']);
			if ($displayPreview)
			{
				$destinationFolder = first(&$this->destinationFolder, $this->dbo->dboName() . '/' . $this->name. '/');
				try
				{
					$this->renderPreview($destinationFolder . $attributes['value']);
				}
				catch(errors $e)
				{
					print $e->getMessage();
				}
			}
			$attributes['type'] = 'file';
			$attributesCopy = $attributes;
			$attributesCopy['name'] .= '[newfile]';
			if (!array_key_exists('td', $attributes))
				$p = new p();
			new input($attributesCopy);
			if ($displayPreview)
				new input(array(
					'type' => 'checkbox',
					'name' => $attributes['name'] . '[oldfile]',
					'value' => $attributes['value'],
					'label' => g('Keep the current file'),
					'checked' => true
				));
			if (isset($p))
				unset($p);
		}
	private
		function passResult($fieldName, actionresult $result)
		{
			new input(array(
				'name' => $fieldName . '[result]',
				'value' => $result->getString(),
				'type' => 'hidden'
			));
		}
	private
		function interpretResult($fieldName, $result = null, $parameters = null)	
		{
			if (isset($result) && $result instanceof actionresult)
			{
				$r = $result->get();
				$this->passResult($fieldName, $result);
				if ($result->is(ERR_FILE_REQUIRED))
					new p(g('You must select a file!'));
				if ($result->is(ERR_INVALID_FILE))
					new p(gf('You must select a valid file for this field: %s', $r));
				if ($result->is(ERR_FILE_EXISTS))
				{
					new p('There is already a file having the same name as the one you selected!');
					$fileName = $r['fileName'];
					$tempFile = $r['tempFile'];
					$destinationFolder = $r['destinationFolder'];
					new h2(g('The new file'));
					$image = new image($tempFile, array('no_output' => true));
					$image->setRepo($this->getTempRepository());
					$image->render();
					$p = new p();
					try
					{
						new input(array
						(
							'name' => $fieldName . '[decision]',
							'type' => 'radio',
							'value' => 'overwrite',
							'label' => g('Replace and use the new uploaded file'),
							'id' => 'overwrite_'.$fieldName,
							'checked' => true
						));
					}
					catch (errors $e)
					{
						trace($e);
					}
					unset ($p);
					new h2(g('The existing file'));
					$this->renderPreview($destinationFolder . $fileName);
					$p = new p();
					new input(array
					(
						'name' => $fieldName . '[decision]',
						'type' => 'radio',
						'value' => 'keep',
						'label' => g('Keep and use the old existing file'),
						'id' => 'keep_'.$fieldName,
						'checked' => false 
					));
					unset ($p);
					$p = new p();
					new input(array
					(
						'name' => $fieldName . '[decision]',
						'type' => 'radio',
						'value' => 'another',
						'label' => g('Choose another file'),
						'id' => 'another_'.$fieldName,
						'checked' => false 
					));
					unset ($p);
				}
			}
		}
	private
		function renderPreview($fileName)
		{
			if (isset($this->uploadable))
			{
				$p = new p();
				$callback = array($this->uploadable, 'renderPreview');
				call_user_func($callback, $fileName);
				unset($p);
			}
			else
				print (gf('No representation method specifed for %s', $fileName));
		}
	private
		function getPreviousResult($fileInfo)
		{
			$previousResult = null;
			if (isset($fileInfo['result']))
			{
				$previousResultString = $fileInfo['result'];
				$previousResult = actionresult::parse($previousResultString);
			}
			return $previousResult;
		}
	private
		function getdecision($fileInfo)
		{
			$decision = null;
			if (isset($fileInfo['decision']))
				$decision = $fileInfo['decision'];
			return $decision;
		}
	public
		function setValue($fileInfo)
		{
			$value = null;
			if (is_array($fileInfo))
			{
				$previousResult = $this->getPreviousResult($fileInfo);
				$decision = $this->getDecision($fileInfo);
				if ($fileInfo['name']['newfile'] || $previousResult)
				{
					$r = $this->process($fileInfo, $decision, $previousResult);
					$this->result = $r;
					if ($r)
					{
						if ($r->is(SUCCES_FILE_UPLOADED))
						{
							$success = $r->get();
							$value = $success['fileName'];
						}
						else
						{
							switch($r->getCode())
							{
								case UPLOAD_ERR_INI_SIZE:
									throw new errors(sprintf('The uploaded file exceeds the maximum allowed filesize (%s)', ini_get('upload_max_filesize')));
								case UPLOAD_ERR_FORM_SIZE:
									throw new errors('The uploaded file exceeds the maximum allowed filesize');
								case UPLOAD_ERR_PARTIAL:
									throw new errors('The uploaded file was only partially uploaded');
								case UPLOAD_ERR_NO_FILE:
									throw new errors('No file was uploaded');
								case UPLOAD_ERR_NO_TMP_DIR:
									throw new errors('Missing a temporary folder');
								case UPLOAD_ERR_CANT_WRITE:
									throw new errors('Failed to write file to disk');
								case UPLOAD_ERR_EXTENSION:
									throw new errors('File upload stopped by extension');
								case ERR_FILE_EXISTS:
									throw new errors('Naming conflict');
								case ERR_INVALID_FILE:
									throw new errors('Invalid file content');
								default:
									throw new errors('Unkown error');
							}
						}
						if ($r->is(ERR_FILE_EXISTS) && ($value == null))
						{
							$value = &$fileInfo['oldfile'];
						}
					}
				}
				else
				{
					$value = &$fileInfo['oldfile'];
				}
			}
			else
				$value = $fileInfo;
			parent::setValue($value);
		}
	private
		function process($fileInfo, $decision, $previousResult)
		{
			if (isset($fileInfo) && ($fileInfo['name']['newfile']))
			{
				if (isset($fileInfo) && isset($fileInfo['error']['newfile']) && $fileInfo['error']['newfile'] != UPLOAD_ERR_OK)
					return new actionresult($fileInfo['error']['newfile']);
				$dbo = $this->dbo;
				$dbo->readFields();
				$fileName = $fileInfo['name']['newfile']; // The filename (without the path)
				$tempFile = $fileInfo['tmp_name']['newfile']; // The temporary filename (with full path)
				if (isset($this->uploadable))
				{
					$check = $this->uploadable . '::checkFile';
					try
					{
						if (is_callable($check))
						{
							$info = pathinfo($fileName);
							call_user_func($check, $tempFile, &$info['extension']);
						}
					}
					catch(errors $e)
					{
						return new actionresult(ERR_INVALID_FILE, $e->getMessage());
					}
				}
				$destinationFolder = first(&$this->destinationFolder, $this->dbo->dboName() . '/' . $this->name. '/');
				$dbo->select('*', array($this->name => $fileName));
				if ($dbo->selected() && ($this->getRepository()->is_file($destinationFolder . $fileName)))
				{
					$this->getTempRepository()->upload($tempFile, $fileName, '/');
					return new actionresult 
					(
						ERR_FILE_EXISTS,
						array (
							'fileName' => $fileName,
							'destinationFolder' => $destinationFolder,
							'tempFile' => $fileName
						)
					);
				}
				else
				{
					$this->getRepository()->upload($tempFile, $fileName, $destinationFolder);
					return new actionresult 
					(
						SUCCES_FILE_UPLOADED,
						array(
							'fileName' => $fileName,
							'destinationFolder' => $destinationFolder
						)
					);
				}
			}
			else if ($previousResult)
			{
				if ($previousResult->is(SUCCES_FILE_UPLOADED))
					return $previousResult;
				if ($previousResult->is(ERR_FILE_EXISTS))
				{
					if ($decision)
					{
						if ($decision == 'overwrite')
						{
							$file = $previousResult->get();
							$fileName = $file['fileName'];
							$tempFile = $this->getTempRepository()->getHashedFile($file['tempFile']);
							$destinationFolder = $file['destinationFolder'];
							if (is_file($tempFile) && is_readable($tempFile))
							{
								$this->getRepository()->unlink($destinationFolder . $fileName);
								rename($tempFile, $this->getRepository()->getHashedFile($destinationFolder . $fileName));
							}
							return new actionresult(SUCCES_FILE_UPLOADED, array(
								'fileName' => $fileName,
								'destinationFolder' => $destinationFolder
							));
						}
						if ($decision == 'keep')
						{
							$previousResult->setCode(SUCCES_FILE_UPLOADED);
							return $previousResult;
						}
						if ($decision == 'another')
						{
						}
					}
					else
					{
						return $previousResult;
					}
				}
			}
		}
	public static
		function upload($tmpFile, $fileName, $destinationFolder)
		{
			trace('Deprecated spUploadable::upload() called');
			return $this->getRepository()->upload($tmpFile, $fileName, $destinationFolder);
		}
	public
		function getXMLSchemaPart()
		{
			$typeName = $this->type;
			if ($this->getLength())
				$typeName .= '_' . $this->getLength();
			return array 
			(
				$typeName.'Type' => 
				'<xsd:simpleType name="'.$typeName.'Type">'.
				'<xsd:restriction base="xsd:string">'.
				'<xsd:maxLength value="'.$this->getLength().'" />'.
				'</xsd:restriction>'.
				'</xsd:simpleType>',
				$typeName => '<xsd:element name="'.$typeName.'" type="tns:'.$typeName.'Type" />'
			);
		}
}
?>
