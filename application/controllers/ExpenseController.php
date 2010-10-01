<?php

class ExpenseController extends Standard_Controller
{
	protected $_ajaxActions = array(
		'edit-category' => 'json',
		'order-categories' => 'json',
		'edit' => 'json',
		'chooser' => 'json',
		'recalc-totals' => 'json',
		);

	public function editCategoryAction()
	{
		$form = new App_Form_Category();
		$category = new App_Model_Category();
		
		if($this->_request->has('category_id') && $category->find($this->_request->category_id))
		{
			$form->populate($category->toArray());
		}
		
		if($this->_request->isPost())
		{
			$params = $this->_request->getPost();
			if($form->isValid($params))
			{
				$category->user_id = $this->user->id;
				$category->name = $form->getValue('name');
				$category->save();
				
				$categories = $category->fetchByUser($this->user);
				$html = $this->view->partial('partials/category-list.phtml', array('categories' => $categories));
				$this->setRefresh('#expense-categories', $html);
				$this->addNotification('The category has been saved.', 'Category Saved');
				return;
			}
		}
		
		$this->setForm($form);
	}
	
	public function orderCategoriesAction()
	{
		if($this->_request->isPost())
		{
			$categories = $this->_request->category;
			$category = new App_Model_Category();
			foreach($categories as $order => $id)
			{
				$category->find($id);
				$category->sort_order = $order;
				$category->save();
			}
			$this->addNotification('Expense Category order has been saved', 'Update Successful');
		}
	}
	
	public function editAction()
	{
		$expense = new App_Model_Expense();
		$form = new App_Form_Expense();
		$category = new App_Model_Category();
		$categories = $category->fetchByUser($this->user);
		foreach($categories as $category)
		{
			$form->getElement('category_id')->addMultiOption($category->id, $category->name);
		}
		
		if($this->_request->has('expense_id') && $expense->find($this->_request->expense_id))
		{
			$form->populate($expense->toArray());
		}
		
		if($this->_request->isPost())
		{
			$params = $this->_request->getPost();
			if($form->isValid($params))
			{
				$expense->user_id = $this->user->id;
				$expense->name = $form->getValue('name');
				$expense->category_id = $form->getValue('category_id');
				$expense->day_due = $form->getValue('day_due');
				$expense->auto_pay = $form->getValue('auto_pay');
				$expense->summary = $form->getValue('summary');
				
				$expense->save();
				
				$this->addNotification('Expense saved', 'Success');
				$this->view->expense_id = $expense->id;
				
				return;
			}
		}
		
		$this->setForm($form);
	}
	
	public function chooserAction()
	{
		$expense = new App_Model_Expense();
		$expenses = $expense->fetchByUser($this->user);
		
		$form = new App_Form_ExpenseChooser();
		foreach($expenses as $expense)
		{
			$form->getElement('expense_id')->addMultiOption($expense->id, $expense->name);
		}
		
		if($this->_request->isPost())
		{
			$this->view->expense_id = $this->_request->expense_id;
			return;
		}
		
		$this->setForm($form);
	}
	
	public function recalcTotalsAction()
	{
		$expenses = new App_Model_Expense();
		foreach($expenses->fetchByUser($this->user) as $expense)
		{
			$expense->updateTotals($this->user->created);
		}
	}
}
