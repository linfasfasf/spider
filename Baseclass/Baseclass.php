<?php
class Baseclass {
	static private $_time = array();
	static protected $frame_conf;
	protected $load;

	public function __construct(){
		$this->frame_conf = require_once(ROOT.'/frameworkconfig.php');
		var_dump($this->frame_conf);
		if ($this->frame_conf['redis']) {
			$this->checkUpdateRedis();
		}
	}

	

	public function test(){
		echo "string";
	}

	public function getFrameConf(){
		return $this->frame_conf;
	}

	//检查是否更新redis
	public function checkUpdateRedis(){
		$redis   = $this->getRedisConn();
		$mysqli  = $this->sqliConnect();
		echo "intoupdateredis";
		$query 	 = 'select uid from cre_user order by id desc limit 1';
		$uid_arr = $mysqli->query($query)->fetch_array(MYSQLI_ASSOC);
		var_dump($uid_arr);
		if( !$redis->exists('uid:'.$uid_arr['uid'])){//如果数据库中最新的数据不再redis中即更新redis
			echo "update redis";
			$query 	 = 'select * from cre_user';
			$result  = $mysqli->query($query);
			$pipe    =  $redis->multi();
			$redis->flushall();
			while ($result_arr = $result->fetch_array(MYSQLI_ASSOC)) {
				$pipe->set('uid:'.$result_arr['uid'], $result_arr['uid']);
				$pipe->exec();
			}
		}

		$mysqli->close();
	}

	//获取地区代码
	public function getAreaCode($area){
		foreach ($this->area as $value) {
			// echo $value['name'];
			if(strstr($value['name'], $area)){
				return $value['id'];
			}
		}
		return 'this area do not exists.';
	}

	//将要插入到creUser表中的数据写入到redis中
	public function insertIntoCreUser($uid, $username, $password){
		$redis = $this->getRedisConn();
		if ($redis->set($uid.':username', $username) && $redis->set($uid.':password', $password)){
			return true;
		}
		return false;
	}

	//将要插入到creUserContent中的数据写入到redis中
	public function insertIntoCreUserContent($uid, $flag, $content, $cid){
		$redis = $this->getRedisConn();
		if ($redis->set($uid.':flag', $flag) && $redis->set($uid.':content', $content)&& $redis->set($uid.':cid', $cid)) {
			 return true; 
		}
		return false;
	}

	//将要插入到creUserInfo中的数据写入到redis中
	public function insertIntoCreUserInfo($uid, $sex, $name, $province, $city, $education, $experience, $age){
		$redis = $this->getRedisConn();
		if ($redis->set($uid.':sex', $sex) && $redis->set($uid.':name', $name) && $redis->set($uid.':province', $province) && $redis->set($uid.':city', $city) &&$redis->set($uid.':education', $education) && $redis->set($uid.':experience', $experience) && $redis->set($uid.':age', $age) ) {
			return true;
		}
		return false;
	}

	//将uid数据插入到redis队列中
	public function insertUidIntoRedis($uid){
		$redis =  $this->getRedisConn();
		if ($redis->lPush('uidList', $uid)) {
			return true;
		}
		return false;
	}

	public function getRedisConn(){
		$redis = new Redis();
		$redis->connect('127.0.0.1', 6379);

		try {
			$val = $redis->ping();
			if (!$val) {
				die('redis ping fail');
			}
		} catch (RedisException $e) {
			die('can not connect  to redis!');
		}
		return $redis;
	}


	public function time_check($time_lit){
		if (empty(self::$_time['begin'])) {
			self::$_time['begin'] = time();
			return true;
		}elseif ((self::$_time['begin'] + $time_lit) >= time()) {
			return true;
		}
		return false;
	}

	public function curl($url, $post=null, $cookie_str='', $referer=''){

        if (!file_exists($cookie = ROOT.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'cookie.txt')) {
        	if (!is_dir(ROOT.DIRECTORY_SEPARATOR.'assets')) {
        		mkdir(ROOT.DIRECTORY_SEPARATOR.'assets', 0755);
        	}
        	touch($cookie);
        }
        $ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.116 Safari/537.36');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        if (!empty($referer)) {
        	curl_setopt($ch, CURLOPT_REFERER, $referer);
        }
        if (!empty($post)) {
            curl_setopt($ch, CURLOPT_POST,1);
        	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        if (!empty($cookie_str)) {
        	curl_setopt($ch, CURLOPT_COOKIE, $cookie_str);
        }else{
	        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
	        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
    	}
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST , false);

        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

