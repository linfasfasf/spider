<?php

/*
 *广度优先策略，先遍历所有上层URL，将匹配结果推送到后面的队列
 *深度优先策略，先遍历第一个URL，其他检测到的URL推送到待爬取队列中
 *PAGERANK 算法，对爬取到的URL进行投票算法，对于分值最高的URL先进行爬取，对于未知的URL先给与初值（50），然后进行第二轮计算
 * TODO:不同的抓取需求需要组装各种不同的curl规则，以至于需要多次重写curl方法
 * */


class SPIDER extends Baseclass{
	public function run(){
		echo "INFO: Start the spider".PHP_EOL;
		$config	= array(
			'allowDomain'=>false,//false不做限制，array('domain.com','get.com') 只允许在相应的站内进行爬取
			
		);
		$this->main();
	}

	public function main(){
		$redis = $this->getREdisConn();
		echo "INFO: Connect to the REDIS".PHP_EOL;

		while(true){
			if(!$url = $redis->rpop('urlist')){//如果数据为空，则从入口开始爬取
				$url	= 'http://news.baidu.com';
			}
			if(!$this->checkUrl($url)){
				if(DEBUG){
					echo "DEBUG: The url is broken ".PHP_EOL;
				}
				continue;
			}

			if(DEBUG){
				echo "DEBUG: Start to run the url: ".$url.PHP_EOL;
			}
			
			$data	= $this->curl($url);
			if(DEBUG){
				echo  "DEBUG: Check html if exists info we need".PHP_EOL;
			}
			$this->checkData($data);//数据检查
			if(DEBUG){
				echo "DEBUG: Get url from the html file ".PHP_EOL;
			}
			$this->getUrl($data, $redis);
			if(DEBUG){
				echo "DEBUG: Put the url to history".PHP_EOL;
			}
			$redis->hset('urlhistory', $url, 1);
			echo PHP_EOL;
		}
			
	}

	public function checkUrl($url){
		$headerArr	= get_headers($url, 1);
		if(preg_match("/200/", $headerArr[0])){
			return true;
		}elseif(preg_match("/302/", $headerArr[0])){
			return true;
		}else{
			return false;
		}
	}

	public function getUrl($data, $redis){
		if(preg_match_all("/<a.*<\/a>/", $data, $matchUrl)){
			foreach($matchUrl[0] as $url1){
				if(preg_match("/http.*?\"/", $url1, $matchUrl2)){
					foreach($matchUrl2 as $url){
						$url	= substr($url, 0, -1);
						//检查URL是否抓取过，如果在urlhistory中则不存入待抓取队列中
						if(!$redis->hexists('urlhistory', $url)){
							$this->pushUrlToRedis($url, $redis);
						}
					}
				}
			}
		}
	}

	//采用深度优先遍历策略
	public function pushUrlToRedis($url, $redis){
		return $redis->lpush("urlist", $url);
	}
	//数据监测
	public function checkData($data){
		$preg	= $this->getPregInfo();
		if(preg_match_all("/$preg/", $data, $match)){
			$res	= $match[0];
			$this->saveInfoToMysql($data, $url);
		}
	}

	public function getPregInfo(){
		return '去哪贷';
	}

	public function saveInfoToMysql($data, $url){
		$mysqli		= $this->sqliConnect();
		$saveQuery	= "insert into cre_spider_html (data, url) values($data, $url)";
		if($mysqli->query($saveQuery) && $mysqli->errno){
			if(DEBUG){
				echo 'DEBUG: Insert into mysql fail'.PHP_EOL;
			}
			return false;
		}
		if(DEBUG){
			echo 'DEBUG: Insert into mysql success'.PHP_EOL;
		}
		return true;
	}

	public function getNewsUrl($data){
		//preg_match_all()
	}

	public function spiderCurl($url){
		$ch	= curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);	
		curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
		curl_exec($ch);
	}

}
