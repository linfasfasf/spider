<?php

class loader {
	protected $model_path;

	protected $config;

	public function __construct(){
		$this->config	=& load_class('config', 'core');
	}


	public function initialize(){
		
	}

	public function library($library, $param =''){
		$module	= $this->config->module;
		if(is_array($library)){
			foreach($library as $class){
				$this->_lp_load_class($calss, $module);
			}
		}
		$lib_file	= APPPATH.'/'.$module.'/library/'.$library.EXT;
		if(file_exists($lib_file)){
			$this->_lp_load_class($library, $module);
		}
	}


	public function _lp_load_class($class, $module){
		if(!file_exists(APPPATH.'/'.$module.'/library/'.strtolower($class).EXT)){
			return FALSE;
		}
	}

	public function config(){
		$SC	=& get_instance();
		$SC->config->init();
	}
	
}
