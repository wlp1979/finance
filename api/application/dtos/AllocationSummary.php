<?php

class App_Dto_AllocationSummary extends App_Dto_Abstract {
	public $total;

	public $expenses = array();

	public function __construct(App_Dto_AllocationSummaryRecord $total) {
		$this->total = $total;
	}

	public function addExpenseRecord(App_Dto_AllocationSummaryRecord $expense) {
		$this->expenses[] = $expense;
	}
}