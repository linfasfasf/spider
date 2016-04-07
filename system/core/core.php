<?php

define('lspider_verion', 0.1);

require(SYSDIR.'/core/function.php');


$CFG	=& load_class('config', 'core');

//if do not set the module run by default
if( !empty($module) && !empty($method)){
	$CFG->init($module, $method);
}elseif(!empty($module)){
	$CFG->init($module);
}else{
	$CFG->init();//if not set module but set method , give up method
}

//init the super class controller,to load 
$controller	=& load_class('controller', 'core');


//load class router 
$RTR	=& load_class('router', 'core');
$RTR->auto_load($module);//require compoenents and controller floder


