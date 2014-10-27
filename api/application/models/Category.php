<?php

class App_Model_Category extends Standard_Model
{
	protected $_dbTable = "App_Model_DbTable_Categories";
	
	protected $_columns = array(
		'id' => 'int',
		'user_id' => 'int',
		'name' => 'string',
		'sort_order' => 'int',
		);
		
	public function save()
	{
		if($this->sort_order === '' || $this->sort_order === null)
		{
			$table = $this->getDbTable();
			$this->sort_order = $table->getNextSortOrder($this->user_id);
		}
		
		return parent::save();
	}
}