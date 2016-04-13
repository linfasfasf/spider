<?php

class loader {
	protected $model_path;

	protected $config;

	protected $_lp_model = array();

	protected $_lp_library	= array();
	public function __construct(){
		$this->config	=& load_class('config', 'core');
		return $this;
	}


	public function autoload(){
		if(file_exists($file = APPPATH.'/'.$this->config->get_module().'/config/autoload.php')){
			include_once($file);
		}else{
			require_once(APPPATH.'/config/autoload.php');
		}

		if(!isset($autoload)){
			return FALSE;
		}

		if(count($autoload['config']) > 0){
			foreach($autoload['config'] as $key =>$val){
				$this->config->load($val);
			}
		}

		if(count($autoload['library']) > 0){
			foreach($autoload['library'] as $key =>$val){
				$this->library($val);
			}
		}
		//load database 
		$this->database();

		if(count($autoload['model']) > 0){
			$this->model($autoload['model']);
		}
		return $this;
	}

	public function library($library, $param =''){
		$module	= $this->config->get_module();
		if(is_array($library)){
			foreach($library as $class){
				$this->_lp_load_library($class, $module);
			}
		}
		$lib_file	= APPPATH.'/'.$module.'/library/'.$library.EXT;
		if(file_exists($lib_file)){
			$this->_lp_load_library($library, $module);
		}
	}


	public function _lp_load_library($class, $module){
		if(file_exists($file = APPPATH.'/'.$module.'/library/'.strtolower($class).EXT)){
			require_once($file);
		}elseif(file_exists($file_lib = SYSDIR.'/library/'.strtolower($class).EXT)){
			require_once($file_lib);
		}else{
			exit('ERROR: MSG the library '.$class.' is not exists !');
		}

		if(file_exists($file = APPPATH.'/'.$module.'/config/'.$class.EXT)){
			require_once($file);
		}

		$SC	=& get_instance();
		if($config != NULL){
			$SC->$class	= new $class($config);
		}else{
			$SC->$class	= new $class();
		}

	}

	public function config($module = ''){
		$SC	=& get_instance();
		$SC->config->init($module);
	}


	//$model the name of class
	//$name  alias name of the model
	public function model($model, $name= '', $param = ''){
		if(DEBUG){
			echo "DEBUG: INFO CLASS: loader METHOD: model ".PHP_EOL;
		}
		if(is_array($model)){
			foreach($model as $base){
				$this->model($base);
			}
		}
		if($model == ''){
			return;
		}
		if($name == ''){
			$name	= $model;
		}

		$SC 	=& get_instance();
		if(isset($SC->$name)){
			exit(' the model name you are loading is the name of resource that is already bening used :'. $name);
		}
		$model	= strtolower($model);
		if(!file_exists($file = APPPATH.'/'.$this->config->get_module().'/model/'.$name.EXT)){
			exit('Unable to locate the model you have specified: '.$model);
		}
		if($param != FALSE && ! class_exists('LP_DB')){
			if($param = TRUE){
				$param	= '';
			}
			$SC->load->database($param);
		}

		if(! class_exists('LP_Model')){
			 load_class('model', 'core');
		}
		require_once($file);
		$SC->$name	= new $model();
		$this->_lp_model[] = $name;
		return ;
	}

	public function database($param = ''){
		$SC	=& get_instance();
		require_once(SYSDIR.'/core/database/DB.php');
		$SC->db	= '';
		$SC->db	= DB($this->config->get_module(), $param);
	}
	
}
