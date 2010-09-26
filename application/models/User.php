<?php

class App_Model_User extends Standard_Model
{
	protected $_dbTable = "App_Model_DbTable_Users";
	
	protected $_columns = array(
		'id' => 'int',
		'name' => 'string',
		'email' => 'string',
		'password' => 'string',
		'create' => 'timestamp',
		'last_login' => 'timestamp',
		);
		
	public function setPassword($password)
	{
		$this->_data['password'] = md5($this->id . $password);
	}
	
	public static function create(App_Form_User $form)
	{
		$user = new self();
		$user->_data['name'] = $form->getValue('name');
		$user->_data['email'] = $form->getValue('email');
		try
		{
			$user->save();
		}
		catch(Zend_Db_Statement_Exception $e)
		{
			//is it an integrity constraint
			if($e->getCode() == '23000')
			{
				$form->getElement('email')->addError('A user with this email address already exists');
				return false;
			}
		}
		
		//now that we have an id, set the password
		$user->setPassword($form->getValue('password'));
		$user->save();
		
		return $user;
	}
	
	public function login()
	{
		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();
		$auth->getStorage()->write($this);
		
		$this->last_login = time();
		$this->save();
		
		return $this;
	}
}