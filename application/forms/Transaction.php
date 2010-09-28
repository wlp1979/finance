<?php

class App_Form_Transaction extends Standard_Form
{
	public function init()
	{
		$this->setName('transaction');

		$this->addElement('text', 'date', array(
			'label' => 'Date',
			'class' => 'datepicker',
			'required' => true,
			'filters' => array('StringTrim'),
			));

		$this->addElement('text', 'check', array(
			'label' => 'Check',
			'filters' => array('StringTrim'),
			));

		$this->addElement('text', 'description', array(
			'label' => 'Description',
			'required' => true,
			'filters' => array('StringTrim'),
			));

		$this->addElement('text', 'amount', array(
			'label' => 'Amount',
			'required' => true,
			'filters' => array('StringTrim'),
			));

		$this->addElement('select', 'expense_id', array(
			'label' => 'Expense',
			'required' => true,
			));
	}
}