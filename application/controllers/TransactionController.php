<?php

class TransactionController extends Standard_Controller
{
	protected $_ajaxActions = array(
		'index' => 'html',
		'edit' => 'json',
		'edit-value' => 'html',
		'import-form' => 'html',
		'import' => 'json',
		'delete' => 'json',
		);

	public function indexAction()
	{
		$transaction = new App_Model_Transaction();
		$paginator = $transaction->fetchRange($this->user, $this->_startDate, $this->_endDate);
        $paginator->setItemCountPerPage(30);
        $paginator->setCurrentPageNumber($this->_request->getParam('page', 1));
        Zend_Paginator::setDefaultScrollingStyle('Sliding');
        Zend_View_Helper_PaginationControl::setDefaultViewPartial(
            'partials/pagination.phtml'
        );

		$transactions = array();
		$expenseIds = array();
		foreach($paginator as $row)
		{
			$transaction = new App_Model_Transaction();
			$transaction->loadFromDb($row);
			$transactions[$row->id] = $transaction;
			$expenseIds[$row->expense_id] = $row->expense_id; 
		}
		
		$expense = new App_Model_Expense();
		$expenses = $expense->findMany($expenseIds);
		
		$form = new App_Form_Transaction();
		foreach($expense->fetchByUser($this->user) as $expense)
		{
			$options[$expense->id] = $expense->name;
		}
		asort($options);
		$form->getElement('expense_id')->addMultiOptions($options);
		
		$data = array(
			'date' => strftime('%m/%d/%y'),
			);
		if($this->_request->has('last_date'))
		{
			$data['date'] = strftime('%m/%d/%y', $this->_request->last_date);
		}
		
		if($this->_request->has('last_expense'))
		{
			$data['expense_id'] = $this->_request->last_expense;
		}
		
		$form->populate($data);
		
		$sessns = new Zend_Session_Namespace('import');
		$this->view->import = (count($sessns->import) > 0);
		$this->view->paginator = $paginator;
		$this->view->transactions = $transactions;
		$this->view->expenses = $expenses;
		$this->view->form = $form;
		$this->view->expenseOptions = $options;
	}
	
	public function editAction()
	{
		$form = new App_Form_Transaction();
		$transaction = new App_Model_Transaction();
		$expense = new App_Model_Expense();
		
		foreach($expense->fetchByUser($this->user) as $expense)
		{
			$options[$expense->id] = $expense->name;
		}
		asort($options);
		$form->getElement('expense_id')->addMultiOptions($options);
		
		if($this->_request->has('transaction_id') && $transaction->find($this->_request->transaction_id))
		{
			$data = $transaction->toArray();
			$data['date'] = $transaction->displayDate();
			$form->populate($data);
		}
		
		if($this->_request->isPost())
		{
			$params = $this->_request->getPost();
			if($form->isValid($params))
			{
				$transaction->user_id = $this->user->id;
				$transaction->expense_id = $form->getValue('expense_id');
				$transaction->date = $form->getValue('date');
				$transaction->amount = $form->getValue('amount');
				$transaction->description = $form->getValue('description');
				$transaction->check_num = $form->getValue('check_num');
				
				$transaction->save();
				
				$this->view->last_date = $transaction->date;
				$this->view->last_expense = $transaction->expense_id;
				$this->addNotification('Transaction saved', 'Success');
				return;
			}
		}
		
		$this->setForm($form);
	}
	
	public function editValueAction()
	{
		$transaction = new App_Model_Transaction();
		if($this->_request->has('transaction_id') && $transaction->find($this->_request->transaction_id))
		{
			if($this->_request->has('column') && $this->_request->has('value'))
			{
				$column = $this->_request->column;
				$value = $this->_request->value;
				
				$transaction->$column = $value;
				$transaction->save();
				
				switch($column)
				{
					case 'date':
					$this->view->value = $transaction->displayDate();
					break;
					
					case 'amount':
					$this->view->value = $transaction->displayCurrency();
					break;
					
					case 'expense_id':
					$expense = $transaction->getExpense();
					$this->view->value = $expense->name;
					break;
					
					case 'check_num':
					$this->view->value = ($transaction->check_num == '') ? '&nbsp;' : $transaction->check_num;
					break;
					
					default:
					$this->view->value = $value;
				}
			}
		}
	}
	
	public function uploadAction()
	{
		$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
		$transaction = new App_Model_Transaction();
		$form = new App_Form_File();
		if($this->_request->isPost())
		{
			$params = $this->_request->getPost();
			if($form->isValid($params))
			{
				$form->file->receive();
				$file = $form->file->getFileName();
				if(!empty($file))
				{
					$transactions = $transaction->parseOfx($file);
					if(!empty($transactions))
					{
						$sessns = new Zend_Session_Namespace('import');
						$sessns->import = $transactions;
						echo "import";
						return;
					}
				}
				echo "empty";
			}
		}
	}
	
	public function importFormAction()
	{
		$sessns = new Zend_Session_Namespace('import');
		$import = $sessns->import;
		$count = 0;
		$this->view->matches = array();
		$this->view->import = array();
		foreach($import as $ofxid => $transaction)
		{
			if($count++ >= 10)
				break;
			$this->view->import[$ofxid] = $transaction;
			$match = $transaction->fetchPossibleMatch();
			if($match instanceof App_Model_Transaction)
				$this->view->matches[$ofxid] = $match;
		}

		$expense = new App_Model_Expense();
		
		$this->view->options = array();
		foreach($expense->fetchByUser($this->user) as $expense)
		{
			$this->view->options[$expense->id] = $expense->name;
		}
	}
	
	public function importAction()
	{
		$sessns = new Zend_Session_Namespace('import');

		if($this->_request->isPost())
		{
			$params = $this->_request->getPost();
			foreach($params['transactions'] as $ofxid => $data)
			{
				if(in_array($ofxid, $params['import']))
				{
					$transaction = $sessns->import[$ofxid];
					$transaction->date = $data['date'];
					$transaction->description = $data['description'];
					$transaction->expense_id = $data['expense_id'];
					
					$transaction->save();
				}
				elseif(isset($params['match'][$ofxid]))
				{
					$match = new App_Model_Transaction();
					if($match->find($params['match'][$ofxid]))
					{
						$match->date = $data['date'];
						$match->description = $data['description'];
						$match->expense_id = $data['expense_id'];
						$match->ofxid = $ofxid;
						$match->save();
					}
				}
				
				unset($sessns->import[$ofxid]);
			}
		}
	}
	
	public function deleteAction()
	{
		$transaction = new App_Model_Transaction();
		if($transaction->find($this->_request->transaction_id))
		{
			$transaction->delete();
			$this->addNotification('The transaction has been deleted', 'Success');
		}
		
		return;
	}
}
