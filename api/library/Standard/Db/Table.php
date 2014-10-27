<?php

class Standard_Db_Table extends Zend_Db_Table_Abstract
{
	public function insert(array $data)
	{
		$columns = $this->_getCols();
		if(in_array('created', $columns))
			$data['created'] = time();
		return parent::insert($data);
	}

	public function update(array $data, $where)
	{
		$columns = $this->_getCols();
		if(in_array('modified', $columns))
			$data['modified'] = time();
		return parent::update($data, $where);
	}
	
	public function fetchByUserId($user_id)
	{
		$columns = $this->_getCols();
		if(!in_array('user_id', $columns))
			return null;
		
		$select = $this->select();
		$select->where('user_id = ?', $user_id);
		return $this->fetchAll($select);
	}
	
	public function findMany($ids)
	{
		$columns = $this->_getCols();
		if(!in_array('id', $columns))
			return null;
		
		$select = $this->select();
		$select->where('id IN (?)', $ids);
		return $this->fetchAll($select);
	}
	
	function bulkInsert($rows)
	{
		if(empty($rows))
			return;

		$tableSpec = ($this->_schema ? $this->_schema . '.' : '') . $this->_name;

		
		$data = reset($rows);
		foreach ($data as $col => $val)
		{
			$cols[] = $this->_db->quoteIdentifier($col, true);
		}

		$query = "INSERT INTO "
			. $this->_db->quoteIdentifier($tableSpec, true)
			. ' (' . implode(', ', $cols) . ') '
			. 'VALUES ';


		$count = 0;
		foreach($rows as $data)
		{
			// extract and quote col names from the array keys
			$vals = array();
			foreach ($data as $col => $val) {
				if ($val instanceof Zend_Db_Expr) {
					$vals[] = $val->__toString();
					unset($bind[$col]);
				} else {
					if($col == 'created')
						$vals[] = $this->_db->quote(time());
					else
						$vals[] = $this->_db->quote($val);
				}
			}


			if($count > 0)
				$query .= ', ';
			$query .= '(' . implode(', ', $vals) . ')';
			$count++;
		}

		$query .= " ON DUPLICATE KEY UPDATE";


		$count = 0;
		foreach($cols as $column)
		{
			if($column != '`id`')
			{
				if($count != 0)
					$query .= ",";
				$query .= " $column = values($column)";
				$count++;
			}
		}
		$this->_db->exec($query);
	}
}