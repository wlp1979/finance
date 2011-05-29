<?php

class App_Form_TransactionFilter extends Standard_Form
{
	public function init()
	{
		$this->setName('transaction_filter');

		$this->addElement('select', 'filter_expense_id', array(
			'label' => 'By Expense',
			'required' => false,
			'multiOptions' => array('' => 'None'),
			));
	}
}