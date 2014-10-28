<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
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

	/**
	 * Bootstrap autoloader for application resources
	 * 
	 * @return Zend_Application_Module_Autoloader
	 */
	protected function _initAutoload()
	{
		$autoloader = new Zend_Application_Module_Autoloader(array(
			'namespace' => 'App',
			'basePath'  => dirname(__FILE__),
			));
		// look in dto directory as well
		$autoloader->addResourceType('dtos', '/dtos/', 'Dto');

		return $autoloader;
	}

}
