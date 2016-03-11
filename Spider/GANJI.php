<?php
class GANJI extends Baseclass{

	protected $cookie = 'citydomain=bj; ganji_uuid=9080891786496972817428; GANJISESSID=7faaefb3580c2b8fa5689e42d8db1cae; ganji_xuuid=4c902a72-c613-4a8b-9eb2-fcc5a4c01d11.1457405491837; sscode=e%2FL6oxG6hR%2Fn%2FSmhe%2F2PJ9ak; GanjiUserName=linyaoshan; GanjiUserInfo=%7B%22user_id%22%3A344694516%2C%22email%22%3A%22%22%2C%22username%22%3A%22linyaoshan%22%2C%22user_name%22%3A%22linyaoshan%22%2C%22nickname%22%3A%22%22%7D; bizs=%5B%5D; supercookie=ZmD0Awx0AGR2WTR2LGAvMGxjAGH0MGuuLGN1MwN2AmqvATMzAJL1ATEvMQMyZwt0MGp%3D; nTalk_CACHE_DATA={uid:kf_10111_ISME9754_344694516}; NTKF_T2D_CLIENTID=guest361DA3BA-990A-9435-CC33-5423B161A3D4; lg=1; __utma=32156897.1282038066.1457405488.1457405488.1457405488.1; __utmb=32156897.20.10.1457405488; __utmc=32156897; __utmz=32156897.1457405488.1.1.utmcsr=baidu|utmccn=(organic)|utmcmd=organic; _gl_tracker=%7B%22ca_source%22%3A%22www.baidu.com%22%2C%22ca_name%22%3A%22-%22%2C%22ca_kw%22%3A%22-%22%2C%22ca_id%22%3A%22-%22%2C%22ca_s%22%3A%22seo_baidu%22%2C%22ca_n%22%3A%22-%22%2C%22ca_i%22%3A%22-%22%2C%22sid%22%3A39088227922%7D';

	public function run($time_lit){
		if (empty($time_lit)) {
			$time_lit = 600;
		}
		$url = 'http://hrvip.ganji.com/resume_library/search_resume/?category=-1&major=&tag=&city_id=76&district_id=-1&street_id=-1&sex=-1&degree=-1&date=-1&age=-1&age_start=&age_end=&period=-1&price=5&parttime_price=5&key=&related=1';	
		$this->getSearchInfo($url);
	}

	public function getSearchInfo($url){
		

		$referer = 'http://hrvip.ganji.com/resume_library/search_resume/?category=-1&major=&tag=&city_id=76&district_id=-1&street_id=-1&sex=-1&degree=-1&date=-1&age=-1&age_start=&age_end=&period=-1&price=5&parttime_price=5&key=&related=1';
		$result = $this->curl($url, '', $this->cookie);
		preg_match_all('/xm.gan.*\.htm/', $result, $getperson);
		var_dump($getperson);
		foreach ($getperson[0] as $url) {
			var_dump($url);
			$this->getPersonInfo($url);
		}
		
	}


	public function getPersonInfo($url){
		$res = $this->curl($url, $this->cookie);
		preg_match('/offer_name.*/', $res, $name);
		$name = substr($name[0], 11, -7);
		
		preg_match('/offer_age.*岁/', $res, $age);
		$age  = substr($age[0], 20);

		preg_match('/最高学历.*\/s/', $res, $education);
		$education = substr($education[0], 19, -3);
		preg_match('/年限.*\/s/', $res, $experience);
		$experience = substr($experience[0], 12, -3).'工作经验';
		mb_convert_encoding($experience, 'GBK', 'UTF8');

		preg_match('/<div class="mt-10 bor_offer".*[\S\s]*<\/div>\s<!--wrapper e-->/', $res, $result);
		$result = substr($result[0], 0, -20);
		var_dump($result);
		var_dump($experience);
		$this->saveLog($experience);

		
		file_put_contents('C:/Users/Administrator/Desktop/result.html', $result);
		die();
	}

}