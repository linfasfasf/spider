<?php

class spider extends controller{
	public $url;

	public function __construct($param = ''){
		if(!empty($param)){
			foreach($param as $key =>$val){
				$this->$key	= $val;
			}
		}
	}

	public function setUrl($url = ''){
		$this->url	= $url;
	}
}
