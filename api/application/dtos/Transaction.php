<?php

class App_Dto_Transaction extends App_Dto_Abstract {
	
	protected $id;
	protected $date;
	protected $checkNum;
	protected $description;
	protected $amount;
	protected $expenseId;

	public function __construct($id, $date, $checkNum, $description, $amount, $expenseId) {
		$this->id = $id;
		$this->date = $this->formatDate($date);
		$this->checkNum = (empty($checkNum)) ? null : intval($checkNum);
		$this->description = $description;
		$this->amount = $amount;
		$this->expenseId = $expenseId;
	}

	public static function fromTransactionModel(App_Model_Transaction $transaction) {
		return new self(
			$transaction->id,
			$transaction->date,
			$transaction->check_num,
			$transaction->description,
			-$transaction->amount,
			$transaction->expense_id
		);
	}

	private function formatDate($date) {
		if((is_string($date) && is_numeric($date)) || is_int($date)) {
			$date = "@{$date}";
		}
		try {
			$dateTime = new DateTime($date);
			return $dateTime->format('Y-m-d');
		} catch (Exception $e) {
			error_log($e);
			return $date;
		}
	}
}
