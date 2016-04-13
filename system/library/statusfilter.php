<?php
/**
 *get curl info 
 * */
class statusfilter extends dispatcher {
	
	public $curlInfo;

	public function __construct(){
		
	}

	public function initCheck(){
		if(empty(self::$ch)){
			exit("ERROR MSG the curl handler was empty, please set the requesthandler first .");
		}
		$this->getInfo();
		return TRUE;
	}

	public function checkHttpCodeEquel($code){
		if(!is_numeric($code)){
			return FALSE;
		}
		if(count($this->curlInfo) < 1){
			$this->getInfo();
		}
		if($this->curlInfo['http_code'] == $code){
			return TRUE;
		}
		return FALSE;
	}

	public function getInfo(){
		if(empty(self::$ch)){
			exit("ERROR MSG the curl handler was empty, please set the requesthandler first .");
		}
		$this->curlInfo	= curl_getinfo(self::$ch);
		return $this;
	}
	
	public function getHeaderSize(){
		if(count($this->curlInfo) < 1){
			$this->getInfo();
		}
		return $this->curlInfo['header_size'];
	}

	public function getRequestSize(){
		if(count($this->curlInfo) < 1){
			$this->getInfo();
		}
		return $this->curlInfo['request_size'];
	}

	public function getContentType(){
		if(count($this->curlInfo) < 1){
			$this->getInfo();
		}
		return $this->curlInfo['content_type'];
	}
}
