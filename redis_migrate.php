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

$mysqli->close();