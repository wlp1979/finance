<?php

class AllocationController extends Standard_Controller
{
	protected $_ajaxActions = array(
		'index' => 'html',
		'summary' => 'html',
		);

	public function indexAction()
	{
		$incomeModel = new App_Model_Income();
		$transModel = new App_Model_Transaction();
		$allocModel = new App_Model_Allocation();
		$expModel = new App_Model_Expense();
		$expTotalModel = new App_Model_ExpenseTotal();
		$catModel = new App_Model_Category();
		$transModel = new App_Model_Transaction();

		$incomes = $incomeModel->fetchByRange($this->_startDate, $this->_endDate);
		if(empty($incomes))
		{
			$incomes = $incomeModel->createFromRecurringByUser($this->user, $this->_startDate, $this->_endDate);
		}

		$previousIncome = $incomeModel->total($this->_startDate);
		$previousSpent = $transModel->total($this->_startDate);
		$totalStarting = $previousIncome - $previousSpent;

		$allocations = $allocModel->fetchByIncome($incomes);
		$expense_ids = array_keys($allocations);
		$balances = $expTotalModel->fetchLastWithBalance($this->_startDate);
		$expense_ids += array_keys($balances);
		$expenses = $expModel->findMany($expense_ids);
		$categories = $catModel->fetchByUser($this->user);
		$spent = $transModel->total($this->_endDate, $this->_startDate, $expenses);
		
		$this->view->incomes = $incomes;
		$this->view->totalStarting = $totalStarting;
		$this->view->categories = $categories;
		$this->view->expenses = $expenses;
		$this->view->allocations = $allocations;
		$this->view->balances = $balances;
		$this->view->spent = $spent;
	}
	
	public function summaryAction()
	{
		$now = time();
		$incomeModel = new App_Model_Income();
		$incomes = $incomeModel->fetchByRange($this->_startDate, $this->_endDate);

		$incomes = $incomeModel->fetchByRange($this->_startDate, $this->_endDate);
		if(empty($incomes))
		{
			$incomes = $incomeModel->createFromRecurringByUser($this->user, $this->_startDate, $this->_endDate);
		}

		$previousIncome = $incomeModel->total($this->_startDate);
		
		$transModel = new App_Model_Transaction();
		$previousSpent = $transModel->total($this->_startDate);
		$totalStarting = $previousIncome - $previousSpent;
		
		$totalIncome = 0;
		$totalCurrent = $totalStarting;
		foreach($incomes as $income)
		{
			$totalIncome += $income->amount;
			if($income->date <= $now)
			{
				$totalCurrent += $income->amount;
			}
		}
		
		$totalSpent = $transModel->total($this->_startDate, $this->_endDate);
		
		$expenseModel = new App_Model_Expense();
		$allocModel = new App_Model_Allocation();
		$expTotalModel = new App_Model_ExpenseTotal();
		$expenses = $expenseModel->fetchSummary($this->_startDate, $this->_endDate);
		$prevTotals = $expTotalModel->fetchLastByExpense($expenses, $this->_startDate);
		$expenseStart = array();
		$expenseCurrent = array();
		$expenseAllocated = array();
		foreach($expenses as $id => $expense)
		{
			$expenseStart[$id] = $expenseCurrent[$id] = $prevTotal[$id]['total_allocated'] - $prevTotal[$id]['total_spent'];
			$expenseAllocated[$id] = 0;
		}
		
		$allocations = $allocModel->fetchByExpenseAndIncome($expenses, $incomes);
		foreach($allocations as $expenseAllocations)
		{
			foreach($expenseAllocations as $allocation)
			{
				$expenseAllocated[$allocation->expense_id] += $allocation->amount;
				if($incomes[$allocation->income_id]->date <= $now)
				{
					$expenseCurrent[$allocation->expense_id] += $allocation->amount;
				}
			}
		}

		$expenseSpent = $transModel->total($this->_endDate, $this->_startDate, $expenses);
		$expenseRemaining = array();
		foreach($expenses as $id => $expense)
		{
			$expenseCurrent[$id] -= $expenseSpent[$id];
			$expenseRemaining[$id] = $expenseStart[$id] + $expenseAllocated[$id] - $expenseSpent[$id];
		}
		
		
		$this->view->totalStarting = $totalStarting;
		$this->view->totalIncome = $totalIncome;
		$this->view->totalSpent = $totalSpent;
		$this->view->totalRemaing = $totalStarting + $totalIncome - $totalSpent;
		$this->view->totalCurrent = $totalCurrent;
		
		$this->view->expenses = $expenses;
		$this->view->expenseStart = $expenseStart;
		$this->view->expenseAllocated = $expenseAllocated;
		$this->view->expenseSpent = $expenseSpent;
		$this->view->expenseRemaining = $expenseRemaining;
		$this->view->expenseCurrent = $expenseCurrent;
	}
}
