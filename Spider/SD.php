<?php
class SD extends Baseclass{

	public function run(){
		
		$this->main();
	}

	public function main(){
		$currentPage 	= 15833;
		while (true) {
			sleep(0.5);
			$currentPage++;
			$searchResJson 	= $this->getSearchResult($currentPage);
			$searchRes 		= json_decode($searchResJson, true);
			var_dump($searchRes);
			if (!$searchRes['statusCode']) {
				echo "Api:get search result fail";
				// die();
			}

			$contentRes = $searchRes['data']['content'];
			$this->saveContentIntoRedis($contentRes);
		}
		

	}

	public function getSearchResult($currentPage){
		$key		 = '*';
		$pageSize 	 = 20;
		$searchApi = 'http://m.shuidixy.com/mobile/v1/company/search?curpage='.$currentPage.'&key='.$key.'&pagesize='.$pageSize.'&pname=shuidixy&ptime=1458527316344&userdevtype=1&vkey=875dfeaa9568bf546e437ff7e1e515fe';
		return $this->curl_simple($searchApi);		

	}


	public function curl_simple($url){
		$ch	= curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
		$data = curl_exec($ch);
		if(curl_errno($ch)){
			if(curl_getinfo($ch, CURLINFO_HTTP_CODE) ==502){
				sleep(5);
				$data 	= $this->curl_simple($url);			
			}else{
				sleep(3);
				$data	= $this->curl_simple($url);
			}
		}
		if(curl_getinfo($ch, CURLINFO_HTTP_CODE) == 502){
			sleep(5);
			$data 	= $this->curl_simple($url);
		}
		
		return $data;
	}


	public function saveContentIntoRedis($contentRes){
		foreach ($contentRes as $content) {
					
			$companyId	 		= $content['companyId'];
			$companyName 		= $content['companyName'];
			$province 	 		= $content['province'];
			$companyType 		= $content['companyType'];
			$legalPerson 		= $content['legalPerson'];
			$capital	 		= $content['capital'];
			$companyCode 		= $content['companyCode'];
			$businessScope 		=  $content['businessScope'];
			$companyStatus 		= $content['companyStatus'];
			$partnerStockName 	= $content['partnerStockName'];
			$employeeName 		= $content['employeeName'];
			$trademark			= $content['trademark'];
			$authority			= $content['authority'];
			$operationStartdate = $content['operationStartdate'];
			$companyAddress		= $content['companyAddress'];
			$redis = $this->getRedisConn();
			if (!$redis->exists('companyid:'.$companyId)) {
				
				$redis->set($companyId.':companyName', $companyName);
				$redis->set($companyId.':province', $province);
				$redis->set($companyId.':companyType', $companyType);
				$redis->set($companyId.':legalPerson', $legalPerson);
				$redis->set($companyId.':capital', $capital);
				$redis->set($companyId.':companyCode', $companyCode);
				$redis->set($companyId.':businessScope', $businessScope);
				$redis->set($companyId.':companyStatus', $companyStatus);
				if (is_array($partnerStockName)) {
					foreach ($partnerStockName as $partnerStockeNameVal) {
						$redis->lpush($companyId.":partnerStockName", $partnerStockeNameVal);	
					}
				}
				if (is_array($employeeName)) {
					foreach ($employeeName as $employeeNameVal) {
						$redis->lpush($companyId.':employeeName', $employeeNameVal);
					}			
				}		
				if (is_array($trademark)) {
					foreach ($trademark as $trademarkVal) {
						$redis->lpush($companyId.':trademark', $trademarkVal);
					}
				}
				$redis->set($companyId.':authority', $authority);
				$redis->set($companyId.':operationStartDate', $operationStartdate);
				$redis->set($companyId.':companyAddress', $companyAddress);
				$redis->lpush('companyId', $companyId);
				$redis->set('companyid:'.$companyId, $companyId);
			}

		}
	}

		
		
		


	
}
