<?php

class SettingsController extends Standard_Controller
{
	protected $_ajaxActions = array(
		'index' => 'html',
		);

	public function indexAction()
	{
		$recurring = new App_Model_RecurringIncome();
		$this->view->recurring = $recurring->fetchByUser($this->user);
		
		$category = new App_Model_Category();
		$this->view->categories = $category->fetchByUser($this->user);
	}
}
