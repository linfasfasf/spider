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

