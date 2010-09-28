<?php

class App_Model_Allocation extends Standard_Model
{
	protected $_dbTable = "App_Model_DbTable_Allocations";
	
	protected $_columns = array(
		'income_id' => 'int',
		'expense_id' => 'int',
		'amount' => 'float',
		);

	public function save()
	{
		$table = $this->getDbTable();
		$data = $this->_data;
		$id = $table->insert($data);
		$expense = $this->getExpense();
		$income = $this->getIncome();
		$expense->updateTotals($income->date);
		return $this;
	}
	
	public function total($end, $start = null, $expenses = null)
	{
		$user = Zend_Auth::getInstance()->getIdentity();
		$table = $this->getDbTable();
		$expense_id = null;
		if(!empty($expenses))
		{
			if(is_array($expenses))
			{
				$expense_id = array();
				foreach($expenses as $expense)
				{
					$expense_id[] = $expense->id;
				}
			}
			else
			{
				$expense_id = $expenses->id;
			}
		}
		
		return $table->total($user->id, $end, $start, $expense_id);
	}
	
	public function totalByIncome(App_Model_Income $income)
	{
		$table = $this->getDbTable();
		return $table->totalByIncomeId($income->id);
	}
	
	public function fetchByExpenseAndIncome($expense, $income)
	{
		if(empty($expense))
		{
			return array();
		}
		else if(is_array($expense))
		{
			$expense_id = array();
			foreach($expense as $item)
			{
				$expense_id[] = $item->id;
			}
		}
		else
		{
			$expense_id = $expense->id;
		}
		
		if(empty($income))
		{
			return array();
		}
		else if(is_array($income))
		{
			$income_id = array();
			foreach($income as $item)
			{
				$income_id[] = $item->id;
			}
		}
		else
		{
			$income_id = $income->id;
		}
		
		$table = $this->getDbTable();
		$rows = $table->fetchByExpenseIdAndIncomeId($expense_id, $income_id);
		$allocations = array();
		foreach($rows as $row)
		{
			$allocation = new self();
			$allocation->loadFromDb($row);
			$allocations[$row->expense_id][$row->income_id] = $allocation;
		}
		
		return $allocations;
	}
	
	public function getExpense()
	{
		$expense = new App_Model_Expense();
		return $expense->find($this->expense_id);
	}

	public function getIncome()
	{
		$income = new App_Model_Income();
		return $income->find($this->income_id);
	}
	
	public function fetchByIncome($income)
	{
		if(empty($income))
		{
			return array();
		}
		
		else if(is_array($income))
		{
			$income_id = array();
			foreach($income as $item)
			{
				$income_id[] = $item->id;
			}
		}
		else
		{
			$income_id = $income->id;
		}
		
		$table = $this->getDbTable();
		$rows = $table->fetchByIncomeId($income_id);
		$allocations = array();
		foreach($rows as $row)
		{
			$allocation = new self();
			$allocation->loadFromDb($row);
			$allocations[$row->expense_id][$row->income_id] = $allocation;
		}
		
		return $allocations;
	}
	
	public function find($income_id, $expense_id)
	{
		$table = $this->getDbTable();
		$rows = $table->find($income_id, $expense_id);
		if(count($rows) < 1)
		{
			return false;
		}
		
		$row = $rows->current();
		return $this->loadFromDb($row);
	}

	public function delete()
	{
		$table = $this->getDbTable();
		$where = array(
			'expense_id = ?' => $this->expense_id,
			'income_id = ?' => $this->income_id,
			);
		
		$table->delete($where);
		$expense = $this->getExpense();
		$income = $this->getIncome();
		$expense->updateTotals($income->date);
		return true;
	}	
}