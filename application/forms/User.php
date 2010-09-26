<?php

class App_Form_User extends Standard_Form
{
	public function init()
	{
		$this->setName('user');

		$this->addElement('text', 'name', array(
			'label' => 'Name',
			'required' => true,
			'filters' => array('StringTrim'),
			));

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

		$this->addElement('password', 'password_confirm', array(
			'label' => 'Confirm Password',
			'required' => true,
			'ignore' => true,
			'filters' => array('StringTrim'),
			));

		$password = $this->getElement('password');
		$confirm = $this->getElement('password_confirm');
		$confirmValid = new Standard_Validate_ConfirmMatch($password);
		$confirm->addValidator($confirmValid);

		$this->addElement('submit', 'submit', array(
			'label' => 'Save',
			));
	}
}