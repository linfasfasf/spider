<?php

class loader {
	protected $model_path;

	protected $config;

	protected $_lp_model = array();

	protected $_lp_library	= array();
	public function __construct(){
		$this->config	=& load_class('config', 'core');
	}


	public function initialize(){
		
	}

	public function library($library, $param =''){
		$module	= $this->config->module;
		if(is_array($library)){
			foreach($library as $class){
				$this->_lp_load_library($calss, $module);
			}
		}
		$lib_file	= APPPATH.'/'.$module.'/library/'.$library.EXT;
		if(file_exists($lib_file)){
			$this->_lp_load_library($library, $module);
		}
	}


	public function _lp_load_library($class, $module){
		if(!file_exists(APPPATH.'/'.$module.'/library/'.strtolower($class).EXT)){
			exit('Unable to locate the library you have specified:'.$calss);
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
		if(is_array($model)){
			foreach($model as $base){
				$this->model($base);
			}
		}

		if($model = ''){
			return;
		}
		
		if($name = ''){
			$name	= $model;
		}

		$SC 	=& get_instance();
		if(isset($SC->$name)){
			exit(' the model name you are loading is the name of resource that is already bening used :'. $name);
		}
		$model	= strtolower($model);
		if(!file_exists($file = APPPATH.'/'.$this->module.'/model/'.$name.EXT)){
			exit('Unable to locate the model you have specified: '.$model);
		}
		if($param != FALSE && ! class_exists('LP_DB')){
			if($param = TRUE){
				$param	= '';
			}
			$SC->load->database($param);
		}

		if(! class_exists('LP_Model')){
			& load_class('model', 'core');
		}
		require_once($file);
		$SC->$name	= new $model();
		$this->_lp_model[] = $name;
		return ;
	}

	public function database($param){
		$SC	=& get_instance();
		require_once(SYSDIR.'/database/DB.php');
		$SC->db	= '';
		$SC->db	=& DB($param);
	}
	
}
