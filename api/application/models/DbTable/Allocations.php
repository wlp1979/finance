<?php

class App_Model_DbTable_Allocations extends Standard_Db_JoinTable
{
    /**
     * @var string Name of the database table
     */
    protected $_name = 'allocations';

	public function total($user_id, $end, $start = null, $expense_id = null)
	{
		$select = $this->getAdapter()->select();
		$columns = array('sum(allocations.amount) as total');
		$byExpense = false;
		if(!empty($expense_id))
		{
			if(is_array($expense_id))
			{
				$byExpense = true;
				$select->where('expense_id IN (?)', $expense_id);
				$columns[] = 'expense_id';
				$select->group('expense_id');
			}
			else
			{
				$select->where('expense_id = ?', $expense_id);
			}
		}
		$select->from($this->_name, $columns);
		$select->join(
			'incomes',
			'allocations.income_id = incomes.id',
			array()
			);
		if($start > 0)
			$select->where('incomes.date >= ?', $start);

		$select->where('incomes.date < ?', $end);
		$select->where('incomes.user_id = ?', $user_id);
		
		$stmt = $select->query();
		$result = $stmt->fetchAll();
		
		if($byExpense)
		{
			foreach($result as $row)
			{
				$totals[$row['expense_id']] = $row['total'];
			}
		}
		else
		{
			return $result[0]['total'];
		}
	}

	public function totalByIncomeId($income_id)
	{
		$select = $this->getAdapter()->select();
		$columns = array('sum(allocations.amount) as total');
		$select->where('income_id = ?', $income_id);		
		$stmt = $select->query();
		$result = $stmt->fetchAll();
		
		return $result[0]['total'];
	}

	public function fetchByExpenseIdAndIncomeId($expense_id, $income_id)
	{
		$select = $this->select();
		if(is_array($expense_id))
		{
			$select->where('expense_id IN (?)', $expense_id);
		}
		else
		{
			$select->where('expense_id = ?', $expense_id);
		}

		if(is_array($income_id))
		{
			$select->where('income_id IN (?)', $income_id);
		}
		else
		{
			$select->where('income_id = ?', $income_id);
		}
		
		return $this->fetchAll($select);
	}
	
	public function fetchByIncomeId($income_id)
	{
		$select = $this->select();
		if(is_array($income_id))
		{
			$select->where('income_id IN (?)', $income_id);
		}
		else
		{
			$select->where('income_id = ?', $income_id);
		}
		
		return $this->fetchAll($select);
	}
}
