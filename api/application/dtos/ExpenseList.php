<?php

class App_Dto_ExpenseList extends App_Dto_Abstract {
	private $expenses;

	public function addExpense(App_Dto_Expense $expense) {
		$this->expenses[] = $expense;
	}

	public function jsonSerialize() {
		return $this->expenses;
	}
}