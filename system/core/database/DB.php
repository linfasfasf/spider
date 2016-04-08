<?php


if(!function_exists('DB')){
	function DB($module, $param = ''){
		if(!file_exists($file = APPPATH.'/'.$module.'/config/database.php')){
			exit('The configuration file database.php does not exists .');
		}
		require_once($file);
		if(!isset($db) && count($db) == 0){
			exit('No database connection setting was found in the database config file ');
		}
		
		if(empty($param)){
			if(file_exists($module_file = APPPATH.'/'.$module.'/config/database.php')){
				require_once($module_file);
			}elseif(file_exists($common_file = APPPATH.'/config/database.php')){
				require_once($common_file);
			}else{
				exit('The configuration file does not exists .');
			}
			$param	= $db[$active_group];
		}
		if(! class_exists('LP_DB')){
			eval('class LP_DB {}');
		}

		require_once(SYSDIR.'/core/database/'.$param['dbdriver'].'/'.$param['dbdriver'].'_driver.php');
		$driver	= 'LP_DB_'.$param['dbdriver'];
		$DB	= new $driver($param);
		if($DB->autoinit  == TRUE){
			$DB->init();
		}
		return $DB;
	}
}
