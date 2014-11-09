<?php

class TransactionController extends Standard_Controller
{
	public function listAction() {
		$request = $this->getRequest();
		if (!$request->isGet()) {
			throw new Exception('unsupported request method');
		}

		$filter = new App_Model_TransactionFilter($this->user);
		if ($request->has('expenseId')) {
			$expense = new App_Model_Expense();
			if($expense->find($request->expenseId)) {
				$filter->setExpense($expense);
			}
		}

		if ($request->has('categoryId')) {
			$category = new App_Model_Category();
			if($category->find($request->categoryId)) {
				$filter->setCategory($category);
			}
		}

		if ($request->has('startDate') && $request->startDate != '') {
			$start = new DateTime($request->startDate);
			$filter->setStartDate($start);
		}

		if ($request->has('endDate') && $request->endDate != '') {
			$end = new DateTime($request->endDate);
			$end->modify('+1 day');
			$filter->setEndDate($end);
		}

		$filter->setMinCheckNum($request->getParam('minCheckNum', null));
		$filter->setMaxCheckNum($request->getParam('maxCheckNum', null));
		$filter->setDescriptionQuery($request->getParam('descriptionQuery', null));
		$transactionModel = new App_Model_Transaction();
		$paginator = $transactionModel->fetchList($filter);
        $paginator->setItemCountPerPage($request->getParam('pageSize', 20));
        $paginator->setCurrentPageNumber($request->getParam('page', 1));

		$dto = new App_Dto_TransactionList();
		foreach($paginator as $row)
		{
			$transaction = new App_Model_Transaction();
			$transaction->loadFromDb($row);
			$dto->addTransaction(App_Dto_Transaction::fromTransactionModel($transaction));
		}
		
		$this->returnJsonResponse($dto);
	}

	public function getAction() {
		if(!$this->_request->isGet()) {
			throw new Exception('unsupported request method');
		}

		$transaction = new App_Model_Transaction();
		$request = $this->getRequest();
		if(!$request->has('transactionId') || !$transaction->find($request->transactionId)) {
			throw new Standard_Controller_NotFoundException();
		}

		$dto = App_Dto_Transaction::fromTransactionModel($transaction, $transaction->getExpense());

		$this->returnJsonResponse($dto);
	}

	public function saveAction() {
		if(!$this->_request->isPost() && !$this->_request->isPut()) {
			throw new Exception('unsupported request method');
		}

		$postData = $this->getPostData();
		if(!isset($postData->date) || empty($postData->date)) {
			//bad request throw an exception
		}

		if(!isset($postData->amount) || empty($postData->amount)) {
			//bad request throw an exception
		}

		if(!isset($postData->description) || empty($postData->description)) {
			//bad request throw an exception
		}

		$expense = new App_Model_Expense();
		if(!isset($postData->expenseId) || empty($postData->expenseId) || !$expense->find($postData->expenseId)) {
			//bad request throw an exception
		}

		$transaction = new App_Model_Transaction();
		if(isset($postData->id) && !empty($postData->id)) {
			$transaction->find($postData->id);
		}


		$transaction->user_id = $this->user->id;
		$transaction->expense_id = $expense->id;
		$transaction->date = $postData->date;

		// Currently transactions are stored with the inverse sign
		$transaction->amount = -$postData->amount;

		$transaction->description = $postData->description;

		if(isset($postData->checkNum) && !empty($postData->checkNum)) {
			$transaction->check_num = $postData->checkNum;
		}
		
		$transaction->save();

		$this->returnJsonResponse(App_Dto_Transaction::fromTransactionModel($transaction));
	}

	public function deleteAction() {
		if(!$this->_request->isDelete()) {
			throw new Exception('unsupported request method');
		}

		$transaction = new App_Model_Transaction();
		$request = $this->getRequest();
		if(!$request->has('id') || !$transaction->find($request->id)) {
			throw new Standard_Controller_NotFoundException();
		}

		$transaction->delete();
		$this->getResponse()->setHttpResponseCode(204);
		return;
	}
	
	public function uploadAction()
	{
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
					$dto = new App_Dto_TransactionList();
					foreach($transactions as $transaction) {
						$transDto = App_Dto_Transaction::fromTransactionModel($transaction);
						$match = $transaction->fetchPossibleMatch();
						if($match && $match instanceof App_Model_Transaction) {
							$transDto->setMatch(App_Dto_Transaction::fromTransactionModel($match));
						}
						$dto->addTransaction($transDto);
					}
					$this->returnJsonResponse($dto);
				}
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
		$this->view->options = $expense->formOptions($this->user);
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
				
				unset($sessns->import[$ofxid]);
			}
			
			foreach($params['match'] as $ofxid => $id)
			{
				$match = new App_Model_Transaction();
				if($match->find($params['match'][$ofxid]))
				{
					$match->ofxid = $ofxid;
					$match->save();
				}

				unset($sessns->import[$ofxid]);
			}
		}
	}
}
