<?
class dbwsdl
{
	private
		$db;
	private 
		$targetNamespace = 'TestAPI';
	private
		$serviceURL = '';
	private
		$name;
	public
		function __construct(db $db, $serviceURL, $targetNamespace = 'TestAPI', $name = 'wsdl')
		{
			$this->db = $db;
			$this->targetNamespace = $targetNamespace;
			$this->serviceURL = $serviceURL;
			$this->name = $name;
		}
	public
		function display()
		{
			$portName = $this->name . 'Port';
			$bindingName = $this->name . 'Binding';
			$xml  = $this->header();
			$xml .= $this->messagesPortTypesBindings($portName, $bindingName);
			$xml .= $this->service($this->name. 'Service', $bindingName, $this->serviceURL);
			$xml .= $this->footer();
			$document = new DOMDocument();
			$document->loadXML($xml);
			$document->formatOutput = true;
			print $document->saveXML();
		}
	private
		function service($serviceName, $portName, $url)
		{
			$r = '<wsdl:service name="'.$serviceName.'">';
			$r.= '<wsdl:port name="'.$serviceName.'Port" binding="tns:'.$portName.'">';
			$r.= '<soap:address location="'.$url.'" />';
			$r.= '</wsdl:port>';
			$r.= '</wsdl:service>';
			return $r;
		}
	private
		function messagesPortTypesBindings($portTypeName, $bindingName)
		{
			$types = '<wsdl:types><xsd:schema targetNamespace="'.$this->targetNamespace.'">';
			$messages = '';
			$portType = '<wsdl:portType name="'.$portTypeName.'">';
			$binding = '<wsdl:binding name="'.$bindingName.'" type="tns:'.$portTypeName.'"><soap:binding transport="http://schemas.xmlsoap.org/soap/http" />';
			$types .= 
				'<xsd:element name="ServerSideFault">'.
					'<xsd:complexType>'.
						'<xsd:sequence>'.
							'<xsd:element name="message" type="xsd:string" />'.
							'<xsd:element name="code" type="xsd:string" />'.
						'</xsd:sequence>'.
					'</xsd:complexType>'.
				'</xsd:element>';
			$messages .= '<wsdl:message name="ServerSideFault"><wsdl:part name="ServerSideFault" element="tns:ServerSideFault" /></wsdl:message>';
			$typesArray = array();
			foreach($this->db->objectNames as $objectName)
				if ($this->db->$objectName instanceof dbo)
				{
					$dboTypes = $this->db->$objectName->getXMLSchemaPart();
					if (count($dboTypes))
						foreach($dboTypes as $typeName => $typeXSL)
						{
							$typesArray[$typeName] = $typeXSL;
						}
					if ($this->db->$objectName instanceof controller)
					{
						$refl = new ReflectionClass($this->db->$objectName);
						$methods = $refl->getMethods();
						foreach($methods as $method)
						{
							$declaringClass = $method->getDeclaringClass()->getName();
							if ($method->isPublic() && $method->isUserDefined())
								if 
								(
									(!in_array ($declaringClass, abstractAccess::$ignoreClasses) || 
									(in_array  ($method->getName(), abstractAccess::$allowedMethods))) && 
									(!in_array ($method->getName(), abstractAccess::$disallowedMethods)) &&
									(strstr($method->getDocComment(), '@soap') !== FALSE) // Metoda marcata pentru soap
								)
								{
									$portType .= '<wsdl:operation name="'.$method->getName().'">';
									$binding  .= '<wsdl:operation name="'.$method->getName().'"><soap:operation soapAction="'.$method->getName().'" />';
									$comment = $method->getDocComment();
									$tags = array();
									preg_match_all('|@([\w$]*)\s*([\w$]*)\s*([\w$:]*)\s*$|m', $method->getDocComment(), $tags, PREG_SET_ORDER);
									foreach($tags as $tag)
									{
										$tagName = &$tag[1];
										$element = &$tag[2];
										switch($tagName)
										{
										case 'soap_element':
											$types 		.= '<xsd:element name="'.$method->getName().'Request" type="tns:'.$element.'Type" />';
											$messages .= '<wsdl:message name="'.$method->getName().'">'. 
																	 '<wsdl:part name="parameters" element="tns:'.$method->getName().'Request" />'.
																	 '</wsdl:message>';
											$portType .= '<wsdl:input message="tns:'.$method->getName().'" />';
											$binding  .= '<wsdl:input><soap:body use="literal" /></wsdl:input>';
											break;
										case 'soap_return_element':
											$types 		.= '<xsd:element name="'.$method->getName().'Response" type="tns:'.$element.'Type" />';
											$messages .= '<wsdl:message name="'.$method->getName().'Result">'.
																	 '<wsdl:part name="parameters" element="tns:'.$method->getName().'Response" />'.
																	 '</wsdl:message>';
											$portType .= '<wsdl:output message="tns:'.$method->getName().'Result" />';
											$portType .= '<wsdl:fault name="error" message="tns:ServerSideFault" />';
											$binding  .= '<wsdl:output><soap:body use="literal" /></wsdl:output>';
											$binding  .= '<wsdl:fault name="error"><soap:fault name="error" use="literal" /></wsdl:fault>';
											break;
										}
									}
									$portType .= '</wsdl:operation>';
									$binding  .= '</wsdl:operation>';
								}
						}
					}
				}
			foreach ($typesArray as $typeXSL)
				$types .= $typeXSL;
			$binding .= '</wsdl:binding>';
			$portType .= '</wsdl:portType>';
			$types .= '</xsd:schema></wsdl:types>';
			return $types . $messages . $portType . $binding;
		}
	private
		function types()
		{
			$return = '<wsdl:types>';
			$return .= '<xsd:schema targetNamespace="'.$this->targetNamespace.'">';
			$types = array();
			foreach($this->db->objectNames as $objectName)
				if ($this->db->$objectName instanceof dbo)
				{
					$dboTypes = $this->db->$objectName->getXMLSchemaPart();
					if (count($dboTypes))
						foreach($dboTypes as $typeName => $typeXSL)
						{
							$types[$typeName] = $typeXSL;
						}
				}
			foreach($types as $typeXSL)
				$return.= $typeXSL;
			$return.= 
			$return.= '';
			return $return;
		}
	private
		function header()
		{
			return
				'<?xml version ="1.0" encoding ="UTF-8" ?><wsdl:definitions targetNamespace="'.$this->targetNamespace.'" 
				  xmlns:tns="'.$this->targetNamespace.'"
					xmlns:xsd="http://www.w3.org/2001/XMLSchema"
					xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
					xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/"
					xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/"	
					>';
		}
	private
		function footer()
		{
			return
				'</wsdl:definitions>';
		}
}
?>
