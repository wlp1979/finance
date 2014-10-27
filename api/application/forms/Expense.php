<?php

class App_Form_Expense extends Standard_Form
{
	public function init()
	{
		$this->setName('expense');

		$this->addElement('text', 'name', array(
			'label' => 'Name',
			'required' => true,
			'filters' => array('StringTrim'),
			));

		$this->addElement('select', 'category_id', array(
			'label' => 'Category',
			'required' => true,
			));

		$this->addElement('text', 'day_due', array(
			'label' => 'Monthly Due Date',
			'filters' => array('StringTrim'),
			));

		$this->addElement('radio', 'auto_pay', array(
			'label' => 'Automatic Payment',
			'multiOptions' => array(0 => 'No', 1 => 'Yes'),
			));

		$this->addElement('radio', 'summary', array(
			'label' => 'Show on Summary Tab',
			'multiOptions' => array(0 => 'No', 1 => 'Yes'),
			));

		$this->addElement('radio', 'auto_hide', array(
			'label' => 'Hide if balance is zero',
			'multiOptions' => array(0 => 'No', 1 => 'Yes'),
			));
	}
}
