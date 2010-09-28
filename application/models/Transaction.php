<?php
require_once APPLICATION_PATH . '/../library/ofx/ofx.php';

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
		
	protected $_oldDate;
	protected $_oldExpenseId;
		
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
				$expense_id = $expenses->id;
			}
		}
		
		return $table->total($user->id, $end, $start, $expense_id);
	}
	
	public function save()
	{
		$return = parent::save();
		$date = $this->date;
		if(!empty($this->_oldDate))
			$date = min($this->_oldDate, $this->date);
		$expense = $this->getExpense();
		$expense->updateTotals($date);
		
		if(!empty($this->_oldExpenseId) && $expense->find($this->_oldExpenseId))
		{
			$expense->updateTotals($date);
		}
		
		return $return;
	}
	
	public function getExpense()
	{
		$expense = new App_Model_Expense();
		return $expense->find($this->expense_id);
	}
	
	public function fetchRange(App_Model_User $user, $start, $end, App_Model_Expense $expense = null, App_Model_Category $category = null)
	{
		$table = $this->getDbTable();
		
		$expense_id = null;
		if(!empty($expense))
		{
			$expense_id = $expense->id;
		}
		
		$category_id = null;
		if(!empty($category))
		{
			$category_id = $category->id;
		}
		
		return $table->fetchRange($user->id, $start, $end, $expense_id, $category_id);
	}
	
	public function setExpenseId($value)
	{
		$this->_oldExpenseId = $this->_data['expense_id'];
		$this->_data['expense_id'] = $value;
	}
	
	public function setDate($value)
	{
		$value = $this->filterTimestamp($value);
		$this->_oldDate = $this->_data['date'];
		$this->_data['date'] = $value;
	}
	
	public function parseOfx($file)
	{
		$user = Zend_Auth::getInstance()->getIdentity();
		$ofx = OFX::fromFile($file);
		$account = $ofx->account();
		$bankId = $ofx->bankId();
		$transactions = array();
		foreach($ofx->transactions() as $entry)
		{
			$transaction = new self();
			$transaction->user_id = $user->id;
			$transaction->date = $entry->date;
			$transaction->amount = $entry->amount;
			$transaction->check = $entry->check;
			$transaction->description = $entry->name . ' ' . $entry->memo;
			$transaction->ofxid = self::buildOfxId($bankId, $account, $entry->id);
			$transactions[] = $transaction;
		}
		
		return $transactions;
	}
	
	public static function buildOfxId($bankId, $account, $itemId)
	{
		return md5("$bankId:$account:$itemId");
	}
}