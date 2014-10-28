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
		$response = new stdClass();
		if($this->_request->isPost())
		{
			$postData = $this->getPostData();
			$params = array(
				'email' => $postData->email,
				'password' => $postData->password
				);
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
					return;
				}
				else
				{
					throw new Standard_Controller_AccessDeniedException("invalid login");
				}
			}
		}
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
	}
}
