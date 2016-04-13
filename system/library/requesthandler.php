<?php

class requesthandler extends dispatcher{

	public $url;

	public $RETURNTRANSFER 	= 1;

	public $cookie_enable 	= 0;

	public $CONNECTTIMEOUT 	= 15;

	public $TIMEOUT		= 15;

	public $referer;

	public $post;

	public $cookie_str;


	public function __construct(){
		if(!file_exists($cookiefile = ROOT.'/assets/cookie.txt')){
			if(!is_dir($assets = ROOT.'/assets')){
				mkdir($assets, 0777);
			}
			touch($cookiefile);
		}
	}

	public function setUrl($url){
		$this->url	= $url;
	}
	

	public function curl(){
		$ch	= curl_init();

		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, $this->RETURNTRANSFER);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->CONNECTTIMEOUT);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->TIMEOUT);
		if(!empty($this->referer)){
			curl_setopt($ch, CURLOPT_REFERER, $this->referer);
		}
		if(!empty($post)){
			curl_setopt($ch, CURLOPT_POST,	1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		}
		if($cookie_enable){
			if(!empty($cookie_str)){//is cookie string 
				curl_setopt($ch, CURLOPT_COOKIE, $cookie_str);
			}else{
				curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiefile);
				curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiefile);
			}
		}
		self::$ch	= $ch;
		return $this;
	}


	public function doCurl(){
		self::$data	= curl_exec(self::$ch);
		
	}

	public function setCookieStr($cookie){
		$this->cookie_str	= $cookie;
	}

	public function setPostData($post){
		$this->post	= $post;
	}

	public function getData(){
		return self::$data;
	}

	public function getHandler(){
		return self::$ch;
	}
}
