<?php

class App_Model_DbTable_Expenses extends Standard_Db_Table
{
    /**
     * @var string Name of the database table
     */
    protected $_name = 'expenses';

	public function fetchSummary($user_id)
	{
		$select = $this->select(Zend_Db_Table_Abstract::SELECT_WITH_FROM_PART);
		$select->join(
			'categories',
			'expenses.category_id = categories.id',
			array()
			);

		$select->where('expenses.user_id = ?', $user_id);
		$select->where('summary = 1');
		$select->order(array('categories.sort_order', 'expenses.day_due'));
		return $this->fetchAll($select);
	}

	public function findMany($ids)
	{
		$columns = $this->_getCols();
		if(!in_array('id', $columns))
			return null;
		
		$select = $this->select();
		$select->where('id IN (?)', $ids);
		$select->order(array('category_id', 'day_due'));
		return $this->fetchAll($select);
	}	
}
