<?php

class SHUIDI extends Baseclass{

	public function run(){
		
		$this->main();
	}


	public function main(){
		$url = 'http://www.shuidixy.com/search?total=13264991&provinceCode=&npage=0&key=*';
		echo "run";
		$this->getSearchResult($url);
	}


	public function getSearchResult($url){
		// $res1 = file_get_contents($url);
		$res = $this->curl($url);
		// var_dump($res1);
		
		var_dump($res);
		// $this->saveDataToDeskTop($res1);
		// die();
		preg_match_all('/company_info_\d.*\.html/', $res, $searchMatch);
		
		foreach ($searchMatch[0] as $val) {
			var_dump($val);
			$this->getCompanyInfo('http://www.shuidixy.com/'.$val);

		}
	}

	public function getCompanyInfo($url){
		$companyRes = $this->curl($url);
		var_dump($companyRes);
		$matchRule = '/<h2>.*<\/h2>/';
		preg_match($matchRule, $companyRes, $companyMatch);
		$companyName = substr($companyMatch[0], 4, -5);
		preg_match('/法定代表.*[\s\S]{1}.*"/', $companyRes, $legelNameMatch);
		$legelName   = substr($legelNameMatch[0], 72, -1);
		preg_match('/注册资本.*[\s\S]{1}.*/', $companyRes, $registCapitalMatch);
		$registCapital = substr($registCapitalMatch[0], 51, -8);
		preg_match('/登记状态.*[\s\S]{1}.*/', $companyRes, $companyStatusMatch);
		$companyStatus = substr($companyStatusMatch[0], 51, -8);
		preg_match('/公司类型.*[\s\S]{1}.*/', $companyRes, $companyTypeMatch);
		$companyType   = substr($companyTypeMatch[0], 51, -8);
		preg_match('/营业期限.*[\s\S]{1}.*/', $companyRes, $bussTerMatch);
		$bussTerm 	   = substr($bussTerMatch[0], 51, -8);
		preg_match('/企业地址.*[\s\S]{1}.*/', $companyRes, $addMatch);
		$addre		   = substr($addMatch[0], 51, -8);
		preg_match_all('/<h4 tag_attr="sht".*\S[\s]+.*/', $companyRes, $manageMatch);
		$manage = array();
		foreach ($manageMatch[0] as $val) {
			preg_match('/>.*<\/h4/', $val, $manageName);
			preg_match('/>.*<\/h5/', $val, $manageJob);
			$manage[substr($manageName[0], 1, -4)] = substr($manageJob[0], 1, -4);
		}
		var_dump($manage);
		echo "111111111";
		$this->saveDataToDeskTop(var_export($manage, true),'txt');
		die(); 
	}

	public function getManage(){

	}
}