<?php

class dispatcher extends controller{

	public static $ch;

	public static $data;

	public function closeCh(){
		curl_close(self::$ch);
	}	

	public function flushData(){
		self::$data	= '';
	}
}
