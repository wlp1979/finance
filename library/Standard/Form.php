<?php

class Standard_Form extends Zend_Form
{
	public function loadDefaultDecorators()
	{

		if ($this->loadDefaultDecoratorsIsDisabled()) {
			return;
		}

		$name = $this->getName();
		$decorators = $this->getDecorators();
		if (empty($decorators)) {
			$this->addDecorator('FormElements')
				->addDecorator('Form')
				->addDecorator('Description', array('tag' => 'div'))
				->addDecorator('HtmlTag', array(
					'tag'   => 'div',
					'class' => "form",
					'id'    => "form-$name",
				));
		}
	}

	public function addElement($element, $name = null, $options = null)
	{
		parent::addElement($element, $name, $options);
		if(is_string($element))
		{
			$element = $this->getElement($name);
		}

		$element->removeDecorator('DdDtWrapper');
		$element->removeDecorator('DtDdWrapper');
		$element->removeDecorator('HtmlTag');

		$label = $element->getDecorator('Label');
		if($label)
		{
			$label->setTag('');
			$label->removeOption('tag');
			$label->setOption('class', 'form-element-label');
			$label->setRequiredSuffix(' *');
		}

		$type = strtolower(str_replace('Zend_Form_Element_', '', $element->getType()));

		$element->addDecorator('HtmlTag', array(
			'tag' => 'div',
			'class' => "form-element $type",
			'id' => 'form-element-' . $element->getName(),
			));
			
		if($element instanceof Zend_Form_Element_Button || $element instanceof Zend_Form_Element_Submit)
		{
			$class = $element->getAttrib('class');
			$newClasses = 'ui-button ui-state-default ui-corner-all ui-button-text-only';
			$class = (empty($class)) ? $newClasses : "$class $newClasses";
			$element->setAttrib('class', $class);
		}
	}

	public function addSubForm(Zend_Form $form, $name, $order = null)
	{
		$form->setIsArray(true);

		parent::addSubForm($form, $name, $order);
		$form->setDecorators(array(
			'FormElements',
			array('HtmlTag', array('tag' => 'div', 'class' => 'subform', 'id' => "subform-$name")),
			'Fieldset',
			));

		return $this;
	}

	public function getDescription()
	{
		foreach($this->getElements() as $element)
		{
			if($element->isRequired())
			{
				return '* = required field';
			}
		}
		return "";
	}

	public function addDisplayGroup(array $elements, $name, $options = null)
	{
		parent::addDisplayGroup($elements, $name, $options);
		$group = $this->getDisplayGroup($name);
		$group->setDecorators(array(
			'FormElements',
			array('HtmlTag', array('tag' => 'div', 'class' => 'group', 'id' => "group-$name")),
			'Fieldset',
			));
	}
}
