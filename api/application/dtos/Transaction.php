<?php

class App_Dto_Transaction extends App_Dto_Abstract {
	
	protected $id;
	protected $date;
	protected $checkNum;
	protected $description;
	protected $amount;
	protected $expense;

	public function __construct($id, $date, $checkNum, $description, $amount, App_Dto_Expense $expense) {
		$this->id = $id;
		$this->date = $this->formatDate($date);
		$this->checkNum = (empty($checkNum)) ? null : $checkNum;
		$this->description = $description;
		$this->amount = $amount;
		$this->expense = $expense;
	}

	public static function fromTransactionModel(App_Model_Transaction $transaction, App_Model_Expense $expense) {
		return new self(
			$transaction->id,
			$transaction->date,
			$transaction->check_num,
			$transaction->description,
			-$transaction->amount,
			App_Dto_Expense::fromExpenseModel($expense)
		);
	}

	private function formatDate($date) {
		if(is_string($date) && is_numeric($date)) {
			$date = "@{$date}";
		}
		try {
			$dateTime = new DateTime($date);
			return $dateTime->format(DateTime::ISO8601);
		} catch (Exception $e) {
			error_log($e);
			return $date;
		}
	}
}
