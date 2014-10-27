<?php

class App_Form_ExpenseChooser extends Standard_Form
{
	public function init()
	{
		$this->setName('expense_chooser');

		$this->addElement('select', 'expense_id', array(
			'label' => 'Expense',
			'required' => true,
			));
	}
}
