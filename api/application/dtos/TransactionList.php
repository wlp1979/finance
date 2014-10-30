<?php

class App_Dto_TransactionList extends App_Dto_Abstract {
	private $transactions = array();

	public function addTransaction(App_Dto_Transaction $transaction) {
		$this->transactions[] = $transaction;
	}

	public function jsonSerialize() {
		return $this->transactions;
	}
}