<?php


class Config {

	public $config		= array();

	public $is_loaded	= array();

	public $_config_path	= array();

	public  static $module;

	public  static $method;

	public  static $controller;
	
	//load the common config file , to get the framwork config info
	public function __construct(){


	}
	
	//load the common config and module config file ,and the module config is effecter than common file
	public function init($module = '', $controller = '', $method = ''){
		
		$score	= param_switch($module, $controller, $method);
		switch($score){
			case 1:
				$this->config		=& get_config('config', $module);
				self::$module		= $module;
				if(empty($this->config['default_controller'])){
					exit('ERROR: MSG '.$module.' config file default_controller not exists !');
				}
				self::$controller	= $this->config['controller'];
				if(empty($this->config['default_method'])){
					exit('ERROR: MSG '.$module.' config file default_method not exists !');
				}
				self::$method		= $this->config['method'];
				break;
			case 3:
				$this->config		=& get_config('config');
				self::$module		= $this->config['default_module'];
				self::$controller	= $controller;
				self::$method		= $this->config['default_method'];
				break;
			case 5:
				$this->config		= get_config('config');
				self::$module		= $this->config['default_module'];
				self::$controller	= $this->config['default_controller'];
				self::$method		= $method;
				break;
			case 4:
				$this->config		= get_config('config', $module);
				self::$module		= $module;
				self::$controller	= $controller;
				if(empty($this->config['method'])){
					exit('ERROR: MSG '.$module.' config file default_method not exists !');
				}
				self::$method		= $this->config['default_method'];
				break;
			case 6:
				$this->config		= get_config('config', $module);
				self::$module		= $module;
				if(empty($this->config['default_controller'])){
					exit('ERROR: MSG '.$module.' config file default_controller not exists !');
				}
				self::$controller	= $this->config['controller'];
				self::$method		= $method;
				break;
			case 8:
				$this->config		= get_config('config');
				self::$module		= $this->config['default_module'];
				self::$controller	= $controller;
				self::$method		= $method;
				break;
			case 9:
				$this->config		= get_config('config', $module);
				self::$module		= $module;
				self::$controller	= $controller;
				self::$method		= $method;
				break;
			default :
				exit('ERROR MSG param error');
		}

		var_dump($this->config);
		
		if(!empty($module)){ 
			if(file_exists(APPPATH.'/'.$module.'/config/config.php')){
				$this->config	= get_config('config', $module);
			}else{
				exit('the module '.$module.' config did not exists!');
			}
		}else{
			if(file_exists(APPPATH.'/'.$this->config['default_module'].'/config/config.php')){
				$this->config	=  get_config('config', $this->config['default_module']);
			}else{
				exit('the module '.$this->config['default_module'].' config did not exists!');
			}
		}
		
		$this->is_loaded	= $this->config;
		return $this;	
	}
	
	public function load($file_name = '', $section = false){
		if(empty($this->is_load)){
			$this->init();
		}
		if(in_array($file_name, $this->is_loaded, TRUE)){
			return true;
		}
		if(!empty($this->module)){
			if(file_exists($file = APPPATH.'/'.self::$module.'/config/'.$file_name.EXT)){
				include_once($file);
			}else{
				return FALSE;
			}
		}else{
			if(file_exists($file = APPPATH.'/config/'.$file_name.EXT)){
				include_once($file);
			}else{
				return FALSE;
			}
		}

		if($section == TRUE){
			if(isset($this->config[$file_name])){
				$this->config[$file_name]	= array_merge($this->config[$file_name], $config);
			}else{
				$this->config[$file_name]	= $config;
			}
		}else{
			$this->config	= array_merge($this->config, $config);
		}

		$this->is_loaded[]	= $file_name;
		unset($config);
		
	}


	//get the config item info
	public function item ($item, $index = ''){
		if($index == ''){
			if(!isset($this->config[$item])){
				return FALSE;
			}
			return $this->config[$item];
		}else{
			if(!isset($this->config[$index])){
				return FALSE;
			}
			if(!isset($this->config[$index][$item])){
				return FALSE;
			}
			return $this->config[$index][$item];
		}
		
	}

	//set the config item info
	public function set_item($config = array()){
		if(count($config) == 0){
			return FALSE;
		}
		foreach($config as $key => $val){
			$this->config[$key] 	= $val;
		}
	}

	public function get_controller(){
		return self::$controller;
	}

	public function get_module(){
		return self::$module;
	}

	public function get_method(){
		return self::$method;
	}
}
