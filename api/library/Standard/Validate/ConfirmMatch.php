<?php

/**
 * A form validator to make sure two form fields match
 *
 * @package default
 * @author Lee Parker
 **/
class Standard_Validate_ConfirmMatch extends Zend_Validate_Abstract
{
    const NOT_MATCH = 'notMatch';
    
    protected $_messageTemplates = array(
        self::NOT_MATCH => 'Confirmation field must match %confirmLabel%',
        );
        
    protected $_messageVariables = array(
        'confirmLabel'  => '_confirmLabel',
    );

    protected $_confirmLabel;
    
    protected $_confirmName;
        
    /**
     * Instantiate validator
     * 
     * @param Zend_Form_Element $elementConfirm The element which needs to be matched
     * @return void
     **/
    public function __construct(Zend_Form_Element $elementConfirm)
    {
        $this->_confirmLabel = $elementConfirm->getLabel();
        $this->_confirmName = $elementConfirm->getName();
    }
    
    public function isValid($value, $context = null)
    {
        $value = (string) $value;
        $this->_setValue($value);
        
        if (is_array($context))
        {
            if((isset($context[$this->_confirmName])) && ($value == $context[$this->_confirmName]))
            {
                return true;
            }
        }
        elseif ((is_string($context)) && ($value == $context))
        {
            return true;
        }
        
        $this->_error(self::NOT_MATCH);
        return false;
    }

}
