<?php
class homecontroller extends admincontroller{
	public function __construct(){
		parent::__construct();
		echo 'this is homecontroller ';
	}

	public function config(){
		$config	= $this->load->config('config');
		var_dump($config);
	}

	public function model(){
		$this->load->model('testmodel');
		$this->testmodel->test();
	}
}
