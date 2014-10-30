<?php

class App_Dto_Expense extends App_Dto_Abstract {
	
	protected $id;
	protected $name;

	public function __construct($id, $name) {
		$this->id = $id;
		$this->name = $name;
	}

	public static function fromExpenseModel(App_Model_Expense $expense) {
		return new self(
			$expense->id,
			$expense->name
		);
	}

}
