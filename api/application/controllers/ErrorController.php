<?php

class ErrorController extends Standard_Controller
{
	protected $_ajaxActions = array(
		'error' => 'json',
		);

	protected $_anonActions = array(
		'error',
		);

	public function errorAction()
	{
		$errors = $this->_getParam('error_handler');
		$response = array();
		switch ($errors->type) {
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
				// 404 error -- controller or action not found
				$this->getResponse()->setHttpResponseCode(404);
				$response['error'] = 'Page not found';
				break;

			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_OTHER:
				if($errors->exception instanceof Standard_Controller_AccessDeniedException) {
					$this->getResponse()->setHttpResponseCode(401);
					$response['error'] = 'Access Denied';
					$response['cause'] = $errors->exception->getMessage();
				}
				break;
			default:
				// application error
				$this->getResponse()->setHttpResponseCode(500);
				$response['error'] = 'Application error';
				break;
		}

		echo json_encode($response);
	}
}
