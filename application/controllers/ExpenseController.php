<?php

class ExpenseController extends Standard_Controller
{
	protected $_ajaxActions = array(
		'edit-category' => 'json',
		'order-categories' => 'json',
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
}
