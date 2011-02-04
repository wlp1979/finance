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
		'auto_hide' => 'int',
		);
	
	public function totalSpent($start, $end)
	{
		$transaction = new App_Model_Transaction();
		return $transaction->total($start, $end, array($this));
	}
	
	public function fetchSummary()
	{
		$user = Zend_Auth::getInstance()->getIdentity();
		$table = $this->getDbTable();
		$rows = $table->fetchSummary($user->id);
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
	
	public function formOptions($user)
	{
		$expenses = $this->fetchByUser($user);
		$options = array();
		foreach($expenses as $expense)
		{
			$options[$expense->id] = $expense->name;
		}
		asort($options);
		return $options;
	}
	
	public function getAverages($expenses, $start)
	{
		$transaction = new App_Model_Transaction();
		$totals = $transaction->total($start, null, $expenses, true);
		$averages = array();
		foreach($totals as $row)
		{
			$months = $this->_countMonths($row['start'], $start);
			$averages[$row['expense_id']] = $row['total'] / $months;
		}
		
		return $averages;
	}
	
	public function fetchVisible($expense_ids = null)
	{
		$user = Zend_Auth::getInstance()->getIdentity();
		$table = $this->getDbTable();
		$rows = $table->fetchVisible($user->id, $expense_ids);
		$expenses = array();
		foreach($rows as $row)
		{
			$expense = new self();
			$expense->loadFromDb($row);
			$expenses[$expense->id] = $expense;
		}
		
		return $expenses;
	}
}