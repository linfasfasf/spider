<?php

class SHUIDI extends Baseclass{

	public function run(){
		
		$this->main();
	}


	public function main(){
		$url = 'http://www.shuidixy.com/search?total=13264991&provinceCode=&npage=0&key=*';
		echo "run";
		$redis	= $this->getRedisConn();

		$num 	= 10;
		$i	= 0;
		$this->getCompanyIdToRedisList($num, $redis);
		while($num > $i){
			$companyid  = $redis->rpop('ext_companyid');
			$cid	    = $this->getCompanyCid($companyid);
			var_dump($cid);
			$partnerRes	= $this->getPartnerInfo($companyid, $cid, $redis);
			$executeRes	= $this->getExecuteInfo($companyid, $cid, $redis);
			$courtRes	= $this->getCourt($companyid, $cid, $redis);
			if($partnerRes || $executeRes || $courtRes){
				$redis->lpush('companyidExt', $companyid);
			}
			$i++;
		}

	}

	public function getCourt($companyid, $cid, $redis){
		$url	= "http://m.shuidixy.com/mobile/v1/company/court/list?companyid=$cid&pname=shuidixy&ptime=1458785303595&vkey=6178c88375309ae35923c286252695f2&pagesize=20";
		$courtJson	= $this->curl_simple($url);
		$court		= json_decode($courtJson, true);
		if($court['statusCode'] == 1){
			echo PHP_EOL.'insert court info into redis'.PHP_EOL;
			$content	= $court['data']['content'];
			//var_dump($url);
			var_dump($content);
			foreach($content as $val){
				$docSubtype	= $val['docSubtype'];
				$docTitle	= $val['docTitle'];
				$docTime	= $val['docTime'];
				$docCode	= $val['docCode'];
				$courtName	= $val['courtName'];
				$docSubmitTime	= $val['docSubmitTime'];
				$docid		= $val['id'];


				$redis->lpush($companyid.':docSubtype', $docSubtype);
				$redis->lpush($companyid.':docTitle', $docTitle);
				$redis->lpush($companyid.':docTime', $docTime);
				$redis->lpush($companyid.':docCode', $docCode);
				$redis->lpush($companyid.':courtName', $courtName);
				$redis->lpush($companyid.':docSubmitTime', $docSubmitTime);
				$redis->lpush($companyid.':docid', $docid);
			}
			return true;
		}else{
			echo PHP_EOL.'court info not exitsts'.PHP_EOL;
			return false;
		}
	}

	public function getExecuteInfo($companyid, $cid, $redis){
		$url	= "http://m.shuidixy.com/mobile/v1/company/execute/list?companyid=$cid&curpage=0&pagesize=20&pname=shuidixy&ptime=1458784378341&vkey=238fa439ea01710fc3a057628f4c2f2a";
		$executeJson	= $this->curl_simple($url);
		$execute	= json_decode($executeJson, true);
		if($execute['statusCode'] == 1){
			echo PHP_EOL.'insert execute info into redis'.PHP_EOL;
			$content	= $execute['data']['content'];
			//var_dump($content);
			foreach($content as $val){
				$execId		= $val['execId'];
				$execName	= $val['execName'];
				$filingDate	= $val['filingDate'];
				$filingNo	= $val['filingNo'];
				$execSubject	= $val['execSubject'];
				$execStatus	= $val['execStatus'];
				$execGov	= $val['execGov'];


				$redis->lpush($companyid.':exec_id', $execId);
				$redis->lpush($companyid.':exec_name', $execName);
				$redis->lpush($companyid.':filing_date', $filingDate);
				$redis->lpush($companyid.':filing_no', $filingNo);
				$redis->lpush($companyid.':exec_subject', $execSubject);
				$redis->lpush($companyid.':exec_status', $execStatus);
				$redis->lpush($companyid.':exec_gov', $execGov);
			}
			return true;
		}else{
			echo PHP_EOL.'execute info not exists'.PHP_EOL;
			return false;
		}
	}
	

	public function getPartnerInfo($companyid, $cid, $redis){
		$url	= "http://m.shuidixy.com/mobile/v1/company/partner/list?bizcompanyid=$companyid&companyid=$cid&curpage=0&pagesize=20&pname=shuidixy&ptime=1458722535689&userdevtype=1&vkey=32f4a8a2a9c87d2e87fd03966dfdc63e";
		var_dump($url);
		$partnerJson	= $this->curl_simple($url);
		$partner	= json_decode($partnerJson, true);
		if($partner['statusCode'] == 1){
			echo PHP_EOL.'insert partner info into redis'.PHP_EOL;
			$content	= $partner['data']['content'];
			var_dump($content);
			foreach($content as $val){
				$partnerId	= $val['partnerId'];
				$stockName	= $val['stockName'];
				$stockCapital	= $val['stockCapital'];
				$proportion	= $val['proportion'];
				$parCompanyId	= $val['companyId'];

				
				var_dump($partnerId);
				$redis->lpush($companyid.':partnerid', $partnerId);
				$redis->lpush($companyid.':stock_name', $stockName);
				$redis->lpush($companyid.':stock_capital', $stockCapital);
				$redis->lpush($companyid.':proportion', $proportion);
				$redis->lpush($companyid.':par_companyid', $parCompanyId);
			}
			return true;
		}else{
			echo PHP_EOL.'partner info not exists'.PHP_EOL;
			return false;
		}				
	}

	public function getCompanyCid($companyid){
		var_dump($companyid);
		$url  = "http://m.shuidixy.com/mobile/v1/company/detail?bizcompanyid=$companyid&pname=shuidixy&ptime=1458724844618&vkey=d92c3a3651b3f00764bfcfa938ca77da";
		$detailJson  = $this->curl_simple($url);
		$detail	     = json_decode($detailJson, true);
		return $detail['data']['cId'];

	}

	//将companyid推送到redis队列中
	public function getCompanyIdToRedisList($len, $redis){
		$unGetQuery = 'SELECT a.companyid FROM `cre_company_ext` a LEFT JOIN cre_tmp b on 
						a.companyid = b.companyid WHERE b.companyid is null AND a.id<'.$len;//获取未爬取的companyid
		$mysqli = $this->sqliConnect();
		$queryRes = $mysqli->query($unGetQuery);
		while ($Res  = $queryRes->fetch_array(MYSQLI_ASSOC)) {
			$redis->lpush('ext_companyid', $Res['companyid']);
		}
	}
}
