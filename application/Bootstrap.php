<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    /**
     * Bootstrap the view helpers
     * 
     * @return void
     */
    protected function _initViewHelpers()
    {
        $this->bootstrap('view');
        $this->bootstrap('db');
        $this->bootstrap('session');
        $view = $this->getResource('view');
		$view->addHelperPath(APPLICATION_PATH . '/views/helpers', 'App_View_Helper');
        $view->doctype('HTML5');
    }
    
    /**
     * Explicitly starts a Zend_Session
     *
     * @return void
     **/
    protected function _initSession()
    {
		if(isset($_POST[session_name()]))
		{
			Zend_Session::setId($_POST[session_name()]);
		}
        Zend_Session::start();
    }
    
    /**
     * Puts the main application configuration in the registry
     *
     * @return void
     * @author Lee Parker
     **/
    protected function _initConfig()
    {
        $config = new Zend_Config($this->getOptions());
        Zend_Registry::set('config', $config);
    }

	protected function _initFirePhp()
	{
		$logger = new Zend_Log();
		if(APPLICATION_ENV != 'production') {
			$writer = new Zend_Log_Writer_Firebug();
			set_error_handler('noticeLogger', E_NOTICE);
		} else {
			$writer = new Zend_Log_Writer_Mock();
		}
		$logger->addWriter($writer);
		Zend_Registry::set('logger', $logger);
	}

	protected function _initMetadataCache()
	{
		$cache = Zend_Cache::factory(
			'Core', 
			'File', 
			array('automatic_serialization' => true), 
			array(
				'file_name_prefix' => 'table_metadata', 
				'cache_file_umask' => '0666',
				'hashed_directory_umask' => '0777'
				)
			);
		Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);
	}
}

function debug($message, $label=null)
{
	$extras = array();
	if ($label!=null) {
		$extras['firebugLabel'] = $label;
	}
	Zend_Registry::get('logger')->debug($message,$extras);
}

function noticeLogger($errno, $errstr, $errfile, $errline)
{
	$message = "$errstr on line $errline of $errfile";
	debug($message, 'NOTICE');
}
