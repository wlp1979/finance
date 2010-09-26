<?php

class App_Form_Login extends Standard_Form
{
	public function init()
	{
		$this->setName('login');

		$this->addElement('text', 'email', array(
			'label' => 'Email',
			'required' => true,
			'filters' => array('StringTrim'),
			'validators' => array('EmailAddress'),
			));

		$this->addElement('password', 'password', array(
			'label' => 'Password',
			'required' => true,
			'filters' => array('StringTrim'),
			));

		$this->addElement('submit', 'submit', array(
			'label' => 'Login',
			));
		$this->addElement('button', 'register', array(
			'label' => 'Register',
			'data-url' => '/user/register',
			'class' => 'direct',
			));
	}
}