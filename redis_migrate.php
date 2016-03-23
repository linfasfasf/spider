<?php
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
try{
	$val = $redis->ping();
	if (!$val) {
		die('Redis connect fail !');
	}
}catch(RedisException $e){
	die('redis connect faul');
}

$mysqli  =  new mysqli('localhost', 'root', '123456', 'qnd');
$mysqli->set_charset("utf8");
if ($mysqli->connect_errno) {
	die('mysqli connect fail !');
}
saveCompany($redis, $mysqli);

function saveRecruit($redis, $mysqli){
	while (true) {
		
		if($uid = $redis->rpop('uidList')){
			echo PHP_EOL.$uid.PHP_EOL;
			$user_name  = $redis->get($uid.':username');
			$password   = $redis->get($uid.':password');

			$flag 		= $redis->get($uid.':flag');
			$content	= $redis->get($uid.':content');
			$cid		= $redis->get($uid.':cid');

			$sex		= $redis->get($uid.':sex');
			$name 		= $redis->get($uid.':name');
			$province	= $redis->get($uid.':province');
			$city		= $redis->get($uid.':city');
			$education	= $redis->get($uid.':education');
			$experience	= $redis->get($uid.':experience');
			$age		= $redis->get($uid.':age');


			$creUserSql = "insert into cre_user (uid, user_name, password) values({$uid}, '$user_name', '$password')";

			$creUserContentSql = "insert into cre_user_content (uid, flag, content, cid) values({$uid}, '$flag', '$content', '$cid')";

			$creUserInfoSql    = "insert into cre_user_info (uid, sex, name, province, city, education, experience, age) values ({$uid}, {$sex}, '{$name}', '$province', '$city', '$education', '$experience', '$age')";

			if ($result = $mysqli->query($creUserSql) &&$mysqli->query($creUserInfoSql)&& $mysqli->query($creUserContentSql)&& $mysqli->errno) {
				$redis->lpush('errorList', $uid);
				}else{
					$redis->delete(array($uid.':username', $uid.':password', $uid.':flag', $uid.':content', $uid.':cid', $uid.':sex', $uid.':name', $uid.':province', $uid.':city' ,$uid.':education', $uid.':experience', $uid.':age'));
					$redis->lpush('successList', $uid);
				}
		}else{
			echo "sleep";
			sleep(5);
		}
	}
}


function saveCompany($redis, $mysqli){
	while (true) {
		if ($companyId = $redis->rpop('companyId')) {
			echo PHP_EOL.$companyId.PHP_EOL;
			$companyName		= stripslashes($redis->get($companyId.':companyName'));
			$province 		= $redis->get($companyId.':province');
			$companyType 		= $redis->get($companyId.':companyType');
			$legalPerson		= $redis->get($companyId.':legalPerson');
			$capital		= $redis->get($companyId.':capital');
			$companyCode  		= $redis->get($companyId.':companyCode');
			$businessScope 		= $redis->get($companyId.':businessScope');
			$companyStatus 		= $redis->get($companyId.':companyStatus');
			$authority		= $redis->get($companyId.':authority');
			$operationStartdate 	= $redis->get($companyId.':operationStartdate');
			$companyAddress 	= $redis->get($companyId.':companyAddress');
			

			$PSNqueryValue		= 'values';
			$ENqueryValue		= 'values';
			$TMqueryValue		= 'values';
			while($partnerStockName = stripslashes($redis->rpop($companyId.':partnerStockName'))){
				$PSNqueryValue .= "($companyId, '$partnerStockName'),";
			}
			$PSNqueryValue = rtrim($PSNqueryValue, ',');
			while($emploeeName = $redis->rpop($companyId.':employeeName')){
				$ENqueryValue  .= "($companyId, '$emploeeName'),";
			}
			$ENqueryValue = rtrim($ENqueryValue, ",");
			while($trademark =  $redis->rpop($companyId.':trademark')){
				$TMqueryValue  .= "($companyId, '$trademark'),";
			}
			$TMqueryValue  = rtrim($TMqueryValue, ",");

			try{
				if(strlen($PSNqueryValue) != 6){
					$PSNqueryValue = "insert into cre_partner_stock_name (companyid, partner_stock_name)".$PSNqueryValue;
					if($mysqli->query($PSNqueryValue) && $mysqli->errno){
						$redis->lpush('errorList',$companyid);
					}
				}
				if(strlen($ENqueryValue != 6)){
					$ENqueryValue = "insert into cre_company_employee  (companyid, employee_name)".$ENqueryValue;
					if($mysqli->query($ENqueryValue) && $mysqli->errno){
						$redis->lpush('errorList', $companyid);
					}	
				}
				if(strlen($trademark) != 6){
					$TMqueryValue  = "insert into cre_company_trademark (companyid, trademark)".$TMqueryValue;
					if($mysqli->query($TMqueryValue) && $mysqli->errno){
						$redis->lpush('errorList', $companyid);
					}
				}

				$companyQuery  = "insert into cre_company  (companyid, company_name, legal_person, bussine_scope, company_status, company_address)
					values($companyId, \"$companyName\", '$legalPerson', '$businessScope',  '$companyStatus', '$companyAddress')";
				if($mysqli->query($companyQuery) && $mysqli->errno){
					$redis->lpush('errorLIst', $companyId);
				}

				$companyExtQuery = "insert into cre_company_ext (companyid, province, company_type, capital, company_code, authority, operation_start_date) 
					values($companyId, '$province', '$companyType', '$capital', '$companyCode', '$authority', '$operationStartdate')";
				if($mysqli->query($companyExtQuery) && $mysqli->errno){
					$redis->lpush('errorList', $companyId);
				}else{
						$redis->delete(array($companyId.':companyName',$companyId.':province',$companyId.':companyType',
						$companyId.':legalPerson', $companyId.':capital', $companyId.':companyCode', $companyId.':businessScope', 
						$companyId.':companyStatus', $companyId.':authority', $companyId.':operationStartDate', 
						$companyId.':companyAddress'));
				}
			}catch(Exception$e){
				$mysqli->rollback();
			}
			
			$mysqli->commit();
		}else{
			echo "sleep";
			sleep(5);
		}
	}
}

$mysqli->close();
