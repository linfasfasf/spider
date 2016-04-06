<?php


class Config {

	public $config		= array();
	public $is_loaded	= array();
	public $_config_path	= array();
	public static $module;

	
	//load the common config file , to get the framwork config info
	public function __construct(){


	}
	
	//load the common config and module config file ,and the module config is effecter than common file
	public function init($module = ''){
		$this->config	=& get_config('config');
		var_dump($this->config);
		
		if(!empty($module)){
			$this->module	= $module;
			if(file_exists(APPPATH.'/'.$module.'/config/config.php')){
				$this->config	= get_config('config', $module);
			}else{
				exit('the module '.$module.' config did not exists!');
			}
		}else{
			$this->module	= $this->config['default_module'];
			if(file_exists(APPPATH.'/'.$this->config['default_module'].'/config/config.php')){
				$this->config	=  get_config('config', $this->config['default_module']);
			}else{
				exit('the module '.$this->config['default_module'].' config did not exists!');
			}
		}
		
		$this->is_loaded	= $this->config;
		return $this;	
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
}
