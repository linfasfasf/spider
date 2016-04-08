<?php

class Router {
	//the config class
	public $config;

	public $module;
	
	//current class name
	public $controller;

	//current method name
	public $method;


	public $directory;


	public function __construct(){
		$this->config		= & load_class('config', 'core');

	}

	public function init(){
		$this->module		= $this->config->get_module();
		$this->directory	= APPPATH.'/'.$this->module.'/controller';
		$this->controller	= $this->config->get_controller();
		$this->method		= $this->config->get_method();		
	}

	//load the components and controller floder
	public function auto_load(){
		//allow the componenets no exists
		if(DEBUG){
			echo 'DEBUG: INFO CLASS: router ,METHOD: auto_load'.PHP_EOL;
		}
		if(is_dir(APPPATH.'/'.$this->module.'/components')){
			$file_arr	= get_file(APPPATH.'/'.$this->module.'/components');
			if(count($file_arr) > 0){
				foreach($file_arr as $file_name){
					if(DEBUG){
						echo 'DEBUG: INFO LOADING COMPONENTS  module'.$this->module.' file '.$file_name.PHP_EOL;
					}
					load_class($file_name, 'components', $this->module);
				}
			}
		}

		// version 1.1 now not autoload the controller floder
#		if(!is_dir(APPPATH.'/'.$this->module.'/controller')){
#			exit($this->module.' controller directory does not exists ');
#		}else{
#			$controller_arr	= get_file(APPPATH.'/'.$this->module.'/controller');
#			if(count($controller_arr) > 0){
#				foreach($controller_arr as $controller){
#					if(DEBUG){
#						echo 'DEBUG: INFO LOADING COTROLLER  module '.$this->module.' file '.$file_name.PHP_EOL;
#					}
#					load_class($controller, 'controller', $this->module);
#				}
#			}
#		}

	}

	public function fetch_directory(){
		return $this->directory;
	}

	public function fetch_controller(){
		return $this->controller;
	}

	public function fetch_method(){
		return $this->method;
	}

	

}
