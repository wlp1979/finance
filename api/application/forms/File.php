<?php

class App_Form_File extends Standard_Form
{
	public function init()
	{
		$this->setAttrib('enctype', 'multipart/form-data');

		$element = new Zend_Form_Element_File('file');
		$element->setLabel('File')
				->setDestination('/tmp')
				->addValidator('Count', false, 1)
				->addValidator('Size', false, 2097152)
				->setMaxFileSize(2097152);
		$this->addElement($element, 'file');	
	}
}
