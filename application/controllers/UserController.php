<?php

class UserController extends Standard_Controller
{
	protected $_anonActions = array(
		'register',
		'login',
		'forgot-password',
		'reset-password',
		);

	public function indexAction()
	{
		return $this->_forward('edit');
	}
	
	public function registerAction()
	{
		$form = new App_Form_User();
		
		if($this->_request->isPost())
		{
			$params = $this->_request->getPost();
			if($form->isValid($params))
			{
				$user = App_Model_User::create($form);
				if($user instanceof App_Model_User)
				{
					$user->login();
					return $this->_redirect('/');
				}
			}
		}
		
		$this->view->form = $form;
	}
	
	public function loginAction()
	{
		$form = new App_Form_Login();
		$auth = Zend_Auth::getInstance();
		if($auth->hasIdentity())
		{
			$this->_redirect('/');
		}

		if($this->_request->isPost())
		{
			$params = $this->_request->getPost();
			if($form->isValid($params))
			{
				$email = $params['email'];
				$pass = $params['password'];

				$user = new App_Model_User();
				$db = $user->getDbTable()->getAdapter();

				$adapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'email', 'password', "MD5(CONCAT(`id`, ?))");
				$adapter->setIdentity($email);
				$adapter->setCredential($pass);

				$result = $auth->authenticate($adapter);
				if($result->isValid())
				{
					$row = $adapter->getResultRowObject();
					$user->loadFromDb($row);
					$user->login();
					if($params['remember'] == 1)
					{
						$persist = new App_Model_PersistentLogin();
						$persist->setup($user);
					}
					
					if(!empty($_SERVER['HTTP_REFERER'])) 
					{
						return $this->_redirect($_SERVER['HTTP_REFERER']);
					} 
					else 
					{
						return $this->_redirect('/');
					}
				}
				else
				{
					$form->getElement('email')->addError("Incorrect email/password combination.");
				}
			}
		}
		
		$this->view->form = $form;
	}
	
	public function forgotPasswordAction()
	{
		//present form for starting the password reset path
	}
	
	public function resetPasswordAction()
	{
		//present form for resetting the users password
	}
	
	public function editAction()
	{
		//present form for editing account info (name, email, and password)
	}
	
	public function logoutAction()
	{
		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();
		Zend_Session::destroy();
		return $this->_redirect('/');
	}
}
