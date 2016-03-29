<?php

class SPIDER extends Baseclass{
	public function run(){
		$this->main();
	}

	public function main(){
		$redis = $this->getREdisConn();
		if(!$url = $redis->rpop('urlist')){
			$url	= 'http://news.baidu.com';
		}

		$data	= $this->curl($url);
		var_dump($data);
		if(!$data){
			
		}
	}

	public function getNewsUrl($data){
		//preg_match_all()
	}

}