	/*
	* 保存log信息
	*/
	public function saveLog($log, $class='', $uid=''){
		$path = ROOT.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'log';
		if (!is_dir($path)) {
			mkdir($path, 0777);
		}

		if (!empty($uid) && !empty($class)) {
			$data = date("Y-m-d H:m:s",time()).' class='.$class.' uid='.$uid.PHP_EOL.'error_info='.$log.PHP_EOL;
		}elseif (!empty($class)) {
			$data = date("Y-m-d H:m:s",time()).' class='.$class.PHP_EOL.'error_info= '.$log.PHP_EOL;
		}else{
			$data = date("Y-m-d H:m:s",time()).'error_info= '.$log.PHP_EOL;
		} 
		
		file_put_contents($path.DIRECTORY_SEPARATOR.'spider_error_info'.date("Y-m-d", time()).'.txt', $data.PHP_EOL, FILE_APPEND);
	}

	public function sqliConnect(){
        $db_host="localhost";                                           //连接的服务器地址
        $db_user="root";                                                  //连接数据库的用户名
        $db_psw="123456";                                                  //连接数据库的密码
        $db_name="qnd";                                           //连接的数据库名称
        $mysqli=new mysqli();
        $mysqli->connect($db_host,$db_user,$db_psw,$db_name);
        $mysqli -> set_charset('utf8');
        if (mysqli_connect_errno()){
            //注意mysqli_connect_error()新特性
            die('Unable to connect!'). mysqli_connect_error();
        }
        return $mysqli;
    }

	/*
         * 生成随机字符串
         */
    public function creatRandStr($len=8, $type='alnum') {
        switch($type){
            case 'alpha':
                $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'alnum':
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'numeric':
                $pool = '0123456789';
                break;
            case 'nozero':
                $pool = '123456789';
                break;
            default :
                return 'type no exist';
        }
        $str = '';
        for($i=0;$i<$len;$i++){
            $str .=substr($pool,mt_rand(0,strlen($pool)-1),1);
        }
        return $str;
    }


	public function getUniqueUid() {
        $str = $this->creatRandStr(6, 'numeric');
        if (strlen(floor($str)) != 6){
            $str = $this->getUniqueUid();
        }

       	if ($this->frame_conf['redis']) {
       		$redis  =  $this->getRedisConn();
       		if ($redis->exists('uid:'.$str)){
               	$str = $this->getUniqueUid();
           	}	
       	}else{
       		$mysqli = $this->sqliConnect();
       		$sql 	= 'select uid from cre_user where uid ='.$str;
            $result=$mysqli->query($sql)->fetch_assoc();	
            if ($result) {
            	$str = $this->getUniqueUid();
            }
        }            
                  
        return $str;
	}


