<?php

class model {
	public function __construct(){

	}

	public function __get($key){
		$SC	= get_instance();
		return $SC->$key;
	}
}
