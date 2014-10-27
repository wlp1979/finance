<?php

class App_Form_Category extends Standard_Form
{
	public function init()
	{
		$this->setName('category');

        $this->addElement('text', 'name', array(
            'label' => 'Name',
            'required' => true,
            'filters' => array('StringTrim'),
            ));
	}
}