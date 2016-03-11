<?php

$run_per_time = 30;
define('ROOT', dirname(__FILE__));
$config = ROOT.'/config.php';
$base_path = ROOT.'/Baseclass/';
$spider_path = ROOT.'/spider';

autoRequire($base_path);
autoRequire($spider_path);
$config = require_once($config);

$args = getopt('b:h::aa');//从cmd获取输入参数，
if (isset($args['h'])&&!$args['h']) {
	echo "set which web spider to run ;
	e.x:
	-b RCW597 => RUN THE SPIDER OF 597RCW";
}

if (!empty($args['b'])) {
	if(array_key_exists($args['b'], $config)){
		$spider_info = $config[$args['b']];
		run($spider_info);

	}else{
		exit('the spider do not exist !');
	}	
}else{
	while (true) {
		foreach ($config as $spider => $spider_info) {
			run($spider_info, $run_per_time);
		}
	
	}
	
}

function run($spider_info, $time_lit=false){
	if (class_exists($spider_info['class'])) {
			// $class_obj=  = new $spider_info['class']();
			if (method_exists( $class_obj= new $spider_info['class'](), $spider_info['run'])) {
				// $class_obj = new $spider_info['class']();
				// $class_obj->$spider_info['run']();
				call_user_func(array($class_obj, $spider_info['run']), $time_lit);//执行该入口方法
			}else{
				exit('the run method do not exist');
			}
		}else{
			exit('the spider class do not exist');
		}
}

function autoRequire($path){
	$path = rtrim($path, "/");
	if ($file_handle= opendir($path)) {
		while (false !== ($file = readdir($file_handle))) {
			if ($file != "." && $file != "..") {
				require_once($path.'/'.$file);
			}
			
		}
	}
}
