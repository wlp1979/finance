<?php

class App_Model_DbTable_Categories extends Standard_Db_Table
{
    /**
     * @var string Name of the database table
     */
    protected $_name = 'categories';

	public function getNextSortOrder($user_id)
	{
		if(empty($user_id))
			return 0;
			
		$select = $this->getAdapter()->select();
		$select->from($this->_name, array('max(sort_order) as max_sort'));
		$select->where('user_id = ?', $user_id);
		$stmt = $select->query();
		$result = $stmt->fetchAll();
		if($result[0]['max_sort'] > 0)
		{
			return $result[0]['max_sort'] + 1;
		}
		else
		{
			return 0;
		}
	}

	public function fetchByUserId($user_id)
	{
		$columns = $this->_getCols();
		if(!in_array('user_id', $columns))
			return null;
		
		$select = $this->select();
		$select->where('user_id = ?', $user_id);
		$select->order('sort_order');
		return $this->fetchAll($select);
	}
}
