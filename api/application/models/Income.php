<?php

class App_Model_Income extends Standard_Model
{
	protected $_dbTable = "App_Model_DbTable_Incomes";
	
	protected $_columns = array(
		'id' => 'int',
		'user_id' => 'int',
		'name' => 'string',
		'recurring_income_id' => 'int',
		'date' => 'timestamp',
		'amount' => 'float',
		);
	
	public function fetchByRange($start, $end)
	{
		$user = Zend_Auth::getInstance()->getIdentity();
		$table = $this->getDbTable();
		$rows = $table->fetchByRange($user->id, $start, $end);
		$incomes = array();
		foreach($rows as $row)
		{
			$income = new self();
			$income->loadFromDb($row);
			$incomes[$income->id] = $income;
		}
		
		return $incomes;
	}
	
	public function total($before = null)
	{
		$user = Zend_Auth::getInstance()->getIdentity();
		$table = $this->getDbTable();
		return $table->total($user->id, $before);
	}
	
	public function createFromRecurringByUser(App_Model_User $user, $start, $end)
	{
		$byDate = array();
		$recurring = new App_Model_RecurringIncome();
		foreach($recurring->fetchByUser($user) as $parent)
		{
			$dates = $parent->occurrences($start, $end);
			foreach($dates as $date)
			{
				$income = new self();
				$income->user_id = $parent->user_id;
				$income->name = $parent->name;
				$income->recurring_income_id = $parent->id;
				$income->date = $date;
				$income->amount = $parent->amount;
				$income->save();
				$byDate[$income->date] = $income;
			}
		}
		
		ksort($byDate);
		$incomes = array();
		foreach($byDate as $income)
		{
			$incomes[$income->id] = $income;
		}
		
		return $incomes;
	}
}