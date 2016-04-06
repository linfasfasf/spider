<?php

define('lspider_verion', 0.1);

require(SYSDIR.'/core/function.php');


$CFG	=& load_class('config', 'core');

//if do not set the module run by default
if(empty($module)){
	$CFG->init();
}else{
	$CFG->init($module);
}

//init the super class controller,to load 
$controller	=& load_class('controller', 'core');


//load class router 
$RTR	=& load_class('router', 'core');
$RTR->auto_load($module);//require compoenents and controller


