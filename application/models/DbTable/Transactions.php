<?php

class App_Model_DbTable_Transactions extends Standard_Db_Table
{
	protected $_name = 'transactions';

	public function total($user_id, $end, $start = null, $expense_id = null)
	{
		$select = $this->getAdapter()->select();
		$columns = array('sum(amount) as total');

		if($start > 0)
			$select->where('date >= ?', $start);

		$select->where('date < ?', $end);
		$select->where('user_id = ?', $user_id);

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
		$stmt = $select->query();
		$result = $stmt->fetchAll();

		if($byExpense)
		{
			foreach($result as $row)
			{
				$totals[$row['expense_id']] = $row['total'];
			}
			
			return $totals;
		}
		else
		{
			return $result[0]['total'];
		}
	}

	public function fetchRange($user_id, $start, $end, $expense_id = null, $category_id = null)
	{
		$select = $this->select(Zend_Db_Table_Abstract::SELECT_WITH_FROM_PART);
		$select->where('user_id = ?', $user_id);
		$select->where('date >= ?', $start);
		$select->where('date < ?', $end);
		if(!empty($expense_id))
			$select->where('expense_id = ?', $expense_id);

		if(!empty($category_id))
		{
			$select->join('expenses', 'transaction.expense_id = expenses.id', array());
			$select->where('expenses.category_id = ?', $category_id);
		}
		$select->order('date DESC');
		$select->order('description');
		return Zend_Paginator::factory($select);
	}
}
