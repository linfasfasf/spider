<?php

class SHUIDI extends Baseclass{

	public function run(){
		
		$this->main();
	}


	public function main(){
		$url = 'http://www.shuidixy.com/search?total=13264991&provinceCode=&npage=0&key=*';
		echo "run";
		$redis	= $this->getRedisConn();

		$num = 10;
		$this->getCompanyIdToRedisList($num);
		$companyid  = $redis->rpop('ext_companyid');
		$cid	    = $this->getCompanyCid($companyid);
		$this->getPartnerInfo($compayid, $cid, $redis);
		$this->getCourt($companyid, $redis);

	}

	public function getCourt($companyid, $redis){
		$url	= "http://m.shuidixy.com/mobile/v1/company/court/list?companyid=$companyid&pname=shuidixy&ptime=1458785303595
			&vkey=6178c88375309ae35923c286252695f2&pagesize=20";
		$courtJson	= $this->curl_simple($url);
		$court		= json_decode($courtJson, true);
		$content	= $court['data']['content'];
		foreach($content as $val){
			$docSubtype	= $val['docSubtype'];
			$docTitle	= $val['docTitle'];
			$docTime	= $val['docTime'];
			$docCode	= $val['docCode'];
			$courtName	= $val['courtName'];
			$docSubmitTime	= $val['docSubmitTime'];

			$redis->set($companyid.':docSubtype', $docSubtype);
			$redis->set($compnayid.':docTitle', $docTitle);
			$redis->set($companyid.':docTime', $docTime);
			$redis->set($companyid.':docCode', $docCode);
			$redis->set($companyid.':courtName', $courtName);
			$redis->set($companyid.':docSubmitTime', $docSubmitTime);
		}
	}

	public function getExecuteInfo($companyid, $redis){
		$url	= "http://m.shuidixy.com/mobile/v1/company/execute/list?companyid=$companyid&curpage=0&pagesize=20
			&pname=shuidixy&ptime=1458784378341&vkey=238fa439ea01710fc3a057628f4c2f2a";
		$executeJson	= $this->curl_simple($url);
		$execute	= json_decode($executeJson, true);
		$content	= $execute['data']['content'];
		foreach($content as $val){
			$execId		= $val['execId'];
			$execName	= $val['execName'];
			$filingDate	= $val['filingDate'];
			$filingNo	= $val['filingNo'];
			$execSubject	= $val['execSubject'];
			$execStatus	= $val['execStatus'];
			$execGov	= $val['exexGov'];

			$redis->set($companyid.':exec_id', $execId);
			$redis->set($companyid.':exec_name', $execName);
			$redis->set($companyid.':filing_date', $filingDate);
			$redis->set($companyid.':filing_no', $filingNo);
			$redis->set($companyid.':exec_subject', $execSubject);
			$redis->set($companyid.':exec_status', $execStatus);
			$redis->set($companyid.':exec_gov', $execStatus);
		}
	

	public function getPartnerInfo($companyid, $cid, $redis){
		$url	= "http://m.shuidixy.com/mobile/v1/company/partner/list?bizcompanyid=$companyid&companyid=$cid&curpage=0
			&pagesize=20&pname=shuidixy&ptime=1458722535689&userdevtype=1&vkey=32f4a8a2a9c87d2e87fd03966dfdc63e";
		$partnerJson	= $this->curl_simple($url);
		$partner	= json_decode($partnerJson, true);
		$content	= $partner['data']['content'];
		foreach($content as $val){
			$partnerId	= $val['partnerId'];
			$stockName	= $val['stockName'];
			$stockCapital	= $val['stockCapital'];
			$proportion	= $val['proportion'];
			$parCompanyId	= $val['companyId'];

			$redis->set($companyid.':partnerid', $partnerid);
			$redis->set($companyid.':stock_name', $stockName);
			$redis->set($companyid.':stock_capital', $stockCapital);
			$redis->set($companyid.':proportion', $proportion);
			$redis->set($companyid.':par_companyid', $parCompanyId);
		}
	}

	public function getCompanyCid(){
		$url  = "http://m.shuidixy.com/mobile/v1/company/detail?bizcompanyid=$companyid&pname=shuidixy&ptime=1458724844618&vkey=d92c3a3651b3f00764bfcfa938ca77da";
		$detailJson  = $this->curl_simple($url);
		$detail	     = json_decode($detailJson, true);
		return $detail['data']['cId'];

	}

	//将companyid推送到redis队列中
	public function getCompanyIdToRedisList($len, $redis){
		$unGetQuery = 'SELECT a.companyid FROM `cre_company_ext` a LEFT JOIN cre_partner_info b on 
						a.companyid = b.companyid WHERE b.companyid is null AND a.id<'.$len;//获取未爬取的companyid
		$mysqli = $this->sqliConnect();
		$queryRes = $mysqli->query($unGetQuery);
		while ($queryRes->fetch_array(MYSQLI_ASSOC)) {
			$redis->lpush('ext_companyid', $queryRes['companyid']);
		}
	}
}
