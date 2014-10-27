<?php
class Standard_Controller extends Zend_Controller_Action
{
	protected $_context;
	protected $_startDate;
	protected $_endDate;
	
	public $user;
	
	public function init()
	{
		if(isset($this->_ajaxActions))
		{
			$ajaxContext = $this->_helper->getHelper('AjaxContext');
			foreach($this->_ajaxActions as $action => $context)
			{
				$ajaxContext->addActionContext($action, $context);

			}
			$ajaxContext->initContext();
			$this->_context = $ajaxContext->getCurrentContext();
		}
		
		$sessns = new Zend_Session_Namespace();
		if($this->_request->has('month') && $this->_request->has('year'))
		{
			$this->setDatesByMonth($this->_request->month, $this->_request->year);
		}
		elseif($this->_request->has('start') && $this->_request->has('end'))
		{
			$this->_startDate = $this->_request->start;
			$this->_endDate = $this->_request->end;
		}
		elseif(isset($sessns->dates))
		{
			$this->_startDate = $sessns->dates->start;
			$this->_endDate = $sessns->dates->end;
		}
		else
		{
			$date = new Zend_Date();
			$this->setDatesByMonth($date->get(Zend_Date::MONTH), $date->get(Zend_Date::YEAR));
		}
		
		$sessns->dates->start = $this->_startDate;
		$sessns->dates->end = $this->_endDate;

		$auth = Zend_Auth::getInstance();
		if($auth->hasIdentity())
			$this->user = $auth->getIdentity();
			
		if($this->_context == 'json')
		{
			ini_set('display_errors', false);
		}
	}

	public function preDispatch()
	{
		$auth = Zend_Auth::getInstance();
        $controller = $this->_request->getControllerName();
        $action = $this->_request->getActionName();
		if (!$auth->hasIdentity() && (!isset($this->_anonActions) || !in_array($action, $this->_anonActions))) 
		{
			$this->_forward('login', 'user');
		}
	}
	
	public function setDatesByMonth($month, $year)
	{
		$date = new Zend_Date("{$month}-01-{$year}", null, 'en_US');
		$this->_startDate = $date->get(Zend_Date::TIMESTAMP);
		$date->add(1, Zend_Date::MONTH);
		$this->_endDate = $date->get(Zend_Date::TIMESTAMP);
	}
	
	public function setForm(Zend_Form $form)
	{
		if($this->_context == 'json')
		{
			$this->view->form = $form->render($this->view);
		}
		else
		{
			$this->view->form = $form;
		}
	}
	
	public function setAppend($selector, $html)
	{
		unset($this->view->replace);
		unset($this->view->refresh);
		$this->view->append = $selector;
		$this->view->content = $html;
	}

	public function setReplace($selector, $html)
	{
		unset($this->view->append);
		unset($this->view->refresh);
		$this->view->replace = $selector;
		$this->view->content = $html;
	}
	
	public function setRefresh($selector, $html)
	{
		unset($this->view->append);
		unset($this->view->replace);
		$this->view->refresh = $selector;
		$this->view->content = $html;
	}
	
	public function addNotification($text, $title = null, $time = null, $sticky = false)
	{
		$notification = compact('title', 'text');
		if(!empty($time))
			$notification['time'] = $time;
		
		if($sticky)
			$notification['sticky'] = $sticky;
		
		if(isset($this->view->notifiy))	
			$this->view->notify[] = $notification;
		else
			$this->view->notify = array($notification);
	}
	
	public function setError($text)
	{
		$this->view->error = $text;
	}
}
