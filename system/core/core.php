<?php

define('lspider_verion', 0.1);

require(SYSDIR.'/core/function.php');


$CFG	=& load_class('config', 'core');


//$module 1  $controller 3 $method 5
switch($switch){
	case 1:
		$CFG->init($module);
		break;
	case 3:
		$CFG->init('',$controller);
		break;
	case 4:
		$CFG->init($module, $controller);
		break;
	case 5:
		$CFG->init('', '', $method);
		break;
	case 6:
		$CFG->init($module, '', $method);
		break;
	case 8:
		$CFG->init('', $controller, $method);
		break;
	case 9:
		$CFG->init($module, $controller, $method);
}


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
	if(!file_exists($file = $RTR->fetch_directory().'/'.$class)){
		exit('Unable to locate the specfied file, controller:'.$RTR->fetch_directory().' class:'.$class);
	}else{
		require_once($file);
		unset($SC);
		$SC	= new $class;
	}
}
call_user_func(array($SC, $method));

