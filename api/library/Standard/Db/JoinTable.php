<?php

class Standard_Db_JoinTable extends Standard_Db_Table
{
	public function insert(array $data)
	{
		try
		{
			return parent::insert($data);
		}
		catch(Zend_Db_Statement_Exception $e)
		{
			//is it an integrity constraint
			if($e->getCode() == '23000')
			{
				$this->_setupPrimaryKey();
				$where = array();
				foreach($this->_primary as $column)
				{
					$clause = "{$column} = ?";
					$where[$clause] = $data[$column];
					unset($data[$column]);
				}
				
				if(!empty($data))
					return $this->update($data, $where);
				else
					return true;
			}
		}
	}
}