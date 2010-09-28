<?php

class App_Model_ExpenseTotal extends Standard_Model
{
	protected $_dbTable = "App_Model_DbTable_ExpenseTotals";
	
	protected $_columns = array(
		'id' => 'int',
		'expense_id' => 'int',
		'end_date' => 'timestamp',
		'total_allocated' => 'float',
		'total_spent' => 'float',
		);
		
	public function fetchLastByExpense($expense, $end = null)
	{
		if(empty($expense)) return array();
		
		if(is_array($expense))
		{
			$expense_id = array();
			foreach($expense as $item)
			{
				if($item instanceof App_Model_Expense)
					$expense_id[] = $item->id;
			}
		}
		elseif($expense instanceof App_Model_Expense)
		{
			$expense_id = $expense->id;
		}
		$table = $this->getDbTable();
		$rows = $table->fetchLastByExpenseId($expense_id, $end);
		$totals = array();
		foreach($rows as $row)
		{
			$total = new self();
			$total->loadFromDb($row);
			$totals[$total->expense_id] = $total;
		}
		
		return $totals;
	}
	
	public function updateTotals($expense, $fromDate)
	{
		/*
		calculate all ends after fromDate up to the one after now
		loop through endDates and calculate the total_allocations and total_spent for this expense_id
		update/insert the entries in the expense_totals table using buffering and bulk insert
		*/
		$user = Zend_Auth::getInstance()->getIdentity();
		$income = new App_Model_Income();
		$incomes = $income->fetchByUser($user);
		$endDates = array();
		foreach($incomes as $income)
		{
			if($income->date >= $fromDate)
			{
				$endDates[] = $income->date;
			}
		}
		
		if(empty($endDates))
		{
			$endDates[] = strtotime(strftime("%m/1/%y", $fromDate));
		}
		
		$allocation = new App_Model_Allocation();
		$transaction = new App_Model_Transaction();
		$this->buffer();
		foreach($endDates as $date)
		{
			$this->id = null;
			$this->expense_id = $expense->id;
			$this->end_date = $date;
			$this->total_allocated = $allocation->total($date, null, $expense);
			$this->total_spent = $transaction->total($date, null, $expense);
			$this->save();
		}
		return $this->flush();
	}

	public function fetchLastWithBalance($end = null)
	{
		$user = Zend_Auth::getInstance()->getIdentity();
		$table = $this->getDbTable();
		$rows = $table->fetchLastByUserId($user->id, $end, true);
		$totals = array();
		foreach($rows as $row)
		{
			$total = new self();
			$total->loadFromDb($row);
			$totals[$total->expense_id] = $total;
		}
		
		return $totals;
	}
}
