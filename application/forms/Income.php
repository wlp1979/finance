<?php

class App_Form_Income extends Standard_Form
{
	public function init()
	{
		$this->setName('income');

        $this->addElement('text', 'name', array(
            'label' => 'Name',
            'required' => true,
            'filters' => array('StringTrim'),
            ));
        
        $this->addElement('text', 'amount', array(
            'label' => 'Amount',
            'required' => true,
            'filters' => array('StringTrim'),
            ));
            
        $this->addElement('text', 'date', array(
            'label' => 'Date',
			'class' => 'datepicker',
            'required' => true,
            'filters' => array('StringTrim'),
            ));
	}
}