	protected $area =array(array("id"=>'1099','fid'=>'0','name'=>'行政区划'),
					array("id"=>'1100','fid'=>'1099','name'=>'北京市'),
					array("id"=>'1101','fid'=>'1100','name'=>'北京市'),
				
					array("id"=>'1200','fid'=>'1099','name'=>'天津市'),
					array("id"=>'1201','fid'=>'1200','name'=>'天津市'),
				
					array("id"=>'1300','fid'=>'1099','name'=>'河北省'),
					array("id"=>'1301','fid'=>'1300','name'=>'石家庄市'),
					array("id"=>'1302','fid'=>'1300','name'=>'唐山市'),
					array("id"=>'1303','fid'=>'1300','name'=>'秦皇岛市'),
					array("id"=>'1304','fid'=>'1300','name'=>'邯郸市'),
					array("id"=>'1305','fid'=>'1300','name'=>'邢台市'),
					array("id"=>'1306','fid'=>'1300','name'=>'保定市'),
					array("id"=>'1307','fid'=>'1300','name'=>'张家口市'),
					array("id"=>'1308','fid'=>'1300','name'=>'承德市'),
					array("id"=>'1309','fid'=>'1300','name'=>'沧州市'),
					array("id"=>'1310','fid'=>'1300','name'=>'廊坊市'),
					array("id"=>'1311','fid'=>'1300','name'=>'衡水市'),
				
					array("id"=>'1400','fid'=>'1099','name'=>'山西省'),
					array("id"=>'1401','fid'=>'1400','name'=>'太原市'),
					array("id"=>'1402','fid'=>'1400','name'=>'大同市'),
					array("id"=>'1403','fid'=>'1400','name'=>'阳泉市'),
					array("id"=>'1404','fid'=>'1400','name'=>'长治市'),
					array("id"=>'1405','fid'=>'1400','name'=>'晋城市'),
					array("id"=>'1406','fid'=>'1400','name'=>'朔州市'),
					array("id"=>'1407','fid'=>'1400','name'=>'晋中市'),
					array("id"=>'1408','fid'=>'1400','name'=>'运城市'),
					array("id"=>'1409','fid'=>'1400','name'=>'忻州市'),
					array("id"=>'1410','fid'=>'1400','name'=>'临汾市'),
					array("id"=>'1411','fid'=>'1400','name'=>'吕梁市'),
				
					array("id"=>'1500','fid'=>'1099','name'=>'内蒙古'),
					array("id"=>'1501','fid'=>'1500','name'=>'呼和浩特市'),
					array("id"=>'1502','fid'=>'1500','name'=>'包头市'),
					array("id"=>'1503','fid'=>'1500','name'=>'乌海市'),
					array("id"=>'1504','fid'=>'1500','name'=>'赤峰市'),
					array("id"=>'1505','fid'=>'1500','name'=>'通辽市'),
					array("id"=>'1506','fid'=>'1500','name'=>'鄂尔多斯市'),
					array("id"=>'1507','fid'=>'1500','name'=>'呼伦贝尔市'),
					array("id"=>'1508','fid'=>'1500','name'=>'巴彦淖尔市'),
					array("id"=>'1509','fid'=>'1500','name'=>'乌兰察布市'),
					array("id"=>'1522','fid'=>'1500','name'=>'兴安盟'),
					array("id"=>'1525','fid'=>'1500','name'=>'锡林郭勒盟'),
					array("id"=>'1529','fid'=>'1500','name'=>'阿拉善盟'),
				
					array("id"=>'2100','fid'=>'1099','name'=>'辽宁省'),
					array("id"=>'2101','fid'=>'2100','name'=>'沈阳市'),
					array("id"=>'2102','fid'=>'2100','name'=>'大连市'),
					array("id"=>'2103','fid'=>'2100','name'=>'鞍山市'),
					array("id"=>'2104','fid'=>'2100','name'=>'抚顺市'),
					array("id"=>'2105','fid'=>'2100','name'=>'本溪市'),
					array("id"=>'2106','fid'=>'2100','name'=>'丹东市'),
					array("id"=>'2107','fid'=>'2100','name'=>'锦州市'),
					array("id"=>'2108','fid'=>'2100','name'=>'营口市'),
					array("id"=>'2109','fid'=>'2100','name'=>'阜新市'),
					array("id"=>'2110','fid'=>'2100','name'=>'辽阳市'),
					array("id"=>'2112','fid'=>'2100','name'=>'铁岭市'),
					array("id"=>'2113','fid'=>'2100','name'=>'朝阳市'),
					array("id"=>'2114','fid'=>'2100','name'=>'葫芦岛市'),
					array("id"=>'2115','fid'=>'2100','name'=>'盘锦市'),
				
				
					array("id"=>'2200','fid'=>'1099','name'=>'吉林省'),
					array("id"=>'2201','fid'=>'2200','name'=>'长春市'),
					array("id"=>'2202','fid'=>'2200','name'=>'吉林市'),
					array("id"=>'2203','fid'=>'2200','name'=>'四平市'),
					array("id"=>'2204','fid'=>'2200','name'=>'辽源市'),
					array("id"=>'2205','fid'=>'2200','name'=>'通化市'),
					array("id"=>'2206','fid'=>'2200','name'=>'白山市'),
					array("id"=>'2207','fid'=>'2200','name'=>'松原市'),
					array("id"=>'2208','fid'=>'2200','name'=>'白城市'),
					array("id"=>'2224','fid'=>'2200','name'=>'延边'),
				
					array("id"=>'2300','fid'=>'1099','name'=>'黑龙江省'),
					array("id"=>'2301','fid'=>'2300','name'=>'哈尔滨市'),
					array("id"=>'2302','fid'=>'2300','name'=>'齐齐哈尔市'),
					array("id"=>'2303','fid'=>'2300','name'=>'鸡西市'),
					array("id"=>'2304','fid'=>'2300','name'=>'鹤岗市'),
					array("id"=>'2305','fid'=>'2300','name'=>'双鸭山市'),
					array("id"=>'2306','fid'=>'2300','name'=>'大庆市'),
					array("id"=>'2307','fid'=>'2300','name'=>'伊春市'),
					array("id"=>'2308','fid'=>'2300','name'=>'佳木斯市'),
					array("id"=>'2309','fid'=>'2300','name'=>'七台河市'),
					array("id"=>'2310','fid'=>'2300','name'=>'牡丹江市'),
					array("id"=>'2311','fid'=>'2300','name'=>'黑河市'),
					array("id"=>'2312','fid'=>'2300','name'=>'绥化市'),
					array("id"=>'2327','fid'=>'2300','name'=>'大兴安岭'),
				
					array("id"=>'3100','fid'=>'1099','name'=>'上海市'),
					array("id"=>'3101','fid'=>'3100','name'=>'上海市'),
				
					array("id"=>'3200','fid'=>'1099','name'=>'江苏省'),
					array("id"=>'3201','fid'=>'3200','name'=>'南京市'),
					array("id"=>'3202','fid'=>'3200','name'=>'无锡市'),
					array("id"=>'3203','fid'=>'3200','name'=>'徐州市'),
					array("id"=>'3204','fid'=>'3200','name'=>'常州市'),
					array("id"=>'3205','fid'=>'3200','name'=>'苏州市'),
					array("id"=>'3206','fid'=>'3200','name'=>'南通市'),
					array("id"=>'3207','fid'=>'3200','name'=>'连云港市'),
					array("id"=>'3208','fid'=>'3200','name'=>'淮安市'),
					array("id"=>'3209','fid'=>'3200','name'=>'盐城市'),
					array("id"=>'3210','fid'=>'3200','name'=>'州市'),
					array("id"=>'3211','fid'=>'3200','name'=>'镇江市'),
					array("id"=>'3212','fid'=>'3200','name'=>'泰州市'),
					array("id"=>'3213','fid'=>'3200','name'=>'宿迁市'),
				
					array("id"=>'3300','fid'=>'1099','name'=>'浙江省'),
					array("id"=>'3301','fid'=>'3300','name'=>'杭州市'),
					array("id"=>'3302','fid'=>'3300','name'=>'宁波市'),
					array("id"=>'3303','fid'=>'3300','name'=>'温州市'),
					array("id"=>'3304','fid'=>'3300','name'=>'嘉兴市'),
					array("id"=>'3305','fid'=>'3300','name'=>'湖州市'),
					array("id"=>'3306','fid'=>'3300','name'=>'绍兴市'),
					array("id"=>'3307','fid'=>'3300','name'=>'金华市'),
					array("id"=>'3308','fid'=>'3300','name'=>'衢州市'),
					array("id"=>'3309','fid'=>'3300','name'=>'舟山市'),
					array("id"=>'3310','fid'=>'3300','name'=>'台州市'),
					array("id"=>'3311','fid'=>'3300','name'=>'丽水市'),
				
					array("id"=>'3400','fid'=>'1099','name'=>'安徽省'),
					array("id"=>'3401','fid'=>'3400','name'=>'合肥市'),
					array("id"=>'3402','fid'=>'3400','name'=>'芜湖市'),
					array("id"=>'3403','fid'=>'3400','name'=>'蚌埠市'),
					array("id"=>'3404','fid'=>'3400','name'=>'淮南市'),
					array("id"=>'3405','fid'=>'3400','name'=>'马鞍山市'),
					array("id"=>'3406','fid'=>'3400','name'=>'淮北市'),
					array("id"=>'3407','fid'=>'3400','name'=>'铜陵市'),
					array("id"=>'3408','fid'=>'3400','name'=>'安庆市'),
					array("id"=>'3410','fid'=>'3400','name'=>'黄山市'),
					array("id"=>'3411','fid'=>'3400','name'=>'滁州市'),
					array("id"=>'3412','fid'=>'3400','name'=>'阜阳市'),
					array("id"=>'3413','fid'=>'3400','name'=>'宿州市'),
					array("id"=>'3415','fid'=>'3400','name'=>'六安市'),
					array("id"=>'3416','fid'=>'3400','name'=>'亳州市'),
					array("id"=>'3417','fid'=>'3400','name'=>'池州市'),
					array("id"=>'3418','fid'=>'3400','name'=>'宣城市'),
				
					array("id"=>'3500','fid'=>'1099','name'=>'福建省'),
					array("id"=>'3501','fid'=>'3500','name'=>'福州市'),
					array("id"=>'3502','fid'=>'3500','name'=>'厦门市'),
					array("id"=>'3503','fid'=>'3500','name'=>'莆田市'),
					array("id"=>'3504','fid'=>'3500','name'=>'三明市'),
					array("id"=>'3505','fid'=>'3500','name'=>'泉州市'),
					array("id"=>'3506','fid'=>'3500','name'=>'漳州市'),
					array("id"=>'3507','fid'=>'3500','name'=>'南平市'),
					array("id"=>'3508','fid'=>'3500','name'=>'龙岩市'),
					array("id"=>'3509','fid'=>'3500','name'=>'宁德市'),
				
					array("id"=>'3600','fid'=>'1099','name'=>'江西省'),
					array("id"=>'3601','fid'=>'3600','name'=>'南昌市'),
					array("id"=>'3602','fid'=>'3600','name'=>'景德镇市'),
					array("id"=>'3603','fid'=>'3600','name'=>'萍乡市'),
					array("id"=>'3604','fid'=>'3600','name'=>'九江市'),
					array("id"=>'3605','fid'=>'3600','name'=>'新余市'),
					array("id"=>'3606','fid'=>'3600','name'=>'鹰潭市'),
					array("id"=>'3607','fid'=>'3600','name'=>'赣州市'),
					array("id"=>'3608','fid'=>'3600','name'=>'吉安市'),
					array("id"=>'3609','fid'=>'3600','name'=>'宜春市'),
					array("id"=>'3610','fid'=>'3600','name'=>'抚州市'),
					array("id"=>'3611','fid'=>'3600','name'=>'上饶市'),
				
					array("id"=>'3700','fid'=>'1099','name'=>'山东省'),
					array("id"=>'3701','fid'=>'3700','name'=>'济南市'),
					array("id"=>'3702','fid'=>'3700','name'=>'青岛市'),
					array("id"=>'3703','fid'=>'3700','name'=>'淄博市'),
					array("id"=>'3704','fid'=>'3700','name'=>'枣庄市'),
					array("id"=>'3705','fid'=>'3700','name'=>'东营市'),
					array("id"=>'3706','fid'=>'3700','name'=>'烟台市'),
					array("id"=>'3707','fid'=>'3700','name'=>'潍坊市'),
					array("id"=>'3708','fid'=>'3700','name'=>'济宁市'),
					array("id"=>'3709','fid'=>'3700','name'=>'泰安市'),
					array("id"=>'3710','fid'=>'3700','name'=>'威海市'),
					array("id"=>'3711','fid'=>'3700','name'=>'日照市'),
					array("id"=>'3712','fid'=>'3700','name'=>'莱芜市'),
					array("id"=>'3713','fid'=>'3700','name'=>'临沂市'),
					array("id"=>'3714','fid'=>'3700','name'=>'德州市'),
					array("id"=>'3715','fid'=>'3700','name'=>'聊城市'),
					array("id"=>'3716','fid'=>'3700','name'=>'滨州市'),
					array("id"=>'3717','fid'=>'3700','name'=>'菏泽市'),
				
					array("id"=>'4100','fid'=>'1099','name'=>'河南省'),
					array("id"=>'4101','fid'=>'4100','name'=>'郑州市'),
					array("id"=>'4102','fid'=>'4100','name'=>'开封市'),
					array("id"=>'4103','fid'=>'4100','name'=>'洛阳市'),
					array("id"=>'4104','fid'=>'4100','name'=>'平顶山市'),
					array("id"=>'4105','fid'=>'4100','name'=>'安阳市'),
					array("id"=>'4106','fid'=>'4100','name'=>'鹤壁市'),
					array("id"=>'4107','fid'=>'4100','name'=>'新乡市'),
					array("id"=>'4108','fid'=>'4100','name'=>'焦作市'),
					array("id"=>'4109','fid'=>'4100','name'=>'濮阳市'),
					array("id"=>'4110','fid'=>'4100','name'=>'许昌市'),
					array("id"=>'4112','fid'=>'4100','name'=>'三门峡市'),
					array("id"=>'4113','fid'=>'4100','name'=>'南阳市'),
					array("id"=>'4114','fid'=>'4100','name'=>'商丘市'),
					array("id"=>'4115','fid'=>'4100','name'=>'信阳市'),
					array("id"=>'4116','fid'=>'4100','name'=>'周口市'),
					array("id"=>'4117','fid'=>'4100','name'=>'驻马店市'),
					array("id"=>'4118','fid'=>'4100','name'=>'漯河市'),
				
					array("id"=>'4200','fid'=>'1099','name'=>'湖北省'),
					array("id"=>'4201','fid'=>'4200','name'=>'武汉市'),
					array("id"=>'4202','fid'=>'4200','name'=>'黄石市'),
					array("id"=>'4203','fid'=>'4200','name'=>'十堰市'),
					array("id"=>'4205','fid'=>'4200','name'=>'宜昌市'),
					array("id"=>'4206','fid'=>'4200','name'=>'襄阳市'),
					array("id"=>'4207','fid'=>'4200','name'=>'鄂州市'),
					array("id"=>'4208','fid'=>'4200','name'=>'荆门市'),
					array("id"=>'4209','fid'=>'4200','name'=>'孝感市'),
					array("id"=>'4210','fid'=>'4200','name'=>'荆州市'),
					array("id"=>'4211','fid'=>'4200','name'=>'黄冈市'),
					array("id"=>'4212','fid'=>'4200','name'=>'咸宁市'),
					array("id"=>'4213','fid'=>'4200','name'=>'随州市'),
					array("id"=>'4228','fid'=>'4200','name'=>'恩施'),
				
					array("id"=>'4300','fid'=>'1099','name'=>'湖南省'),
					array("id"=>'4301','fid'=>'4300','name'=>'长沙市'),
					array("id"=>'4302','fid'=>'4300','name'=>'株洲市'),
					array("id"=>'4303','fid'=>'4300','name'=>'湘潭市'),
					array("id"=>'4304','fid'=>'4300','name'=>'衡阳市'),
					array("id"=>'4305','fid'=>'4300','name'=>'邵阳市'),
					array("id"=>'4306','fid'=>'4300','name'=>'岳阳市'),
					array("id"=>'4307','fid'=>'4300','name'=>'常德市'),
					array("id"=>'4308','fid'=>'4300','name'=>'张家界市'),
					array("id"=>'4309','fid'=>'4300','name'=>'益阳市'),
					array("id"=>'4310','fid'=>'4300','name'=>'郴州市'),
					array("id"=>'4311','fid'=>'4300','name'=>'永州市'),
					array("id"=>'4312','fid'=>'4300','name'=>'怀化市'),
					array("id"=>'4313','fid'=>'4300','name'=>'娄底市'),
					array("id"=>'4331','fid'=>'4300','name'=>'湘西'),
				
					array("id"=>'4400','fid'=>'1099','name'=>'广东省'),
					array("id"=>'4401','fid'=>'4400','name'=>'广州市'),
					array("id"=>'4402','fid'=>'4400','name'=>'韶关市'),
					array("id"=>'4403','fid'=>'4400','name'=>'深圳市'),
					array("id"=>'4404','fid'=>'4400','name'=>'珠海市'),
					array("id"=>'4405','fid'=>'4400','name'=>'汕头市'),
					array("id"=>'4406','fid'=>'4400','name'=>'佛山市'),
					array("id"=>'4407','fid'=>'4400','name'=>'江门市'),
					array("id"=>'4408','fid'=>'4400','name'=>'湛江市'),
					array("id"=>'4409','fid'=>'4400','name'=>'茂名市'),
					array("id"=>'4412','fid'=>'4400','name'=>'肇庆市'),
					array("id"=>'4413','fid'=>'4400','name'=>'惠州市'),
					array("id"=>'4414','fid'=>'4400','name'=>'梅州市'),
					array("id"=>'4415','fid'=>'4400','name'=>'汕尾市'),
					array("id"=>'4416','fid'=>'4400','name'=>'河源市'),
					array("id"=>'4417','fid'=>'4400','name'=>'阳江市'),
					array("id"=>'4418','fid'=>'4400','name'=>'清远市'),
					array("id"=>'4419','fid'=>'4400','name'=>'东莞市'),
					array("id"=>'4420','fid'=>'4400','name'=>'中山市'),
					array("id"=>'4451','fid'=>'4400','name'=>'潮州市'),
					array("id"=>'4452','fid'=>'4400','name'=>'揭阳市'),
					array("id"=>'4453','fid'=>'4400','name'=>'云浮市'),
				
					array("id"=>'4500','fid'=>'1099','name'=>'广西省'),
					array("id"=>'4501','fid'=>'4500','name'=>'南宁市'),
					array("id"=>'4502','fid'=>'4500','name'=>'柳州市'),
					array("id"=>'4503','fid'=>'4500','name'=>'桂林市'),
					array("id"=>'4504','fid'=>'4500','name'=>'梧州市'),
					array("id"=>'4505','fid'=>'4500','name'=>'北海市'),
					array("id"=>'4506','fid'=>'4500','name'=>'防城港市'),
					array("id"=>'4507','fid'=>'4500','name'=>'钦州市'),
					array("id"=>'4508','fid'=>'4500','name'=>'贵港市'),
					array("id"=>'4509','fid'=>'4500','name'=>'玉林市'),
					array("id"=>'4510','fid'=>'4500','name'=>'百色市'),
					array("id"=>'4511','fid'=>'4500','name'=>'贺州市'),
					array("id"=>'4512','fid'=>'4500','name'=>'河池市'),
					array("id"=>'4513','fid'=>'4500','name'=>'来宾市'),
					array("id"=>'4514','fid'=>'4500','name'=>'崇左市'),
				
					array("id"=>'4600','fid'=>'1099','name'=>'海南省'),
					array("id"=>'4601','fid'=>'4600','name'=>'海口市'),
					array("id"=>'4602','fid'=>'4600','name'=>'三亚市'),
					array("id"=>'4603','fid'=>'4600','name'=>'三沙市'),
				
					array("id"=>'5000','fid'=>'1099','name'=>'重庆市'),
					array("id"=>'5001','fid'=>'5000','name'=>'重庆市'),
				
					array("id"=>'5100','fid'=>'1099','name'=>'四川省'),
					array("id"=>'5101','fid'=>'5100','name'=>'成都市'),
					array("id"=>'5103','fid'=>'5100','name'=>'自贡市'),
					array("id"=>'5104','fid'=>'5100','name'=>'攀枝花市'),
					array("id"=>'5105','fid'=>'5100','name'=>'泸州市'),
					array("id"=>'5106','fid'=>'5100','name'=>'德阳市'),
					array("id"=>'5107','fid'=>'5100','name'=>'绵阳市'),
					array("id"=>'5108','fid'=>'5100','name'=>'广元市'),
					array("id"=>'5109','fid'=>'5100','name'=>'遂宁市'),
					array("id"=>'5110','fid'=>'5100','name'=>'内江市'),
					array("id"=>'5113','fid'=>'5100','name'=>'南充市'),
					array("id"=>'5114','fid'=>'5100','name'=>'眉山市'),
					array("id"=>'5115','fid'=>'5100','name'=>'宜宾市'),
					array("id"=>'5116','fid'=>'5100','name'=>'广安市'),
					array("id"=>'5117','fid'=>'5100','name'=>'达州市'),
					array("id"=>'5118','fid'=>'5100','name'=>'雅安市'),
					array("id"=>'5119','fid'=>'5100','name'=>'巴中市'),
					array("id"=>'5120','fid'=>'5100','name'=>'资阳市'),
					array("id"=>'5132','fid'=>'5100','name'=>'阿坝'),
					array("id"=>'5133','fid'=>'5100','name'=>'甘孜'),
					array("id"=>'5134','fid'=>'5100','name'=>'凉山'),
					array("id"=>'5135','fid'=>'5100','name'=>'西昌'),
					array("id"=>'5136','fid'=>'5100','name'=>'乐山'),
				
				
					array("id"=>'5200','fid'=>'1099','name'=>'贵州省'),
					array("id"=>'5201','fid'=>'5200','name'=>'贵阳市'),
					array("id"=>'5202','fid'=>'5200','name'=>'六盘水市'),
					array("id"=>'5203','fid'=>'5200','name'=>'遵义市'),
					array("id"=>'5204','fid'=>'5200','name'=>'安顺市'),
					array("id"=>'5205','fid'=>'5200','name'=>'毕节市'),
					array("id"=>'5206','fid'=>'5200','name'=>'铜仁市'),
					array("id"=>'5223','fid'=>'5200','name'=>'黔西'),
					array("id"=>'5226','fid'=>'5200','name'=>'黔东'),
					array("id"=>'5227','fid'=>'5200','name'=>'黔南'),
				
					array("id"=>'5300','fid'=>'1099','name'=>'云南省'),
					array("id"=>'5301','fid'=>'5300','name'=>'昆明市'),
					array("id"=>'5303','fid'=>'5300','name'=>'曲靖市'),
					array("id"=>'5304','fid'=>'5300','name'=>'玉溪市'),
					array("id"=>'5305','fid'=>'5300','name'=>'保山市'),
					array("id"=>'5306','fid'=>'5300','name'=>'昭通市'),
					array("id"=>'5307','fid'=>'5300','name'=>'丽江市'),
					array("id"=>'5308','fid'=>'5300','name'=>'普洱市'),
					array("id"=>'5309','fid'=>'5300','name'=>'临沧市'),
					array("id"=>'5323','fid'=>'5300','name'=>'楚雄彝族自治州'),
					array("id"=>'5325','fid'=>'5300','name'=>'红河'),
					array("id"=>'5326','fid'=>'5300','name'=>'文山'),
					array("id"=>'5328','fid'=>'5300','name'=>'西双版纳'),
					array("id"=>'5329','fid'=>'5300','name'=>'大理'),
					array("id"=>'5331','fid'=>'5300','name'=>'德宏'),
					array("id"=>'5333','fid'=>'5300','name'=>'怒江'),
					array("id"=>'5334','fid'=>'5300','name'=>'迪庆'),
				
					array("id"=>'5400','fid'=>'1099','name'=>'西藏'),
					array("id"=>'5401','fid'=>'5400','name'=>'拉萨市'),
					array("id"=>'5402','fid'=>'5400','name'=>'日喀则市'),
					array("id"=>'5421','fid'=>'5400','name'=>'昌都地区'),
					array("id"=>'5422','fid'=>'5400','name'=>'山南地区'),
					array("id"=>'5424','fid'=>'5400','name'=>'那曲地区'),
					array("id"=>'5425','fid'=>'5400','name'=>'阿里地区'),
					array("id"=>'5426','fid'=>'5400','name'=>'林芝地区'),
				
					array("id"=>'6100','fid'=>'1099','name'=>'陕西省'),
					array("id"=>'6101','fid'=>'6100','name'=>'西安市'),
					array("id"=>'6102','fid'=>'6100','name'=>'铜川市'),
					array("id"=>'6103','fid'=>'6100','name'=>'宝鸡市'),
					array("id"=>'6104','fid'=>'6100','name'=>'咸阳市'),
					array("id"=>'6105','fid'=>'6100','name'=>'渭南市'),
					array("id"=>'6106','fid'=>'6100','name'=>'延安市'),
					array("id"=>'6107','fid'=>'6100','name'=>'汉中市'),
					array("id"=>'6108','fid'=>'6100','name'=>'榆林市'),
					array("id"=>'6109','fid'=>'6100','name'=>'安康市'),
					array("id"=>'6110','fid'=>'6100','name'=>'商洛市'),
				
					array("id"=>'6200','fid'=>'1099','name'=>'甘肃省'),
					array("id"=>'6201','fid'=>'6200','name'=>'兰州市'),
					array("id"=>'6202','fid'=>'6200','name'=>'嘉峪关市'),
					array("id"=>'6203','fid'=>'6200','name'=>'金昌市'),
					array("id"=>'6204','fid'=>'6200','name'=>'白银市'),
					array("id"=>'6205','fid'=>'6200','name'=>'天水市'),
					array("id"=>'6206','fid'=>'6200','name'=>'武威市'),
					array("id"=>'6207','fid'=>'6200','name'=>'张掖市'),
					array("id"=>'6208','fid'=>'6200','name'=>'平凉市'),
					array("id"=>'6209','fid'=>'6200','name'=>'酒泉市'),
					array("id"=>'6210','fid'=>'6200','name'=>'庆阳市'),
					array("id"=>'6211','fid'=>'6200','name'=>'定西市'),
					array("id"=>'6212','fid'=>'6200','name'=>'陇南市'),
					array("id"=>'6229','fid'=>'6200','name'=>'临夏'),
					array("id"=>'6230','fid'=>'6200','name'=>'甘南'),
				
					array("id"=>'6300','fid'=>'1099','name'=>'青海省'),
					array("id"=>'6301','fid'=>'6300','name'=>'西宁市'),
					array("id"=>'6302','fid'=>'6300','name'=>'海东市'),
					array("id"=>'6322','fid'=>'6300','name'=>'海北'),
					array("id"=>'6323','fid'=>'6300','name'=>'黄南'),
					array("id"=>'6325','fid'=>'6300','name'=>'海南'),
					array("id"=>'6326','fid'=>'6300','name'=>'果洛'),
					array("id"=>'6327','fid'=>'6300','name'=>'玉树'),
					array("id"=>'6328','fid'=>'6300','name'=>'海西'),
				
					array("id"=>'6400','fid'=>'1099','name'=>'宁夏'),
					array("id"=>'6401','fid'=>'6400','name'=>'银川市'),
					array("id"=>'6402','fid'=>'6400','name'=>'石嘴山市'),
					array("id"=>'6403','fid'=>'6400','name'=>'吴忠市'),
					array("id"=>'6404','fid'=>'6400','name'=>'固原市'),
					array("id"=>'6405','fid'=>'6400','name'=>'中卫市'),
				
					array("id"=>'6500','fid'=>'1099','name'=>'新疆'),
					array("id"=>'6501','fid'=>'6500','name'=>'乌鲁木齐市'),
					array("id"=>'6502','fid'=>'6500','name'=>'克拉玛依市'),
					array("id"=>'6521','fid'=>'6500','name'=>'吐鲁番地区'),
					array("id"=>'6522','fid'=>'6500','name'=>'哈密地区'),
					array("id"=>'6523','fid'=>'6500','name'=>'昌吉'),
					array("id"=>'6527','fid'=>'6500','name'=>'博尔塔拉'),
					array("id"=>'6528','fid'=>'6500','name'=>'巴音郭楞'),
					array("id"=>'6529','fid'=>'6500','name'=>'阿克苏地区'),
					array("id"=>'6530','fid'=>'6500','name'=>'克孜勒苏柯尔克孜'),
					array("id"=>'6531','fid'=>'6500','name'=>'喀什地区'),
					array("id"=>'6532','fid'=>'6500','name'=>'和田地区'),
					array("id"=>'6540','fid'=>'6500','name'=>'伊犁哈萨克'),
					array("id"=>'6542','fid'=>'6500','name'=>'塔城地区'),
					array("id"=>'6543','fid'=>'6500','name'=>'阿勒泰地区'),
				
					array("id"=>'7100','fid'=>'1099','name'=>'台湾省'),
					array("id"=>'7101','fid'=>'7100','name'=>'台湾'),
				
					array("id"=>'8100','fid'=>'1099','name'=>'香港'),
					array("id"=>'8101','fid'=>'8100','name'=>'香港'),
				
					array("id"=>'8200','fid'=>'1099','name'=>'澳门'),
					array("id"=>'8201','fid'=>'8200','name'=>'澳门'),
	);
}