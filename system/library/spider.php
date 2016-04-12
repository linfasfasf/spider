<?php

class spider extends controller{

	public $url;

	public function __construct(){
		$this->requestHandler	= $this->load->library('requestHandler');
		$this->dispathcher	= $this->load->library('dispatcher');
		$this->queneManager	= $this->load->library('queneManger');
	}

	public function setUrl($url){
		$this->url	= $url;
	}

	public function 
}
