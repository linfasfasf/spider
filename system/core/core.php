<?php

define('lspider_verion', 0.1);

require(SYSDIR.'/core/function.php');


$CFG	=& load_class('config', 'core');


//$module 1  $controller 3 $method 5
$CFG->init($module, $controller, $method);

//load class router 
$RTR	=& load_class('router', 'core');
$RTR->init();

function &get_instance(){
	return controller::get_instance();
}

//init the super class controller,to load 
$controller	=& load_class('controller', 'core');

$RTR->auto_load($module);//require compoenents floder

$class	=  $RTR->fetch_controller();
$method	=  $RTR->fetch_method();
if(!class_exists($RTR->fetch_controller())){
	if(!file_exists($file = $RTR->fetch_directory().'/'.$class.EXT)){
		exit('Unable to locate the specfied file, controller:'.$RTR->fetch_directory().' class:'.$class);
	}else{
		require_once($file);
		unset($SC);
		$SC	= new $class;
	}
}
call_user_func(array($SC, $method));

