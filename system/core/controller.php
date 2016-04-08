<?php

class controller {
	private static $instance;

	public function __construct(){
		self::$instance	=& $this;

		//load config and init it , so the super class can get it 
		$this->config	=& load_class('config', 'core');

		$this->load	=& load_class('loader', 'core');
		$this->load->autoload();
		
	}

	public static function &get_instance(){
		return self::$instance;
	}
}
