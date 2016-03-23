<?php
class SD extends Baseclass{

	public function run(){
		
		$this->main();
	}

	public function main(){
		$currentPage 	= 0;
		while (true) {
			sleep(0.5);
			$currentPage++;
			$searchResJson 	= $this->getSearchResult($currentPage);
			$searchRes 		= json_decode($searchResJson, true);
			var_dump($searchRes);
			if (!$searchRes['statusCode']) {
				echo "Api:get search result fail";
				die();
			}

			$contentRes = $searchRes['data']['content'];
			$this->saveContentIntoRedis($contentRes);
			// die();
		}
		

	}

	public function getSearchResult($currentPage){
		$key		 = '*';
		$pageSize 	 = 20;
		$searchApi = 'http://m.shuidixy.com/mobile/v1/company/search?curpage='.$currentPage.'&key='.$key.'&pagesize='.$pageSize.'&pname=shuidixy&ptime=1458527316344&userdevid=0b41226148657c8cd4bf24ffed1da6ec&userdevtype=1&vkey=875dfeaa9568bf546e437ff7e1e515fe';
		return file_get_contents($searchApi);		
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


		}
	}

		
		
		


	
}