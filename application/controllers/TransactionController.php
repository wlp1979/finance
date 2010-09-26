<?php

class TransactionController extends Standard_Controller
{
	protected $_ajaxActions = array(
		'index' => 'html',
		);

	public function indexAction()
	{
		//display main user interface for budget data all other calls will be ajax calls to the budgetcontroller
	}
}
