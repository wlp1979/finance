<?php

class App_Form_RecurringIncome extends Standard_Form
{
	public function init()
	{
		$this->setName('recurring_income');

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
        
        $this->addElement('select', 'recur_type', array(
            'label' => 'Recurrence Type',
            'required' => true,
            ));
        $this->getElement('recur_type')->addMultiOptions(App_Model_RecurringIncome::$recurTypes);
            
        $this->addElement('text', 'start_date', array(
            'label' => 'Start Date',
			'class' => 'datepicker',
            'required' => true,
            'filters' => array('StringTrim'),
            ));
        
        $this->addElement('text', 'end_date', array(
            'label' => 'End Date',
			'class' => 'datepicker',
            'filters' => array('StringTrim'),
            ));
	}
}