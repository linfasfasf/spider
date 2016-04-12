<?php

class requesthandler extends controller{

	public $url;

	public $RETURNTRANSFER 	= 1;

	public $cookie_enable 	= 0;

	public $CONNECTTIMEOUT 	= 15;

	public $TIMEOUT		= 15;

	public $referer;

	public $post;

	public $cookie_str;

	public $data;

	public $ch;

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
		$this->ch	= $ch;
		return $this;
	}

	public function doCurl(){
		$this->data	= curl_exec($this->ch);
		curl_close($this->ch);
	}
}
