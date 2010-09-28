<?php

class App_Form_Allocation extends Standard_Form
{
	public function init()
	{
		$this->setName('allocation');
		
		$this->addElement('text', 'income', array(
			'label' => 'From Income',
			'disable' => true,
			));

		$this->addElement('text', 'expense', array(
			'label' => 'To Expense',
			'disable' => true,
			));

        $this->addElement('text', 'amount', array(
            'label' => 'Amount',
            'required' => true,
            'filters' => array('StringTrim'),
            ));
	}
}