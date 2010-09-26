<?php

class IncomeController extends Standard_Controller
{
	protected $_ajaxActions = array(
		'edit-recurring' => 'json',
		);
	
	public function editRecurringAction()
	{
		$form = new App_Form_RecurringIncome();
		$income = new App_Model_RecurringIncome();
		
		$new = true;
		if($this->_request->has('recurring_income_id') && $income->find($this->_request->recurring_income_id))
		{
			$new = false;
			$data = $income->toArray();
			$data['start_date'] = $income->displayDate('start_date');
			if($income->end_date > 0)
			{
				$data['end_date'] = $income->displayDate('end_date');
			}
			else
			{
				unset($data['end_date']);
			}
			$form->populate($data);
		}
		
		if($this->_request->isPost())
		{
			$params = $this->_request->getPost();
			if($form->isValid($params))
			{
				$income->user_id = $this->user->id;
				$income->name = $form->getValue('name');
				$income->amount = $form->getValue('amount');
				$income->recur_type = $form->getValue('recur_type');
				$income->start_date = $form->getValue('start_date');
				$income->end_date = $form->getValue('end_date');
				$income->save();
				
				$html = $this->view->partial('partials/recurring-income.phtml', array('income' => $income));
				if($new)
				{
					$this->setAppend('#recurring-incomes', $html);
				}
				else
				{
					$this->setReplace('#recurring-income-' . $income->id, $html);
				}
				$this->addNotification('The recurring income has been saved', 'Saved');
				return;
			}
		}
		
		$this->setForm($form);
	}
}
