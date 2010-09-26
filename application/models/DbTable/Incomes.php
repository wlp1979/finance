<?php

class App_Model_DbTable_Incomes extends Standard_Db_Table
{
    /**
     * @var string Name of the database table
     */
    protected $_name = 'incomes';

	public function fetchByRange($user_id, $start, $end)
	{
		$select = $this->select();
		$select->where('user_id = ?', $user_id);
		$select->where('date >= ?', $start);
		$select->where('date < ?', $end);
		$select->order('date');
		return $this->fetchAll($select);
	}

	public function total($user_id, $before = null)
	{
		$select = $this->getAdapter()->select();
		$select->from($this->_name, array('sum(amount) as total'));
		$select->where('user_id = ?', $user_id);

		if($before > 0)
		{
			$select->where('date < ?', $before);
		}

		$stmt = $select->query();
		$result = $stmt->fetchAll();
		return $result[0]['total'];
	}
}
