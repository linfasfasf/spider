<?php


	$options	= getopt('h:c:m:f:',array("debug:"));
	var_dump($options);
	$switch	= 0;

	if(isset($options['m'])){
		$module		=  $options['m'];
	}else{
		$module		= '';
	}

	if(isset($options['f'])){
		$method		=  $options['f'];
	}else{
		$method		= '';
	}

	if(isset($options['c'])){
		$controller	= $options['c'];
	}else{
		$controller	= '';
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
		echo 'debug close';
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
