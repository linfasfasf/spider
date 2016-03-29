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

function saveRecruit($redis, $mysqli){//简历同步脚本
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


function saveCompany($redis, $mysqli){//水滴信用同步脚本
	while (true) {
		//if ($companyId = $redis->rpop('companyId')) {
		if(false){
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
		}elseif($companyid	= $redis->rpop('companyidExt')){
			echo PHP_EOL.$companyid.PHP_EOL;
			$partnerQuery	= "values";
			while($partnerid  = $redis->rpop($companyid.':partnerid')){
				$stock_name	= $redis->rpop($companyid.':stock_name');
				$stock_capital	= $redis->rpop($companyid.':stock_capital');
				$proportion	= $redis->rpop($companyid.':proportion');
				$par_companyid	= $redis->rpop($companyid.':par_companyid');

				$partnerQuery	.= "($companyid, $partnerid, '$stock_name', '$stock_capital', $proportion, $par_companyid),";

			}
			$partnerQuery	= rtrim($partnerQuery, ",");

			$executeQuery	= "values";
			while($exec_id	= $redis->rpop($companyid.':exec_id')){
				$exec_name	= $redis->rpop($companyid.':exec_name');
				$filing_date	= $redis->rpop($companyid.':filing_date');
				$filing_no	= $redis->rpop($companyid.':filing_no');
				$exec_subject	= $redis->rpop($companyid.':exec_subject');
				$exec_status	= $redis->rpop($companyid.':exec_status');
				$exec_gov	= $redis->rpop($companyid.':exec_gov');

				$executeQuery	.= "($companyid, '$exec_name', '$filing_date', '$filing_no', '$exec_subject', '$exec_status', '$exec_gov', $exec_id),";
			}
			$executeQuery	= rtrim($executeQuery, ",");

			$courtQuery	= "values";
			while($docid	= $redis->rpop($companyid.':docid')){
				$docSubType	= $redis->rpop($companyid.':docSubtype');
				$docTitle	= $redis->rpop($companyid.':docTitle');
				$docTime	= $redis->rpop($companyid.':docTime');
				$docCode	= $redis->rpop($companyid.':docCode');
				$courtName	= $redis->rpop($companyid.':courtName');
				$docSubmitTime	= $redis->rpop($companyid.':docSubmitTime');

				$courtQuery	.= "($companyid, '$docSubType', '$docTitle', '$docTime', '$docCode', '$courtName', '$docSubmitTime', $docid),";
			}
			$courtQuery	= rtrim($courtQuery, ",");
			
			//执行数据库插入动作
			try{
				if(strlen($partnerQuery) != 6){
					$partnerQuery	= "insert into cre_partner_info (companyid, partnerid, stock_name, stock_capital, proportion, par_companyid)".$partnerQuery;
					if($mysqli->query($partnerQuery) && $mysqli->errno){
						$redis->lpush('errorList', $companyid);
					}
				}
				if(strlen($executeQuery) != 6){
					$executeQuery	= "insert into cre_company_execute (companyid, exec_name, filing_date, filing_no, exec_subject, exec_status, exec_gov, exec_id)".$executeQuery;
					if($mysqli->query($executeQuery) && $mysqli->errno){
						$redis->lpush('errorList', $companyid);
					}
				}
				if(strlen($courtQuery) != 6){
					$courtQuery	= "insert into cre_company_court (companyid, doc_sub_type, doc_title, doc_time, doc_code, court_name, doc_submit_time, docid)".$courtQuery;
					if($mysqli->query($courtQuery) && $mysqli->errno){
						$redis->lpush('errorList', $companyid);
					}
				}
				$mysqli->query("insert into cre_tmp (companyid) values ($companyid)");//将更新过的信息插入到临时表

			}catch(exception $e){
				var_dump($e);
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
