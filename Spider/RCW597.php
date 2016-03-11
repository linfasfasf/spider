<?php
/**
* 
*/
set_time_limit(0);
class RCW597 extends Baseclass
{
    protected $config = array(
            'hddKeytype'=>0,
            'hddPageSize'=>20,
            'hddIsList'=>1,
            'hddGroupJson'=>'all::1W+;;',
            'hddpostvar'=>'bf0539a8978b2fcaabe3a77beaf3f33e',
            'hddIsfirstPost'=>1,
            'act'=>'search',
            'qw[]'=>1,
            'qw[]'=>2,
            'radSex'=>0,
            'radMarriage'=>0,
            );
    protected $config_login = array(
            'txtUsername'=>'test123456',
            'txtPassword'=>'test1234567',
            'txtUserType'=>2,//1-个人 2-企业
            'txtAppType'=>1,
            'act'=>'login'
    );

    public function run($time_lit = null)
    {
        $first_time = time();
        $i= 0;
        //登录
        $login_url = "http://xm.597.com/api/web/company.api";
        $this->curl($login_url, $this->config_login);

        while((time()-$first_time) <= 600) {
            $i++;
            if($i > 2){
              $i = 0;
                exit;
            }
            $url = "http://xm.597.com/company/resume/search.html?hddbuildseeker=&hddqueryString=&hddKeytype=0&hddPageSize=20&hddIsList=1&hddGroupJson=all%3A%3A1W%2B%3B%3B&hddpostvar=bf0539a8978b2fcaabe3a77beaf3f33e&hddIsfirstPost=1&act=search&qw%5B%5D=1&qw%5B%5D=2&txtKeyword=&expArea=&currArea=&calling=&nativeArea=&radSex=0&ddlMinWrokYear=&ddlMaxWrokYear=&txtAgeLower=&txtAgeUpper=&radMarriage=0&txtMinStature=&txtMaxStature=&page=".$i;
            $this->companySearch($url);
        }
    }

    //查询简历页面--获取每个简历的url
    public function companySearch($url)
    {
        $result = $this->curl($url);
        $result = preg_replace("'<script(.*?)</script>'is","",$result);//去除js文件
        preg_match_all('#<span class=\"name\">[\S\s]*?</span>#',$result,$match);//匹配span
        //取出每个span中的URL
        $i = 0;
        foreach($match[0] as $value){
            $i++;
            preg_match_all('#\<a\shref\=\"([^\"]+)#',$value,$href_info);
            foreach($href_info[1] as  $val){
                $url = "http://xm.597.com".$val;
                $html = $this->curl($url);
                $this->resultContent($html);
            }
        }
    }

    public function resultContent($html)
    {
        //匹配规则
        $cssMatch = '#<link .*#';
        preg_match_all($cssMatch,$html,$cssResult);//通过正则匹配，取出所需要的数据。
        $css = implode("\n\r",$cssResult[0]);

        $match = '#<div class="resume-left"[\S\s]*?>([\S\s]*?)实践经验[\S\s]*<!--/-->#';
        preg_match($match,$html,$result);//通过正则匹配，取出所需要的数据。
        $match_replace = "#<div id=\"linkwayContainer\">[\S\s]*?</div>#";
        $result = preg_replace($match_replace,'',$result);
        echo $css."\n\r".$result[0];
        $this->getUserInfo($css,$result[0]);
    }

    /**
     * 正则配置基本信息，插入到数据库
     * 由于户籍前 正则取的数据都为数组，所以都取下标为1的 例如$cid[1]
     * @param $result TML页面
     */
    public function getUserInfo($css,$result){
        preg_match('#简历编号：(.*?)<#', $result, $cid);//简历编号
        preg_match('#class="n">(.*?) <#', $result, $name);//姓名
        preg_match('#<span id="spnBasicSex" v=""><i class="n"></i>(.*?)</span>#', $result, $sex);//性别
        preg_match('#class="y"></i>(.*?)<#', $result, $age);//年龄
        preg_match('#class="x"></i>(.*?)<#', $result, $education);//学历
        preg_match('#class="j"></i>(.*?)<#', $result, $experience);//工作经验
        preg_match('#<p class="inf2">([\S\s].*?)</p>#', $this->trimall($result), $info);//户籍
        preg_match_all('#<span>(.*?)</span>#', $info[1], $info1);
        $area = str_replace('户籍：','',$info1[1][1]);
        $area = explode(',',$area);
        $province = $this->getAreaCode($area[0]);
        $city = $this->getAreaCode($area[1]);
        if($sex[1]=='男'){
            $sex = 1;
        }else{
            $sex = 0;
        }
        $age = str_replace('岁','',$age[1]);
        $insertArr = array(
            'cid'=>$cid[1],
            'content'=>$css.$result,
            'sex'=>$sex,
            'name'=>$name[1],
            'province'=>$province,
            'city'=>$city,
            'age'=>$age,
            'education'=>$education[1],
            'experience'=>$experience[1]
        );

       $this->insetIntoDatabase($insertArr);
    }

    public function insetIntoDatabase($insertArr){
        //file_put_contents('C:/Users/Administrator/Desktop/log.html',var_export($insertArr, true),FILE_APPEND);
        //判断是否已经存在此简历编号,不插入重复的数据
        $mysqli = $this->sqliConnect();
        $sql = 'select cid from cre_user_content where cid ="'.$insertArr['cid'].'"';
        $result=$mysqli->query($sql)->fetch_assoc();
        if (true){
            $uid = $this->getUniqueUid();//获取随机数
            $insertUser="insert into cre_user (uid,user_name,password) values ({$uid},'597RCW','597RCW')";
            $mysqli->query($insertUser);
            if($mysqli->affected_rows < 1) {
                $this->saveLog($mysqli->error, 'RCW597', $uid);
            }

            $content = mysqli_escape_string($mysqli,$insertArr['content']);
            $insertContent="insert into cre_user_content (uid,flag,content,cid) values ({$uid},'597RCW','.$content.','".$insertArr['cid']."')";
            $mysqli->query($insertContent);
            if($mysqli->affected_rows < 1) {
                $this->saveLog($mysqli->error, 'RCW597', $uid);
            }

            $insertInfo="insert into cre_user_info (uid,sex,name,province,city,education,experience,age) values ({$uid},'{$insertArr['sex']}','{$insertArr['name']}'
                     ,'{$insertArr['province']}','{$insertArr['city']}','{$insertArr['education']}','{$insertArr['experience']}','{$insertArr['age']}')";
            $mysqli->query($insertInfo);
            if($mysqli->affected_rows < 1) {
                $this->saveLog($mysqli->error, 'RCW597', $uid);
            }
            // die();
        }
    }

    //删除空格和回车
    public function trimall($str){
        $qian=array("　","\t","\n","\r");
        return str_replace($qian, '', $str);
    }
}