<?php

class App_Model_Expense extends Standard_Model
{
	protected $_dbTable = "App_Model_DbTable_Expenses";
	
	protected $_columns = array(
		'id' => 'int',
		'user_id' => 'int',
		'name' => 'string',
		'day_due' => 'int',
		'auto_pay' => 'int',
		'summary' => 'int',
		'category_id' => 'int',
		);
	
	public function totalSpent($start, $end)
	{
		$transaction = new App_Model_Transaction();
		return $transaction->total($start, $end, array($this));
	}
	
	public function fetchSummary($start, $end)
	{
		$user = Zend_Auth::getInstance()->getIdentity();
		$table = $this->getDbTable();
		$rows = $table->fetchSummary($user->id, $start, $end);
		$expenses = array();
		foreach($rows as $row)
		{
			$expense = new self();
			$expense->loadFromDb($row);
			$expenses[$expense->id] = $expense;
		}
		
		return $expenses;
	}
	
	public function updateTotals($fromDate)
	{
		$expenseTotal = new App_Model_ExpenseTotal();
		return $expenseTotal->updateTotals($this, $fromDate);
	}
}