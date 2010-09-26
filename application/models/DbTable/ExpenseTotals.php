<?php

class App_Model_DbTable_ExpenseTotals extends Standard_Db_Table
{
    /**
     * @var string Name of the database table
     */
    protected $_name = 'expense_totals';

	public function fetchLastByExpenseId($expense_id, $end = null)
	{
		if(is_array($expense_id))
		{
			$sub = $this->getAdapter()->select();
			$sub->from($this->_name, array('expense_id', 'max(end_date) as end_date'));
			$sub->where('expense_id IN (?)', $expense_id);
			if($end > 0)
				$sub->where('end_date <= ?', $end);
			$sub->group('expense_id');
			$select = $this->select(Zend_Db_Table_Abstract::SELECT_WITH_FROM_PART);
			$select->join($sub, 'expense_totals.expense_id = t.expense_id AND expense_totals.end_date = t.end_date', array());
		}
		else
		{
			$select = $this->select();
			$select->where('expense_id = ?', $expense_id);
			if($end > 0)
				$select->where('end_date <= ?', $end);
			$select->order('end_date DESC');
			$select->limit(1);
		}
		
		return $this->fetchAll($select);
	}

	public function fetchLastByUserId($user_id, $end = null, $excludeZero = false)
	{
		$sub = $this->getAdapter()->select();
		$sub->from($this->_name, array('expense_id', 'max(end_date) as end_date'));
		$expenses = $this->getAdapter()->select();
		$expenses->from('expenses', array('id'));
		$expenses->where('user_id = ?', $user_id);
		$sub->where("expense_id IN ($expenses)");
		if($end > 0)
			$sub->where('end_date <= ?', $end);
		$sub->group('expense_id');
		$select = $this->select(Zend_Db_Table_Abstract::SELECT_WITH_FROM_PART);
		$select->join($sub, 'expense_totals.expense_id = t.expense_id AND expense_totals.end_date = t.end_date', array());
		if($excludeZero)
			$select->where('total_allocated <> total_spent');

		return $this->fetchAll($select);
	}
}
