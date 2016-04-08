<?php

if(!function_exists('load_class')){
	function &load_class($class, $directory , $module = ''){
		static $_class	= array();

		
		//does the class exists
		if(isset($_class[$module][$class])){
			return $_class[$module][$class];
		}elseif(isset($_class['system'][$class])){
			$module	 = 'system';
			return $_class[$module][$class];
		}

		$name	=  FALSE;
		
		//look for the class first appliction/ then  system/
		if(!empty($module)){
			if(file_exists($file = APPPATH.'/'.$module.'/'.$directory.'/'.$class.'.php')){
				$name	= $class;
				if(class_exists($name) === FALSE){
					if(DEBUG){
						echo 'DEBUG: INFO LOADING application file '.$file.PHP_EOL;
					}
					require_once(APPPATH.'/'.$module.'/'.$directory.'/'.$class.'.php');
				}
			}

		}else{//if not set module look for system directory
			if(file_exists($file = SYSDIR.'/'.$directory.'/'.$class.EXT)){
				$name	=  $class;
				if(class_exists($name) === FALSE){
					if(DEBUG){
						echo 'DEBUG: INFO LOADING system file '.$file.PHP_EOL;
					}
					require_once($file);
					$module	= 'system';
				}
			}
		}

		if($name === FALSE){
			exit('Unable to locate the specified class '.$class.'.php');
		}

		$_class[$module][$class]	= new $name();
		is_loaded($class);
		return $_class[$module][$class];

	}
}

if(!function_exists('is_loaded')){
	function &is_loaded($class){
		static $_is_loaded	= array();
		if($class != ''){
			$_is_loaded[strtolower($class)]	= $class;
		}
		return $_is_loaded;
	}
}

if(!function_exists('get_config')){
	function &get_config($file_name, $module=''){
		static $_config;

		//check if the base config file exists
		if(! file_exists($file_path = APPPATH.'/config/'.$file_name.'.php')){
			exit('The base config file does not exitst! ');
		}

		require_once($file_path);

		//if the module config file exists ,we need require common config and mudule config
		if( file_exists($module_file_path = APPPATH.'/'.$module.'/config/config.php')){
			require_once($module_file_path);
		}

		$_config =& $config;
		return $_config;
	}
}


if(!function_exists('config_item')){
	function config_item($item, $file_name = 'config', $module = ''){
		static $_config_item 	= array();

		if(!isset($_config_item[$item])){
			$config		=& get_config($file_name, $module);
			if(!isset($config[$item])){
				return FALSE;
			}
			$_config_item[$item]	= $config[$item];
		}
		return $_config_item[$item];
	}
}

if(!function_exists('set_config')){
	function set_config($config = array()){

		if(count($_config) == 0){
			get_config('config');
		}

		if(count($config) > 0){
			foreach($_config as $key =>$val){
				$_config[$key]	= $val;
			}
		}
		return $_config;
	}
}

if(!function_exists('get_file')){
	function get_file($dir){
		if(!is_dir($dir)){
			return false;
		}
		$result	= array();
		$cddir	= scandir($dir);
		foreach($cddir as $key =>$val){
			if(!in_array($val, array('.', '..'))){
				if(is_dir($dir.'/'.$val)){
					$result[$val]	= get_file($dir.'/'.$val);
				}else{
					$filename	= explode('.', $val);
					$result[]	= $filename[0];
				}
			}
		}
		return $result;
	}
}
