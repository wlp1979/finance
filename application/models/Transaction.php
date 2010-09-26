<?php

class App_Model_Transaction extends Standard_Model
{
	protected $_dbTable = "App_Model_DbTable_Transactions";
	
	protected $_columns = array(
		'id' => 'int',
		'user_id' => 'int',
		'expense_id' => 'int',
		'date' => 'timestamp',
		'amount' => 'float',
		'description' => 'string',
		'check' => 'int',
		'ofxid' => 'string',
		);
		
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
				$expense_id = $expense->id;
			}
		}
		
		return $table->total($user->id, $end, $start, $expense_id);
	}
	
	public function save()
	{
		$return = parent::save();
		$expense = $this->getExpense();
		$expense->updateTotals($this->date);
		return $return;
	}
	
	public function getExpense()
	{
		$expense = new App_Model_Expense();
		return $expense->find($this->expense_id);
	}
}