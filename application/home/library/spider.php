<?php

class spider extends controller{
	public $url;

	public $requesthandler;

	public $ch;

	public $filter	= new statusfilter($this->ch);

	public function __construct(){
		$this->load->library('dispatcher');
		$this->load->library('requesthandler');
		$this->load->library('statusfilter');
	}

	public function setUrl($url = ''){
		$this->requesthandler->setUrl($url);
	}

	public function doCurl(){
		$this->requesthandler->doCurl();
		$this->ch	= $this->requesthandler->getHandler();
	}


}
