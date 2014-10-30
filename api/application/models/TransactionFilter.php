<?php

class App_Model_TransactionFilter {
	private $user;
	private $expense;
	private $category;
	private $startDate;
	private $endDate;
	private $minCheckNum;
	private $maxCheckNum;
	private $descriptionQuery;

	public function __construct(App_Model_User $user) {
		$this->setUser($user);
	}

	public function setUser(App_Model_User $user) {
		$this->user = $user;
	}

	public function getUser() {
		return $this->user;
	}

	public function setExpense(App_Model_Expense $expense) {
		$this->expense = $expense;
	}

	public function getExpense() {
		return $this->expense;
	}

	public function setCategory(App_Model_Category $category) {
		$this->category = $category;
	}

	public function getCategory() {
		return $this->category;
	}

	public function setStartDate(DateTime $start) {
		if ($this->getEndDate() instanceof DateTime && $this->getEndDate()->getTimestamp() < $start) {
			//throw an exception because start has to be less than end
		}

		$this->startDate = $start;
	}

	public function getStartDate() {
		return $this->startDate;
	}

	public function setEndDate(DateTime $end) {
		if ($this->getStartDate() instanceof DateTime && $this->getStartDate()->getTimestamp() > $end) {
			//throw an exception because start has to be less than end
		}
		
		$this->endDate = $end;
	}

	public function getEndDate() {
		return $this->endDate;
	}

	public function setMinCheckNum($minCheckNum) {
		if ($this->getMaxCheckNum() !== null && $minCheckNum > $this->getMaxCheckNum()) {
			//throw an exception because min has to be greater than max
		} 

		$this->minCheckNum = $minCheckNum;
	}

	public function getMinCheckNum() {
		return $this->minCheckNum;
	}

	public function setMaxCheckNum($maxCheckNum) {
		if ($this->getMinCheckNum() !== null && $maxCheckNum < $this->getMinCheckNum()) {
			//throw an exception because min has to be greater than max
		}

		if ($this->getMinCheckNum() === null && $maxCheckNum > 1) {
			$this->setMinCheckNum(1);
		}

		$this->maxCheckNum = $maxCheckNum;
	}

	public function getMaxCheckNum() {
		return $this->maxCheckNum;
	}

	public function setDescriptionQuery($query) {
		$this->descriptionQuery = $query;
	}

	public function getDescriptionQuery() {
		return $this->descriptionQuery;
	}
}
