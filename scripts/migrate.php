#!/usr/bin/php
<?php
// Define path to application directory
defined('APPLICATION_PATH')
	|| define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
	|| define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
	realpath(APPLICATION_PATH . '/../library'),
	get_include_path(),
	)));

define('MIGRATIONS_PATH', APPLICATION_PATH . '/../data/migrations');

/** Zend_Application */
require_once 'Zend/Config/Ini.php';  


// Set up configuration info
$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
$db = $config->resources->db->params;
$host = $db->host;
$mysqli = new mysqli($host, $db->username, $db->password, $db->dbname);

// Gather files
$files = glob(MIGRATIONS_PATH . '/*.sql');
sort($files);
$result = $mysqli->query("select * from migrations");
$done = array();
if($result)
{
	while($row = $result->fetch_assoc())
	{
		$done[] = $row['filename'];
	}
	$result->free();
}

foreach($files as $file)
{
	$filename = basename($file);
	if(in_array($filename, $done))
		continue;

	echo "Running $filename \n";
	
	$sql = file_get_contents($file);
	$mysqli->multi_query($sql);
	do {
		$result = $mysqli->use_result();
		if($result)
			$result->close();
	} while ($mysqli->next_result()); 

	if($mysqli->error) {
		echo $mysqli->error;
		exit;
	}

	$mysqli->query('INSERT INTO `migrations` VALUES (NULL, "' . $filename . '", UNIX_TIMESTAMP())');
}

$mysqli->close();