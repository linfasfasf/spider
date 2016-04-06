<?php


	$options	= getopt('h:b:m:f:',array("debug:"));
	var_dump($options);

	if(isset($options['m'])){
		//static $module;
		$module		=  $options['m'];
	}

	if(isset($options['h'])){
		showCmdMsg();

	}

	if(!isset($options['m'])){
		showCmdMsg();	
	}

	if(isset($options['debug'])){
		define('DEBUG', TRUE);
	}else{
		define('DEBUG', FALSE);
	}
	

	if(DEBUG){
		error_reporting(-1);
	}

	$system		= 'system';
	define('ROOT', str_replace('\\','/',dirname(__FILE__)));
	define('EXT', '.php');
	define('SYSDIR', ROOT.'/system');
	define('APPPATH', ROOT.'/application');


	if(!is_dir($system)){
		mkdir($system);
	}


	function showCmdMsg(){
		echo "----------------------------------------".PHP_EOL;
		echo "| the option of spider                  |".PHP_EOL;
		echo "----------------------------------------".PHP_EOL;
		echo "|  -b  point the spider you want to run |".PHP_EOL;
		echo "----------------------------------------".PHP_EOL;
		echo "|  -t  the max time of spider runing    |".PHP_EOL;
		echo "----------------------------------------".PHP_EOL;
		exit();
	}
	require_once SYSDIR.'/core/core.php';
