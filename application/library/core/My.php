<?php
/**
 * Created by PhpStorm.
 * User: zhanghaisheng
 * Date: 2016/3/18
 * Time: 16:09
 */

class MyController extends BasicController {
    /*protected  function init(){
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
    }*/
    protected $Zuopin_;
    protected $Redis_;
    protected  function init(){
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        $this->Zuopin_= $this->load('Zuopin');
        $this->Redis_= new Redis;
        $this->Redis_->connect('127.0.0.1',6379);

    }

    protected function zanlist5($zuopinid){
        $Zan_=$this->load('zan');
        //$this->load->model('zan_model');
        $zanlist5=$Zan_->zanlist5($zuopinid);
        if (empty($zanlist5)) {
            return $zanlist5;
        }
        $new=array();
        foreach ($zanlist5 as $key => $z) {
            $t=array();
            $t['userid']=$z['userid'];
            if (empty($z['touxiang'])) {
                $t['touxiang']='touxiang.png';
            }else{
                $t['touxiang']=$z['touxiang'];
            }
            $new[]=$t;
        }
        return $new;
    }



    public function zanlist($zuopinid,$page,$myid){
        //1
        /*$zuopinid=$this->input->post('zuopinid');
        $page=$this->input->post('page');
        $myid=$this->input->post('myid');*/
        //2
        if (empty($zuopinid)) {
            $b['code']=1;
            $b['msg']='没有获得有效参数';
            $b['users']=array();
            print_r(json_encode($b));
            return;
        }
        //3
        $page=(int)$page;
        $myid=(int)$myid;
        if (empty($myid)) {
            $myid = 0;
        }
        //4
        $Zan_=$this->load('zan');
        $zanlist=$Zan_->zanlist($zuopinid,$page);
        if (empty($zanlist)) {
            $new=$this->zhengli_users($myid,$zanlist);
            $b['code']=3;
            $b['msg']='没有更多user了';
            $b['users']=$zanlist;
            print_r(json_encode($b));
            return;
        }

        $new=$this->zhengli_users($myid,$zanlist);
        $b['code']=0;
        $b['msg']='成功返回';
        $b['users']=$new;
        print_r(json_encode($b));


    }

    protected function get_jubao_times($zuopinid){
        $times=$this->Zuopin_->get_jubao_times($zuopinid);
        return $times;
    }
    protected function add_jubao_times($zuopinid,$times){
        return $this->Zuopin_->add_jubao_times($zuopinid,$times);
    }
    protected function add_jubao_id($myid,$zuopinid){
        $Jubao_=$this->load('jubao');
        return $Jubao_->add_jubao_id($myid,$zuopinid);
    }



    //一个过滤，是否是admin，来增加不同的次数
    protected function is_admin_id($myid){
        if ($myid==3) {
            return true;
        }
        if ($myid==4) {
            return true;
        }
        if ($myid==10) {
            return true;
        }
        if ($myid==47752) {
            return true;
        }
        return false;
    }

    public function text_filter($text,$zpid){
        if (strstr($text,'红包')) {
            $this->add_huati_filter(44,$zpid);
            return;
        }
        if (strstr($text,'钱')) {
            $this->add_huati_filter(44,$zpid);
            return;
        }
        if (strstr($text,'分')) {
            $this->add_huati_filter(44,$zpid);
            return;
        }

        if (strstr($text,'群')) {
            $this->add_huati_filter(42,$zpid);
            return;
        }
        if (strstr($text,'炮')) {
            $this->add_huati_filter(42,$zpid);
            return;
        }
        if (strstr($text,'楼')) {
            $this->add_huati_filter(42,$zpid);
            return;
        }
        if (strstr($text,'管理')) {
            $this->add_huati_filter(42,$zpid);
            return;
        }

        if (strstr($text,'爷')) {
            $this->add_huati_filter(51,$zpid);
            return;
        }
        if (strstr($text,'爸')) {
            $this->add_huati_filter(51,$zpid);
            return;
        }
        if (strstr($text,'儿')) {
            $this->add_huati_filter(51,$zpid);
            return;
        }
        if (strstr($text,'孙')) {
            $this->add_huati_filter(51,$zpid);
            return;
        }
        if (strstr($text,'装')) {
            $this->add_huati_filter(51,$zpid);
            return;
        }
        if (strstr($text,'逼')) {
            $this->add_huati_filter(51,$zpid);
            return;
        }


    }

    public function add_zoupin($url,$userid,$sourceid,$tagid){
       // $this->load->model('zuopin_model');
        $insetid=$this->Zuopin_->add($url,$userid,$sourceid,$tagid);
        return $insetid;
    }

    public function add_lingjian($url,$zuopinid){
        $Lingjian_= $this->load('lingjian');
        //$this->load->model('lingjian_model');
        $insetid=$Lingjian_->add($url,$zuopinid);
        return $insetid;
    }

    //更新话题的状态，是否有作品 1为有作品
    protected function up_tagid_statue($tagid){
        $Huati_=$this->load('huati');

        $Huati_->up_tagid_statue($tagid);
    }

    //增加某个人user的热门作品排序
    protected function remen_redis_add_num($zuopinid,$num){
        if ($num == 0) {
            $zp=$this->get_zuopin_by_zpid($zuopinid);
            if (!empty($zp['userid'])) {
                $redis = new Redis();
                $redis->connect('127.0.0.1',6379);
                $redis->zIncrBy('userszp_remen::'.$zp['userid'],$num,$zuopinid);
            }
            return;
        }
        $zp=$this->get_zuopin_by_zpid($zuopinid);
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        //增加进日榜
        $redis->zIncrBy('remenzps::'.date('Ymd',$zp['zp_creat_time']),$num,$zuopinid);
        //增加进作品热门总榜
        $redis->zIncrBy('remen_zps',$num,$zuopinid);
        //增加进某个userid的作品热门榜
        if (!empty($zp['userid'])) {
            $redis->zIncrBy('userszp_remen::'.$zp['userid'],$num,$zuopinid);
        }

    }

    protected function guanxi_add($guanxi,$zuopinid){
        $Guanxi_=$this->load('guanxi');
        return $Guanxi_->guanxi_add($guanxi,$zuopinid);
    }

    protected function add_user_zpid_redis($zpid,$userid){
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $redis->hSet('zpid_userid',$zpid,$userid);

    }

    public function add_huati_filter($htid,$zpid){
        //$htid=58;
        //$htid=48;
        //写进tag

        $this->up_tagid_statue($htid);

        //tag_label_zp相关
        $this->tag_label_zp($htid,$zpid);
        //1初始化redis
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $zan_times= $redis->zScore('zan_times',$zpid);
        $edit_times= $redis->zScore('edit_times',$zpid);
        $fenxiang_times= $redis->zScore('fenxiang_times',$zpid);
        $r=$zan_times*5+$edit_times*5+$fenxiang_times*2;
        $redis->zIncrBy('huatizps::'.$htid,$r,$zpid);

        //$this->yibu('huati_tuisong',4,$zpid);
    }

    public function yibu($type,$userid,$zuopinid){
	  $url = '222.73.30.207';
        $fp = fsockopen($url, 80, $errno, $errstr, 30);
        if (!$fp) {
            echo "$errstr ($errno)<br />\n";
        } else {
            $out = "GET /api/$type/$userid/$zuopinid HTTP/1.1\r\n";
            $out .="Host: ".$url."\r\n";
            $out .="Connection: Close\r\n\r\n";
            //print_r($out);
		//error_log($out,3,'/usr/local/nginx/html/jpush/jp.log');
            fwrite($fp, $out);

            /*忽略执行结果
            while (!feof($fp)) {
                echo fgets($fp, 128);
            }*/
            usleep(20000);
            fclose($fp);
            // print_r($out);
        }
    }

    public function add_guanzhu_admin($user_id){
        $Guanzhu_=$this->load('guanzhu');
        //$this->load->model('zan_model');
        return $Guanzhu_->add_guanzhu_admin($user_id);
    }

    protected function get_huatis_zuire_page($page,$myid){
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $min=$page*10;
        $max=($page+1)*10-1;
        $htids=$redis->zRevRange('huatiread',$min,$max);
        if (empty($htids)) {
            $b=array();
            $b['code']=3;
            $b['msg']='没有更多画题';
            $b['huati']=$htids;
            print_r(json_encode($b));
            return;
        }
        $new=array();

        $Huati_=$this->load('huati');

        foreach ($htids as $key => $htid) {

            $huati=$Huati_->get_huati_byid($htid);
            $ht=$this->zhengli_ht($huati,$myid);
            $new[]=$ht;
        }
        $b=array();
        $b['code']=0;
        $b['themes']=array('name'=>'最热','type'=>1);
        $b['msg']='成功返回';
        $b['huati']=$new;
        print_r(json_encode($b));

    }

    //增加用户
    public function user_add($phone,$type,$deviceToken){
        $User_=$this->load('users');
       // $this->load->model('user_model');
        $userid=$User_->user_add($phone,$type,$deviceToken);
        return $userid;
    }


    protected function is_qq($openid){
        $Cq_=$this->load('connect_qq');
        return $Cq_->is_qq($openid);
    }
    protected function is_weibo($openid){
        $Wb_=$this->load('connect_wb');
        return $Wb_->is_weibo($openid);
    }
    protected function is_weixin($openid){
        $Wb_=$this->load('connect_wx');
        return $Wb_->is_weixin($openid);
        /*$this->load->model('user_model');
        return $this->user_model->is_weixin($openid);*/
    }



    protected function zhuce_wx($userid,$openid){
        $connect_wx_=$this->load('connect_wx');
        return $connect_wx_->zhuce_wx($userid,$openid);

    }
    protected function zhuce_wb($userid,$openid){
        $connect_wx_=$this->load('connect_wb');
        return $connect_wx_->zhuce_wb($userid,$openid);

    }
    protected function zhuce_qq($userid,$openid){
        $connect_wx_=$this->load('connect_qq');
        return $connect_wx_->zhuce_qq($userid,$openid);

    }
    protected function is_phone($phone){
        $Users_=$this->load('users');
        return $Users_->is_phone($phone);
        /*$this->load->model('user_model');
        return $this->user_model->is_phone($phone);*/
    }

    //记录启动登录的时间
    protected function add_denglu_time($userid){
        $Users_=$this->load('users');
        $Users_->add_denglu_time($userid);

        //写进session
       /* $this->load->library('session');
        $this->session->set_userdata('uid', $userid);*/
    }

    protected function up_deviceToken($userid,$deviceToken){
        $Users_=$this->load('users');
        $Users_->up_deviceToken($userid,$deviceToken);
    }

    protected function phone_denglu($phone){
        $Users_=$this->load('users');
        return  $Users_->phone_denglu($phone);
    }

    //增加新的用户，返回userid
    protected function zhuce_add_user($name,$touxiang,$type,$gender,$deviceToken){
        $Users_=$this->load('users');

        return $Users_->zhuce_add_user($name,$touxiang,$type,$gender,$deviceToken);


    }

    protected function get_curl_touxiang($url){
        // create curl resource
        $ch = curl_init();
        // set url
        curl_setopt($ch, CURLOPT_URL, $url);
        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // $output contains the output string
        $output = curl_exec($ch);
        // close curl resource to free up system resources
        curl_close($ch);
        return $output;
    }

    public function user_edit($userid,$nickname,$xingbie,$jianjie){
        $Users_=$this->load('users');

        return $Users_->user_edit($userid,$nickname,$xingbie,$jianjie);
    }
    //修改用户密码
    public function user_pswd($userid,$pswd){
        $Users_=$this->load('users');
        $sta=$Users_->user_pswd($userid,$pswd);
        return $sta;
    }

    public function user_sign($userid,$jianjie){
        $Users_=$this->load('users');
        return $Users_->user_sign($userid,$jianjie);
    }

    //增加头像到数据库
    protected function edit_touxiang($userid,$name){
        $Users_=$this->load('users');
        $sta=$Users_->edit_touxiang($userid,$name);
        return $sta;
    }
    //查询是否赞
    public function is_guanzhu($myid,$userid){
        $Guanzhu_=$this->load('guanzhu');

        return $Guanzhu_->is_guanzhu($myid,$userid);
    }

    public function dian_guanzhu($myid,$userid){
        $Guanzhu_=$this->load('guanzhu');
        return $Guanzhu_->dian_guanzhu($myid,$userid);
    }

    public function delete_guanzhu($guanzhuid){
        $Guanzhu_=$this->load('guanzhu');

        return $Guanzhu_->delete_guanzhu($guanzhuid);
    }

    //关注列表
    public function guanzhu_list($myid,$userid,$page){
        $Guanzhu_=$this->load('guanzhu');
        $users=$Guanzhu_->guanzhu_list($userid,$page);
        if (empty($users)) {
            //没有关注的人，返回什么？
            return $users;
        }
        $new=array();
        if($myid == $userid){
            $new=$this->zhengli_users_myguanzhu($myid,$users);
        }else{
            $new=$this->zhengli_users($myid,$users);
        }

        return $new;
    }

    //增加某个作品的评论
    public function comment_add($comment_text,$zuopinid,$userid){
        //0增加进次数缓存
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $redis->zIncrBy('comment_times', 1, $zuopinid);

        //1增加进数据库

        $C_=$this->load('comment');
        return $C_->comment_add($comment_text,$zuopinid,$userid);
    }

    //粉丝列表
    public function fensi_list($myid,$userid,$page){
        $Guanzhu_=$this->load('guanzhu');
        $users=$Guanzhu_->fensi_list($userid,$page);
        if (empty($users)) {
            //没有关注的人，返回什么？
            return $users;
        }
        $new=$this->zhengli_users($myid,$users);
        return $new;
    }

    public function zhengli_users($myid,$users){
        $new=array();
        foreach ($users as $key => $user) {
            $tmp=array();
            if(empty($user['user_id'])){
                continue;
            }
            $tmp['uid']=$user['user_id'];
            $tmp['nickname']=$user['user_name'];
            if (empty($user['user_name'])) {
                $n=(int)$user['user_id']+rand(10000,20000);
                $tmp['name']='P表情'.$n;
            }else{
                $tmp['name']=$user['user_name'];
            }
            if (empty($user['user_touxiang'])) {
                $tmp['touxiang']='touxiang.png';
            }else{
                $tmp['touxiang']=$user['user_touxiang'];
            }
            $tmp['email']=$user['user_email'];
            if (empty($user['user_phone'])) {
                $tmp['phone']='';
            }else{
                $tmp['phone']=$user['user_phone'];
            }
            if (empty($user['connect_type'])) {
                $tmp['connect_type']='';
            }else{
                $tmp['connect_type']=(int)$user['connect_type'];
            }
            $tmp['birthday']=(int)$user['user_shengri'];
            $tmp['sign']=$user['user_jieshao'];
            $tmp['gender']=(int)$user['user_xingbie'];
            if (empty($myid)) {
                $tmp['attended']=false;
            }else{
                $tmp['attended']=$this->is_i_guanzhu($myid,$user['user_id']);
            }


            $new[]=$tmp;
        }
        return $new;
    }


    public function zhengli_users_myguanzhu($myid,$users){
        $new=array();
        foreach ($users as $key => $user) {
            $tmp=array();
            if(empty($user['user_id'])){
                continue;
            }
            $tmp['uid']=$user['user_id'];
            $tmp['nickname']=$user['user_name'];
            if (empty($user['user_name'])) {
                $n=(int)$user['user_id']+rand(10000,20000);
                $tmp['name']='P表情'.$n;
            }else{
                $tmp['name']=$user['user_name'];
            }
            if (empty($user['user_touxiang'])) {
                $tmp['touxiang']='touxiang.png';
            }else{
                $tmp['touxiang']=$user['user_touxiang'];
            }
            $tmp['email']=$user['user_email'];
            if (empty($user['user_phone'])) {
                $tmp['phone']='';
            }else{
                $tmp['phone']=$user['user_phone'];
            }
            if (empty($user['connect_type'])) {
                $tmp['connect_type']='';
            }else{
                $tmp['connect_type']=(int)$user['connect_type'];
            }
            $tmp['birthday']=(int)$user['user_shengri'];
            $tmp['sign']=$user['user_jieshao'];
            $tmp['gender']=(int)$user['user_xingbie'];
            if (empty($myid)) {
                $tmp['attended']=false;
            }else{
                $tmp['attended']=$this->is_i_guanzhu($myid,$user['user_id']);
                if(!$tmp['attended']){
                    $redis = new Redis();
                    $redis->connect('127.0.0.1',6379);
                    $redis->zIncrBy('userdetail::'.$myid, 1,$user['user_id']);
                }
                $tmp['attended']=true;
            }


            $new[]=$tmp;
        }
        return $new;
    }

    //通过userid取得user
    public function user_getbyid($userid){
        $Users_=$this->load('users');
        $userinfo=$Users_->user_getbyid($userid);
        return $this->zhengli_userinfo($userinfo);
    }

    protected function get_score_by_key_member($key,$member){
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $score=$redis->zScore($key,$member);
        return $score;
    }

    protected function get_zps4week($userid){

        $zps=$this->Zuopin_->get_zuopin_by_userid_week($userid);

        if (empty($zps)) {
            return array();
        }
        $new=array();
        foreach ($zps as $key => $zuopin) {
            $zuopin=$this->Zuopin_->geturl($zuopin['zuopin_id']);
            $tmp=array();
            $tmp['zuopin_id'] = $zuopin['zuopin_id'];
            $tmp['zuopin_url'] = $zuopin['zuopin_url'];
            $tmp['liked_times'] = $this->liked_times($zuopin['zuopin_id']);
            $tmp['fenxiang_times'] = $this->get_fenxiang_times($zuopin['zuopin_id']);
            $tmp['edit_times'] = $this->get_edit_times($zuopin['zuopin_id']);
            $tmp['remen_times'] = $tmp['liked_times']*5 + $tmp['edit_times']*5+$tmp['fenxiang_times']*2;

            $new[]=$tmp;
            $remen_times[] = $tmp['remen_times'];
            $t[] = $tmp['zuopin_id'];
        }
        //返回一个做的热门排序
        array_multisort($remen_times, SORT_DESC, $t, SORT_DESC, $new);
        //取4个作品
        $zuopins=array();
        foreach ($new as $key => $zp) {
            if (intval($key) <= 3) {
                $zuopins[]=$zp;
            }

        }


        return $zuopins;

    }



    protected function get_zps4($userid){
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $zpids4=$redis->zRevRange('userszp_remen::'.$userid, 0,4);
        $new=array();
        foreach ($zpids4 as $key => $zpid) {
            $zuopin=$this->Zuopin_->geturl($zpid);
            if (empty($zuopin)) {
                continue;
            }
            $tmp=array();
            $tmp['zuopin_id'] = $zpid;
            $tmp['zuopin_url'] = $zuopin['zuopin_url'];

            $new[]=$tmp;

        }
        return $new;
    }



    //通过userid取得user
    public function usergetbyid($userid){
        $Users_=$this->load('users');
        $userinfo=$Users_->usergetbyid($userid);
        return $userinfo;
    }
    public function zhengli_userinfo($userinfo){
        if (empty($userinfo)) {
            return array();
        }
        $tmp=array();
        $tmp['uid']=$userinfo['user_id'];
        $tmp['nickname']=$userinfo['user_name'];
        if (empty($userinfo['user_name'])) {
            $n=(int)$userinfo['user_id']+rand(10000,20000);
            $tmp['name']='P表情'.$n;
        }else{
            $tmp['name']=$userinfo['user_name'];
        }
        if (empty($userinfo['user_touxiang'])) {
            $tmp['touxiang']='touxiang.png';
        }else{
            $tmp['touxiang']=$userinfo['user_touxiang'];
        }
        $tmp['email']=$userinfo['user_email'];
        if (empty($userinfo['user_phone'])) {
            $tmp['phone']='';
        }else{
            $tmp['phone']=$userinfo['user_phone'];
        }
        if (empty($userinfo['connect_type'])) {
            $tmp['connect_type']='';
        }else{
            $tmp['connect_type']=(int)$userinfo['connect_type'];
        }
        $tmp['birthday']=(int)$userinfo['user_shengri'];
        $tmp['sign']=$userinfo['user_jieshao'];
        $tmp['gender']=(int)$userinfo['user_xingbie'];
        $tmp['shebei_id']=$userinfo['shebei_id'];
        $tmp['deviceToken']=$userinfo['deviceToken'];
	if (empty($userinfo['platform'])) {
            $tmp['platform']='';
        }else{
            $tmp['platform']=(int)$userinfo['platform'];
        }
        return $tmp;

    }

    //我是否关注
    public function is_i_guanzhu($myid,$userid){
        if (empty($myid)) {
            return false;
        }
        if (empty($userid)) {
            return false;
        }

        if($myid == $userid){
            return false;
        }

        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $score=$redis->zScore('userdetail::'.$myid,$userid);
        if ($score) {
            return true;
        }
        return false;
    }

    protected function zhengli_ht($huati,$myid){
        $ht = new huati();
        $ht->id = $huati['htid'];
        $ht->name = $huati['name'];
        $ht->userid = $huati['userid'];
        $ht->attended = false;
        $ht->avatar = $huati['user_touxiang'];
        $ht->nickName = $huati['user_name'];
        $ht->attendedTheme=false;
        if (empty($huati['creattime'])) {
            $ht->creattime = 0;
        }else{
            $ht->creattime = intval($huati['creattime']);
        }
        $ht->zuopinCount = $this->zuopinCount_huati($huati['htid']);
        $zp4s=$this->get_huati_4zps($huati['htid']);

        $ht->zuopins = $this->zhengli_ht_zps($zp4s);

        $ht->readCount = $this->readCount_huati($huati['htid']);
        $ht->likedCount = $this->get_huati_zp_liketime($huati['htid']);
        $ht->editCount = $this->get_huati_zp_editCount($huati['htid']);
        $ht->shareCount = $this->get_huati_zp_shareCount($huati['htid']);

        return $ht;
    }

    protected function get_huati_zp_liketime($tagid){
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $score=$redis->zScore('huati_liketimes',$tagid);
        if (empty($score)) {
            $score = 0;
        }
        return $score;
    }
    protected function add_huati_zp_liketime($zuopinid,$num){
        $zp=$this->get_zuopin_by_zpid($zuopinid);
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $redis->zIncrBy('huati_liketimes',$num,$zp['tagid']);
    }
    protected function get_huati_zp_editCount($tagid){
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $score=$redis->zScore('huati_edittimes',$tagid);
        if (empty($score)) {
            $score = 0;
        }
        return $score;
    }
    protected function add_huati_zp_editCount($zuopinid,$num){
        $zp=$this->get_zuopin_by_zpid($zuopinid);
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $redis->zIncrBy('huati_edittimes',$num,$zp['tagid']);
    }
    protected function get_huati_zp_shareCount($tagid){
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $score=$redis->zScore('huati_sharetimes',$tagid);
        if (empty($score)) {
            $score = 0;
        }
        return $score;
    }
    protected function add_huati_zp_shareCount($zuopinid,$num){
        $zp=$this->get_zuopin_by_zpid($zuopinid);
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $redis->zIncrBy('huati_sharetimes',$num,$zp['tagid']);
    }
    protected function readCount_huati($id){
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $score=$redis->zScore('huatiread',$id);
        if (empty($score)) {
            $score = 0;
        }
        return $score;
    }

    protected function zhengli_ht_zps($zps){
        $new=array();
        foreach ($zps as $key => $zp) {
            $tmp=array();
            $tmp['zuopin_url']=$zp['zuopin_url'];
            if (intval($zp['quxiao_remen']) == 2) {
                $tmp['isRecommend']=true;
            }else{
                $tmp['isRecommend']=false;
            }
            $new[]=$tmp;
        }
        return  $new;

    }

    protected function get_huati_4zps($tagid){
        $Htzps=$this->load('htzps');
        return $Htzps->get_huati_4zps($tagid);
    }


    protected function zuopinCount_huati($tagid){
        $Htzps=$this->load('htzps');

        return $Htzps->zuopinCount_huati($tagid);
    }

    protected function tag_label_zp($htid,$zpid){
        $Huati_=$this->load('huati');
        //$this->load->model('tag_model');
        $ht=$Huati_->get_htname_by_htid($htid);

        if (empty($ht['name'])) {
            return;
        }

        //1 增加进话题与作品对应表 htzps 参数 htid(=themeid) zpid
        $Htzps=$this->load('htzps');
        $Htzps->add_htzps($htid,$zpid);

        //2 c.redis（Set集合) ht::labels::$htid 某个话题下面的标签列表  增加（themei对应的name）为标签
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $redis->sAdd('ht::labels::'.$htid , $ht['name']);

        //3redis（Set集合) label_zpids::$ht['name'] 某个标签下的zpid列表  以themeid对应的那么为标签，增加对应的zpid
        $redis->sAdd('label_zpids::'.$ht['name'] , $zpid);
        //4redis（Set集合) zpid::labels::$zpid 某个zpid对应的label列表   增加对应zpid下对应的标签(=themei对应的name)
        $redis->sAdd('zpid::labels::'.$zpid , $ht['name']);
    }

    //取得作品被喜欢的次数
    public function liked_times($zpid){
        /*	$this->load->model('zan_model');
            $liked_times=$this->zan_model->liked_times($zpid);*/
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $liked_times=$redis->zScore('zan_times',$zpid);
        //error_log(sprintf("[%s] %s\n", date('Y-m-d H:i:s'), print_r($zpid, true)), 3, '/tmp/debug1.log');
        if (empty($liked_times)) {
            $liked_times=0;
        }
        if ($liked_times < 0) {
            $liked_times=0;
            $redis->zAdd('zan_times',0,$zpid);
        }
        return $liked_times;
    }


    //整理数据
    protected function zhengli_notify($notify){
        $new=array();
        foreach ($notify as $key => $note) {
            $tmp=array();
            $tmp['noticeid']= $note['noticeid'];
            $userinfo=$this->user_getbyid($note['userid']);
            $zp=$this->get_zuopin_by_zpid($note['zpid']);
            $tmp['userid']= $note['userid'];
            $tmp['touxiang']= $userinfo['touxiang'];
            $tmp['nickname']= $userinfo['nickname'];
            //$tmp['myid']= $note['myid'];
            $tmp['myid']= $zp['userid'];
            $tmp['zpid']= $note['zpid'];
            $tmp['zpurl']= $zp['zuopin_url'];
            $tmp['zp_creat_time']= (int)$zp['zp_creat_time'];
            if (empty($note['message'])) {
                $tmp['message']="";
            }else{
                $tmp['message']= $note['message'];
            }

            $tmp['action']= intval($note['action']);
            $tmp['actiontime']=intval($note['actiontime']) ;
            //$tmp['type']=intval($note['type']);
            $tmp['creat_time']= $note['creat_time'];
            $new[]=$tmp;
        }
        return $new;
    }
    //得到某个人的通知
    protected function get_notify_byuserid($userid,$page,$version){
        $N_=$this->load('notify');
        return $N_->get_notify_byuserid($userid,$page,$version);

    }

    protected function get_zhutiku_remen_redis($themeid,$page){
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $json=$redis->get('zhutizps::'.$themeid);
        $zps=json_decode($json,true);
        $new=array();
        $min=$page*21;
        $max=($page+1)*21-1;
        if(empty($zps)){
            return $new;
        }
        foreach ($zps as $key => $zp) {
            if ($key >= $min && $key <= $max ) {
                $new[]=$zp;
            }
        }
        return $new;
    }


    //增加收藏
    protected function add_ku_store_byzpid($zpid,$userid){
        $Store_=$this->load('store');
        $sta=$Store_->add_ku_store_byzpid($zpid,$userid);

        return  $sta;
    }

    protected function cancel_ku_store_byzpid($zpid,$userid){
        $Store_=$this->load('store');
        $sta=$Store_->cancel_ku_store_byzpid($zpid,$userid);

        return  $sta;
    }
    protected function cancel_ku_store_byzpid_new($store_id){
        $Store_=$this->load('store');
        $sta=$Store_->cancel_ku_store_byzpid_new($store_id);

        return  $sta;
    }

    protected function zhengli_zt_zps($zps,$myid){
        $new=array();
        foreach ($zps as $key => $zp) {
            $tmp=array();
            $tmp['photoType'] = 0;
            $tmp['baiduUrl'] = '';
            $tmp['zpid'] = $zp['zpid'];
            $tmp['photoid'] = $zp['zpid'];
            $tmp['photoUrl'] = $zp['zpurl'];
            $tmp['themeid'] = $zp['themeid'];
            $tmp['themeName'] = $zp['zhuti'];
            $tmp['likedCount'] = $this->store_likedCount($zp['zpid']);
            $tmp['commentCount'] = 0;
            $tmp['shareCount'] = $this->ku_shareCount($zp['zpid']);
            $tmp['editCount'] = $this->ku_editCount($zp['zpid']);
            if (empty($myid)) {
                $tmp['am_i_zan']=false;
            }else{
                $tmp['am_i_zan']=$this->is_store($zp['zpid'],$myid);
            }
            $tmp['zpcreattime']=(int)$zp['zp_creat_time'];
            $new[]=$tmp;
        }
        return $new;
    }

    protected function is_store($photoid,$myid){
        $Store_=$this->load('store');
        $sta=$Store_->is_store($photoid,$myid);
        if (empty($sta)) {
            return false;
        }
        return true;
    }



    //整理收藏的作品
    protected function zhengli_store_zps($zps,$myid){
        $new=array();

        $Zhutiku_=$this->load('zhutiku');
        foreach ($zps as $key => $zp) {
            if (empty($zp['baiduUrl'])) {
                $z=$Zhutiku_->get_zp_by_zpid($zp['zpid']);

                $store_zp = new store_zp();
                $store_zp->photoType = 0;
                $store_zp->baiduUrl = '';
                $store_zp->zpid = $z['zpid'];
                $store_zp->photoid = $z['zpid'];
                $store_zp->photoUrl = 'http://pimgs.all-appp.com/'.$z['zpurl'];
                $store_zp->themeid = $z['themeid'];
                $store_zp->themeName = $z['zhuti'];
                $store_zp->likedCount = intval($this->store_likedCount($z['zpid']));
                $store_zp->shareCount = intval($this->ku_shareCount($z['zpid']));
                $store_zp->editCount = intval($this->ku_editCount($z['zpid']));
                if (empty($myid)) {
                    $store_zp->am_i_zan = false;
                }else{
                    $store_zp->am_i_zan = $this->is_store($z['zpid'],$myid);
                }
                $store_zp->zpcreattime = (int)$z['zp_creat_time'];
                $new[]=$store_zp;
            }else{

                $store_zp = new store_zp();
                $store_zp->photoType = 1;
                $store_zp->photoid = $zp['store_id'];
                $store_zp->baiduUrl = $zp['baiduUrl'];
                $store_zp->themeName = $zp['baiduTag'];
                $store_zp->likedCount = intval($this->baidi_store_likedCount($zp['baiduUrl']));
                $store_zp->shareCount = intval($this->get_baidu_shareCount($zp['baiduUrl']));
                $store_zp->editCount = 	intval($this->get_baidu_editCount($zp['baiduUrl']));
                if (empty($myid)) {
                    $store_zp->am_i_zan = false;
                }else{
                    $store_zp->am_i_zan = true;
                }
                $store_zp->zpcreattime = (int)$zp['store_time'];
                $new[]=$store_zp;
            }


        }
        return $new;

    }



    //获取收藏图片的数量
    protected function store_likedCount($zpid){
        $Store_=$this->load('store');
        return $Store_->store_likedCount($zpid);
    }
    //获取百度某个链接的收藏数量
    protected function baidi_store_likedCount($url){
        $Store_=$this->load('store');
        return $Store_->baidi_store_likedCount($url);
    }
    //整理，我有没有收藏百度图
    protected function is_baidu_store($userid,$baiduUrl){
        $Store_=$this->load('store');
        return $Store_->is_baidu_store($userid,$baiduUrl);
    }


    protected function ku_shareCount($zpid){
        $kushare_=$this->load('kushare');
        return $kushare_->ku_shareCount($zpid);
    }
    protected function ku_editCount($zpid){
        $kuedit_=$this->load('kuedit');
        return $kuedit_->ku_editCount($zpid);
    }


    //增加素材库的分享此时
    public function add_ku_shareCount(){
        $zpid=$this->input->post('zpid');
        $userid=$this->input->post('userid');

        //判断是否是百度
        $type=$this->input->post('type');
        $baiduUrl=$this->input->post('baiduUrl');


        //判断是否是 baidu url
        if (intval($type) == 1) {
            if (empty($baiduUrl)) {
                $b=array();
                $b['code']=3;
                $b['msg']='缺少参数baiduUrl';
                print_r(json_encode($b));
                return;
            }
            $this->add_baidu_shareCount($baiduUrl);
            $b=array();
            $b['code']=0;
            $b['msg']='增加成功';
            print_r(json_encode($b));
            return ;

        }

        if (empty($zpid)) {
            $b=array();
            $b['code']=3;
            $b['msg']='缺少参数zpid或userid';
            print_r(json_encode($b));
            return;
        }

        $this->load->model('ku_model');
        $this->ku_model->add_ku_shareCount($zpid,$userid);


        $b=array();
        $b['code']=0;
        $b['msg']='增加成功';
        print_r(json_encode($b));

    }
    //增加百度分享的次数
    protected function add_baidu_shareCount($baiduUrl){
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $redis->zIncrBy('baidu_share',1, $baiduUrl);
    }
    //取得百度分享的次数
    protected function get_baidu_shareCount($baiduUrl){
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        return $redis->zScore('baidu_share', $baiduUrl);

    }


    protected function get_ku_score($themeid,$zpid){
        $zhuti='';
        if ($themeid == 1) {
            $zhuti = 'aodamao';
        }
        if ($themeid == 2) {
            $zhuti = 'jinguanzhang';
        }
        if ($themeid == 3) {
            $zhuti = 'egaotu';
        }
        if ($themeid == 4) {
            $zhuti = 'jianmengdeshouhuiban';
        }
        if ($themeid == 5) {
            $zhuti = 'qipaopao';
        }
        if ($themeid == 6) {
            $zhuti = 'abuwawa';
        }
        if ($themeid == 7) {
            $zhuti = 'bofu';
        }
        if ($themeid == 8) {
            $zhuti = 'baochoumiantanchihan';
        }
        if ($themeid == 9) {
            $zhuti = 'fenhongbaolixiong';
        }
        if ($themeid == 10) {
            $zhuti = 'moshixiaoyaoji';
        }
        if ($themeid == 11) {
            $zhuti = 'nvwunaonao';
        }
        if ($themeid == 12) {
            $zhuti = 'shengdanjietizhi';
        }
        if (empty($zhuti)) {
            $Zhuti_=$this->load('zhuti');
            $zt=$Zhuti_->get_zhuti_by_themeid($themeid);
            $zhuti=$zt['zhuti_name'];
        }


        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $sc=$redis->zScore('ku::'.$zhuti,$zpid);
        return intval($sc);
    }


    protected function set_ztzps_redis_themeid($themeid){
        //1取得所有的zp
        $Zhutiku_=$this->load('zhutiku');

        $zps=$Zhutiku_->get_zt_zps($themeid);
        //print_r($zps);
        $new=array();
        foreach ($zps as $key => $zp) {
            $redu = $this->get_ku_score($themeid,$zp['zpid']);
            $zp['redu']=$redu;
            $new[$zp['t']][]=$zp;
        }
        $new2=array();
        foreach ($new as $key => $zpgroup) {
            $new2[$key]=$this->paixu_redu_zhutizps($zpgroup);
        }
        $new3=array();
        foreach ($new2 as $key => $zps) {
            foreach ($zps as $key => $zp) {
                $new3[]=$zp;
            }
        }
        $zs=json_encode($new3);
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $redis->set('zhutizps::'.$themeid,$zs);
    }


    protected function paixu_redu_zhutizps($zpgroup){

        $redu_times=array();
        $edition=array();
        foreach ($zpgroup as $key => $zp) {
            $redu_times[$key]  =$zp['redu'];
            $edition[$key] = $zp['zpid'];
        }
        // 将数据根据 volume 降序排列，根据 edition 升序排列
        array_multisort($redu_times, SORT_DESC, $edition, SORT_DESC, $zpgroup);
        return $zpgroup;
    }

    //增加百度编辑的次数
    protected function add_baidu_editCount($baiduUrl){
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $redis->zIncrBy('baidu_edit',1, $baiduUrl);
    }
    //取得百度编辑的次数
    protected function get_baidu_editCount($baiduUrl){
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        return $redis->zScore('baidu_edit', $baiduUrl);
    }

    //增加改图的次数
    public function add_ku_editCountAction(){
        $zpid=$this->getPost('zpid');
        $userid=$this->getPost('userid');
        $type=$this->getPost('type');
        $baiduUrl=$this->getPost('baiduUrl');


        //判断是否是 baidu url
        if (intval($type) == 1) {
            if (empty($baiduUrl)) {
                $b=array();
                $b['code']=3;
                $b['msg']='缺少参数baiduUrl';
                print_r(json_encode($b));
                return;
            }
            $this->add_baidu_editCount($baiduUrl);
            $b=array();
            $b['code']=0;
            $b['msg']='增加成功';
            print_r(json_encode($b));
            return ;

        }
        if (empty($zpid) || empty($userid)) {
            $b=array();
            $b['code']=3;
            $b['msg']='缺少参数zpid或userid';
            print_r(json_encode($b));
            return;
        }

        $Kuedit_=$this->load('kuedit');
        $Kuedit_->add_ku_editCount($zpid,$userid);


        $b=array();
        $b['code']=0;
        $b['msg']='增加成功';
        print_r(json_encode($b));
    }

    protected function get_zhuti_ku_editCount($themeid){
        $Kuedit_ = $this->load('kuedit');
        return $Kuedit_->get_zhuti_ku_editCount($themeid);
    }


    protected function zhengli_zt($zhutis){
        $new=array();
        $newthemes=array();
        foreach ($zhutis as $key => $zhuti) {
            $tmp=array();
            $tmp['themeid']= $zhuti['themeid'];
            $tmp['themeName']= $zhuti['themeName'];
            //图片要返回最热的一个，这个需要改
            $tmp['showUrl']=$this->get_zhuti_fengmian($zhuti['themeid'],$zhuti['zhuti_name']);
            $tmp['zuopinCount']= $this->get_zps_number_by_zhuti_id($zhuti['themeid']);
            //下面这几个也要改的
            $tmp['likeCount']= intval($this->get_zhuti_store_likedCount($zhuti['themeid']));
            $tmp['editCount']= intval($this->get_zhuti_ku_editCount($zhuti['themeid']));
            $tmp['shareCount']= intval($this->get_zhuti_ku_shareCount($zhuti['themeid']));
            //次数
            $apple=intval($zhuti['apple']);
            if ( $apple >= 100) {
                $newthemes[$apple] = $tmp;
                continue;
            }

            $num=intval($tmp['likeCount'])*5+intval($tmp['editCount'])*5+intval($tmp['shareCount'])*2;
            $nums[]=$num;
            $ids[]=$zhuti['themeid'];
            $new[]=$tmp;
        }
        array_multisort($nums, SORT_DESC, $ids, SORT_DESC, $new);

        ksort($newthemes);
        foreach ($newthemes as $key => $newtheme) {
            array_unshift($new,$newtheme);
        }
        return $new;
    }

    protected function get_zhuti_ku_shareCount($themeid){
        $Kushare_=$this->load('kushare');
        return $Kushare_->get_zhuti_ku_shareCount($themeid);
    }

    protected function get_zhuti_fengmian($themeid,$zhuti_name){
        if (!empty($zhuti_name)) {
            $redis = new Redis();
            $redis->connect('127.0.0.1',6379);
            $zpid=$redis->zRevRange('ku::'.$zhuti_name,0,0);

            if (empty($zpid[0])) {
                $redis = new Redis();
                $redis->connect('127.0.0.1',6379);
                $zpid=$redis->zRevRange('ku::'.$zhuti_name,1,1);
            }

            $Zhutiku_=$this->load('zhutiku');
            $zpurl=$Zhutiku_->get_ku_zpurl_by_zpid($zpid[0]);
            return $zpurl['zpurl'];

        }

        if ($themeid == 1) {
            $zhuti = 'aodamao';
        }
        if ($themeid == 2) {
            $zhuti = 'jinguanzhang';
        }
        if ($themeid == 3) {
            $zhuti = 'egaotu';
        }
        if ($themeid == 4) {
            $zhuti = 'jianmengdeshouhuiban';
        }
        if ($themeid == 5) {
            $zhuti = 'qipaopao';
        }
        if ($themeid == 6) {
            $zhuti = 'abuwawa';
        }
        if ($themeid == 7) {
            $zhuti = 'bofu';
        }
        if ($themeid == 8) {
            $zhuti = 'baochoumiantanchihan';
        }
        if ($themeid == 9) {
            $zhuti = 'fenhongbaolixiong';
        }
        if ($themeid == 10) {
            $zhuti = 'moshixiaoyaoji';
        }
        if ($themeid == 11) {
            $zhuti = 'nvwunaonao';
        }
        if ($themeid == 12) {
            $zhuti = 'shengdanjietizhi';
        }
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $zpid=$redis->zRevRange('ku::'.$zhuti,0,0);

        if (empty($zpid[0])) {
            $redis = new Redis();
            $redis->connect('127.0.0.1',6379);
            $zpid=$redis->zRevRange('ku::'.$zhuti,1,1);
        }


        $Zhutiku_=$this->load('zhutiku');
        $zpurl=$Zhutiku_->get_ku_zpurl_by_zpid($zpid[0]);
        return $zpurl['zpurl'];

    }

    protected function get_zhuti_store_likedCount($themeid){
        $Store_=$this->load('store');
        return $Store_->get_zhuti_store_likedCount($themeid);

    }

    protected function get_zps_number_by_zhuti_id($ztid){
        $Zhutiku_=$this->load('zhutiku');
        return $Zhutiku_->get_zps_number_by_zhuti_id($ztid);
    }



    //作品评论的次数
    public function comment_times($zpid){
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $comment_times=$redis->zScore('comment_times',$zpid);
        if (empty($comment_times)) {
            $comment_times=0;
        }
        return  $comment_times;
        /*$C_=$this->load('comment');
        return $C_->comment_times($zpid);*/
    }
    //取得作品被喜欢的次数
    public function get_fenxiang_times($zpid){
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $fenxiang_times=$redis->zScore('fenxiang_times',$zpid);
        if (empty($fenxiang_times)) {
            $fenxiang_times=0;
        }
        return  $fenxiang_times;
    }

    protected function get_edit_times($zuopinid){
        /*	$this->load->model('zan_model');
            $edit_times=$this->zan_model->get_edit_times($zuopinid);*/
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $edit_times=$redis->zScore('edit_times',$zuopinid);
        if (empty($edit_times)) {
            $edit_times=0;
        }
        return $edit_times;

    }

    //show 最新的
    public function show_new($page){
        $Zp=$this->load('zuopin');
        //$this->load->model('zuopin_model');
        $zuopins=$Zp->show_new($page);
        return $zuopins;

    }

    protected function remen_zp_by_zpid($zpid){
        //$this->load->model('zuopin_model');
        $zp=$this->Zuopin_->remen_zp_by_zpid($zpid);
        return $zp;
    }

    //show_userid_redis_remen
    protected function show_userid_redis_remen($userid,$page){
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $remen_zps=$redis->zRevRange('userszp_remen::'.$userid, $page*21, ($page+1)*21-1);
        //print_r($remen_zps);exit;
        if (empty($remen_zps) && $page == 0) {
            //要获取个人的所有作品，并且把热度写进 userszp_remen::$userid
            //echo "string";exit;
            $sta=$this->add_userszp_remen_byuserid_2($userid);
            //再取
            if ($sta) {
                $redis = new Redis();
                $redis->connect('127.0.0.1',6379);
                $remen_zps=$redis->zRevRange('userszp_remen::'.$userid, $page*21, ($page+1)*21-1);
            }

        }

        return $remen_zps;
    }

    //取得个人的作品，计算热度，并且把热度写进 userszp_remen::$userid
    protected function add_userszp_remen_byuserid_2($userid){
        $zpids=$this->show_zpids_all_by_userid($userid);
        if (!empty($zpids)) {
            foreach ($zpids as $key => $zp) {
                $liked_times = $this->liked_times($zp['zuopin_id']);
                $fenxiang_times= $this->get_fenxiang_times($zp['zuopin_id']);
                $edit_times = $this->get_edit_times($zp['zuopin_id']);
                $remen_times = $liked_times*5 + $edit_times*5+$fenxiang_times*2;

                $redis = new Redis();
                $redis->connect('127.0.0.1',6379);
                $redis->zAdd('userszp_remen::'.$userid,$remen_times,$zp['zuopin_id']);
            }

        }
        return true;
    }



    //show 摸个用户的作品
    public function show_userid($userid,$myid,$page){

        $zuopins=$this->Zuopin_->show_userid($userid,$myid,$page);
        return $zuopins;
    }
    public function showlike_userid($userid,$page){
       // $this->load->model('zuopin_model');
        $zuopins=$this->Zuopin_->showlike_userid($userid,$page);
        return $zuopins;
    }
    public function showlike_userid_all($userid){
        $Zan_=$this->load('zan');
       // $this->load->model('zuopin_model');
        $zuopins=$Zan_->showlike_userid_all($userid);
        return $zuopins;
    }

    public function showliked_userid_all($userid){
        $zuopins=$this->Zuopin_->showliked_userid_all($userid);
        return $zuopins;
    }

    protected function am_i_attendedTheme($myid,$htid){
        $Guanzhuht_=$this->load('guanzhuht');
        //$this->load->model('tag_model');
        $is=$Guanzhuht_->get_guanzhu_huati($myid,$htid);
        if (empty($is)) {
            return false;
        }
        return true;
    }

    //增加进榜
    protected function add_souci_bang($word){
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        //增加进总榜
        $redis->zIncrBy('souci_bang',1,$word);
        //$redis->zIncrBy('souci_bang',200000,'金馆长gif');
        //增加进每天的热词榜 souday::$Ymd
    }
    //增加进个人的搜索词库
    protected function add_souci_userid($userid,$word){
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        //增加进总榜
        $redis->zAdd('souword::'.$userid,time(),$word);
        //增加进每天的热词榜 souday::$Ymd
        //souci_bang
        //$redis->zAdd('souci_bang',1,$word);
    }

    protected function get_huatis_userid_guanzhu($userid,$page,$myid){
        $Guanzhuht_=$this->load('guanzhuht');
        $Huati_=$this->load('guanzhuht');
        $huatis=$Guanzhuht_->get_huatis_userid_guanzhu($userid,intval($page));
        if (empty($huatis)) {
            $b=array();
            $b['code']=3;
            $b['msg']='没有更多话题';
            $b['huati']=$huatis;
            print_r(json_encode($b));
            return;
        }
        $new=array();

        foreach ($huatis as $key => $ht) {
            $huati=$Huati_->get_huati_byid($ht['htid']);
            $ht=$this->zhengli_ht($huati,$myid);
            $new[]=$ht;
        }

        $b=array();
        $b['code']=0;
        $b['themes']=array('name'=>'画题','type'=>0);
        $b['msg']='成功返回';
        $b['huati']=$new;
        print_r(json_encode($b));

    }

    protected function zhengli_ht_pingbi0zps($huati,$myid){
        $zp4s=$this->get_huati_4zps($huati['htid']);
        if (empty($zp4s)) {
            return array();
        }
        $ht = new huati();
        $ht->id = $huati['htid'];
        $ht->name = $huati['name'];
        $ht->userid = $huati['userid'];
        $ht->attended = $this->is_i_guanzhu($myid,$huati['userid']);;
        $ht->avatar = $huati['user_touxiang'];
        $ht->nickName = $huati['user_name'];
        $ht->attendedTheme=$this->am_i_attendedTheme($myid,$huati['htid']);
        if (empty($huati['creattime'])) {
            $ht->creattime = 0;
        }else{
            $ht->creattime = intval($huati['creattime']);
        }
        $ht->zuopinCount = $this->zuopinCount_huati($huati['htid']);

        $ht->zuopins = $this->zhengli_ht_zps($zp4s);

        $ht->readCount = $this->readCount_huati($huati['htid']);
        $ht->likedCount = $this->get_huati_zp_liketime($huati['htid']);
        $ht->editCount = $this->get_huati_zp_editCount($huati['htid']);
        $ht->shareCount = $this->get_huati_zp_shareCount($huati['htid']);

        return $ht;
    }

    protected function get_huatis_zuixin_page($page,$myid,$version){
        $Huati_=$this->load('huati');
        $huatis=$Huati_->get_huati(intval($page));

        if (empty($huatis)) {
            $b=array();
            if (intval($page) == 0 ) {
                $redis = new Redis();
                $redis->connect('127.0.0.1',6379);
                $callboardsredis=$redis->get('callboardsredis');
                $b['callboards']=json_decode($callboardsredis);
            }
            $b['code']=3;
            $b['msg']='没有更多话题';
            $b['huati']=$huatis;
            print_r(json_encode($b));
            return;
        }

        $new=array();
        foreach ($huatis as $key => $ht) {
            $huati= $Huati_->get_huati_byid($ht['htid']);
            $ht=$this->zhengli_ht_pingbi0zps($huati,$myid);
            /*print_r($ht);
            if (intval($ht) == 0) {
                  continue;
            }  */
            $new[]=$ht;
        }
        if (empty($new)) {
            $b=array();
            if (intval($page) == 0 ) {
                $redis = new Redis();
                $redis->connect('127.0.0.1',6379);
                $callboardsredis=$redis->get('callboardsredis');
                $b['callboards']=json_decode($callboardsredis);
            }
            $b['code']=3;
            $b['themes']=array('name'=>'画题','type'=>0);
            $b['msg']='没有更多话题';
            $b['huati']=$new;
            print_r(json_encode($b));
            return;
        }

        $b=array();
        if (intval($page) == 0 ) {
            $b['callboards']=$this->allcallboardsad($version);
        }
        $b['code']=0;
        $b['themes']=array('name'=>'画题','type'=>0);
        $b['msg']='成功返回';
        $b['huati']=$new;
        print_r(json_encode($b));
    }


    protected function get_huatis_zuixin_page2($page,$myid,$version){
        $Huati_=$this->load('huati');
        $huatis=$Huati_->get_huati(intval($page));

        if (empty($huatis)) {
            $b=array();
            if (intval($page) == 0 ) {
                $b['callboards']=$this->allcallboardsad($version);
            }
            $b['code']=3;
            $b['msg']='没有更多话题';
            $b['huati']=$huatis;
            print_r(json_encode($b));
            return;
        }

        $new=array();
        foreach ($huatis as $key => $ht) {
            if (($ht['htid'] == 41) || ($ht['htid'] == 42) || ($ht['htid'] == 43) || ($ht['htid'] == 44) || ($ht['htid'] == 51) || ($ht['htid'] == 58)) {

                $huati= $Huati_->get_huati_byid($ht['htid']);
                $ht=$this->zhengli_ht_pingbi0zps($huati,$myid);
                /*print_r($ht);
                if (intval($ht) == 0) {
                      continue;
                }  */
                $new[]=$ht;
            }

        }
        if (empty($new)) {
            $b=array();
            if (intval($page) == 0 ) {
                $b['callboards']=$this->allcallboardsad($version);
            }
            $b['code']=3;
            $b['themes']=array('name'=>'画题','type'=>0);
            $b['msg']='没有更多话题';
            $b['huati']=$new;
            print_r(json_encode($b));
            return;
        }

        $b=array();
        if (intval($page) == 0 ) {
            $b['callboards']=$this->allcallboardsad($version);
        }
        $b['code']=0;
        $b['msg']='成功返回';
        $b['huati']=$new;
        print_r(json_encode($b));
    }

    protected function get_huatis_userid_page($userid,$page,$myid){
        $Huati_=$this->load('huati');
        $huatis=$Huati_->get_huatis_byuserid($userid,$page);

        if (empty($huatis)) {
            $b=array();
            $b['code']=3;
            $b['msg']='没有更多作品';
            $b['huati']=$huatis;
            print_r(json_encode($b));
            return;
        }

        $new=array();
        foreach ($huatis as $key => $ht) {
            $huati=$Huati_->get_huati_byid($ht['htid']);
            $ht=$this->zhengli_ht($huati,$myid);
            $new[]=$ht;
        }

        $b=array();
        $b['code']=0;
        $b['themes']=array('name'=>'画题','type'=>0);
        $b['msg']='成功返回';
        $b['huati']=$new;
        print_r(json_encode($b));

    }


    public function get_ht_list2($page){

        $Huati_=$this->load('huati');
        $hts=$Huati_->get_ht_list(intval($page));
        if (empty($hts)) {
            $b['code'] = 3;
            $b['msg'] = '没有更多';
            print_r(json_encode($b));
            return;
        }
        $new=array();
        foreach ($hts as $key => $huati) {
            $ht = new huati();
            $ht->id = $huati['htid'];
            $ht->name = $huati['name'];
            $ht->userid = $huati['userid'];

            if (empty($huati['creattime'])) {
                $ht->creattime = 0;
            }else{
                $ht->creattime = intval($huati['creattime']);
            }
            $new[]=$ht;

        }
        $b['code'] = 0;
        $b['msg'] = '正常返回';
        $b['huati']=$new;
        print_r(json_encode($b));
    }

    protected function get_huati_zps_page($zhuti_id,$page){
       // $this->load->model('zuopin_model');
        $zps=$this->Zuopin_->get_huati_zps_page($zhuti_id,$page);
        return $zps;
    }
    //某个主题，话题的作品，返回最新
    protected function get_huati_zps($zhuti_id,$myid,$page){
        $zps=$this->get_huati_zps_page($zhuti_id,$page);
        if (empty($zps)) {
            $b=array();
            $b['code']=3;
            $b['msg']='没有作品';
            $b['attendedTheme']=$this->am_i_attendedTheme($myid,$zhuti_id);
            $b['zuopins']=array();
            print_r(json_encode($b));
            return;
        }
        $new=$this->show_zhengli($zps,$myid);
        $b=array();
        $b['code']=0;
        $b['msg']='作品列表如下';
        $b['attendedTheme']=$this->am_i_attendedTheme($myid,$zhuti_id);
        $b['zuopins']=$new;
        print_r(json_encode($b));
    }

    protected function get_ht_zps_remen($page,$htid,$myid){
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $min=$page*21;
        $max=($page+1)*21-1;
        $zpids=$redis->zRevRange('huatizps::'.$htid, $min,$max);
        if (empty($zpids)) {
            $b=array();
            $b['zhuti']=array();
            $b['code']=3;
            $b['msg']='没有作品';
            $b['zuopins']=$zpids;
            print_r(json_encode($b));
            return;
        }
        $zuopins=$this->zhengli_remen_by_redu_redis($zpids,$myid);
        $b['zhuti']=array();
        $b['code']=0;
        $b['msg']='作品列表如下';
        $b['zuopins']=$zuopins;
        print_r(json_encode($b));
        return;
    }



    protected function show_zpids_all_by_userid($userid){
        //$this->load->model('zuopin_model');
        return $this->Zuopin_->show_zpids_all_by_userid($userid);
    }

    //show 我关注的作品
    public function show_guanzhu($myid,$page){
        $zuopins= $this->Zuopin_->show_guanzhu($myid,$page);
        return $zuopins;

    }

    //取得某个userid的所有点赞作品
    protected function get_zpid_byuserid_fromzan($userid){
        $Zan_=$this->load('zan');
        //$this->load->model('zan_model');
        $zpids=$Zan_->get_zpid_byuserid_fromzan($userid);
        return $zpids;
    }

    protected function zhengli_redu($zpids){
        if (empty($zpids)) {
            return $zpids;
        }
        $new=array();
        foreach ($zpids as $key => $zp) {
            $score=$this->get_zp_redu($zp['zuopinid']);
            if (empty($score)) {
                $score = 0;
            }
            $new[$zp['zuopinid']]=$score;
        }
        //得到一个zpid对应 score的数组
        //print_r($new);
        //按照值，降序排
        arsort($new);
        return $new;

    }

    //取得每个作品的热门指数
    protected function get_zp_redu($zpid){
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $redu=$redis->zScore('remen_zps',$zpid);
        return $redu;
    }

    //取page
    protected function zhengli_redu_bypage($redu,$page){
        $min=$page*21;
        $max=($page+1)*21;
        $t=0;
        $new=array();
        foreach ($redu as $zpid => $value) {
            if ($t >= $min && $t < $max) {
                $new[]=$zpid;
            }
            $t=$t+1;
        }
        return $new;
    }

    //查询是否赞
    public function is_zan($zuopinid,$userid){
        //每次作品列表都有点赞，每一次21个，就得查询21遍，用redis代替
        /*$this->load->model('zan_model');
        return $this->zan_model->is_zan($zuopinid,$userid);*/
        if (empty($userid)) {
            return false;
        }
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $is=$redis->zScore('userdetail::'.$userid,'zan:'.$zuopinid);
        if ($is) {
            return true;
        }
        return false;
    }

    public function dian_zan($zuopinid,$userid){
        $Zan_=$this->load('zan');
        return $Zan_->dian_zan($zuopinid,$userid);
    }

    public function delete_zan($zuopinid,$userid){
        $Zan_=$this->load('zan');
        return $Zan_->delete_zan($zuopinid,$userid);
    }


    protected function zan_redis($zuopinid,$userid){
        //1初始化redis
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);

        //2取得作品的信息
        $zp=$this->get_zuopin_by_zpid($zuopinid);
        //2.1过滤$zp
        if (empty($zp)) {
            return;
        }
        //3和作品相关的redis排行榜
        //3.1 zan_times加1
        $redis->zIncrBy('zan_times',1,$zuopinid);
        //3.2 userszp_remen::$userid  某个$userid的热门作品排序(SortedSet)
        $redis->zIncrBy('userszp_remen::'.$zp['userid'],5,$zuopinid);
        //3.2 remen_zps 所有的作品热度排序(SortedSet)
        $redis->zIncrBy('remen_zps',5,$zuopinid);
        //3.3每天的日榜
        $redis->zIncrBy('remenzps::'.date('Ymd',$zp['zp_creat_time']),5,$zuopinid);

        //4和用户相关的redis排行榜
        //4.1增加进本周的用户榜
        $num=intval(date('N',time()));
        $today=strtotime(date('Y-m-d 00:00:00'));
        $min=$today-($num-1)*86400;
        $max=$today+(8-$num)*86400;
        if ($zp['zp_creat_time'] < $max && $zp['zp_creat_time'] > $min) {
            $redis->zIncrBy('week',5,$zp['userid']);
            $redis->zIncrBy('week::'.date('YW',$zp['zp_creat_time']),5,$zp['userid']);

        }
        //4.2增加进总的用户榜
        $redis->zIncrBy('zongbang',5,$zp['userid']);

        //5.话题相关
        //5.1话题被点赞数 huati_liketimes
        $htzp=$this->get_htzp_zpid($zuopinid);
        if (!empty($htzp)) {
            $redis->zIncrBy('huati_liketimes',1,$htzp['htid']);
            //5.2话题作品热门榜  huatizps::$tagid
            $redis->zIncrBy('huatizps::'.$htzp['htid'],5,$zuopinid);
        }

        //6 user_detail相关
        //6.1我喜欢的作品数
        $redis->zIncrBy('userdetail::'.$userid, 1,'zan_number');
        //增加他喜欢的作品
        $redis->zAdd('userdetail::'.$userid, 1,'zan:'.$zuopinid);
        //他的所有作品被喜欢的数
        $redis->zIncrBy('userdetail::'.$zp['userid'], 1,'bei_zan_number');

        //s
    }


    protected function tuijian_zps_ymd($zps){
        $new=array();
        $t='';
        foreach ($zps as $key => $zp) {
            if ($t !== $zp['t']) {
                $t = $zp['t'];
            }
            $new[$t][]=$zp;
        }
        return $new;
    }

    //生成新推荐
    public function get_tuijian_zps(){

        $zps= $this->Zuopin_->get_all_tuijian_zps();
        //整理成ymd为key value的数组
        $new=$this->tuijian_zps_ymd($zps);
        //print_r($new);
        //按热度排序
        $new_zps=$this->zhengli_redu_tuijian($new);
        $he_zps=$this->he_zps($new_zps);
        //print_r($he_zps);
        //写进推荐热门
        $ss=json_encode($he_zps);
        //print_r(base64_encode($ss));
        //存redis
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $redis->set('tuijian',base64_encode($ss));
    }

    protected function he_zps($new_zps){
        $new=array();
        foreach ($new_zps as $key => $value) {
            if (!empty($value)) {
                foreach ($value as $k => $zp) {
                    $new[]=$zp;
                }
            }
        }
        return $new;
    }

    protected function paixu_redu($zpgroup){
        $new=array();
        $redu_times=array();
        $edition=array();
        foreach ($zpgroup as $key => $zp) {
            $tmp=array();
            $userinfo=$this->user_getbyzpid($zp['zuopin_id']);


            $tmp['user_name'] = $userinfo['user_name'];
            $tmp['user_touxiang'] = $userinfo['user_touxiang'];
            if(intval($userinfo['quxiao_remen']) == 2) {
                $tmp['isRecommend'] = true;
            }else{
                $tmp['isRecommend'] = false;
            }
            $tmp['zp_creat_time']=$zp['zp_creat_time'];
            $tmp['zuopin_id']=$zp['zuopin_id'];
            $tmp['zuopin_url']=$zp['zuopin_url'];
            $tmp['liked_times'] = $this->liked_times($zp['zuopin_id']);
            $tmp['fenxiang_times'] = $this->get_fenxiang_times($zp['zuopin_id']);
            $tmp['edit_times'] = $this->get_edit_times($zp['zuopin_id']);
            $tmp['remen_times'] = $tmp['liked_times']*5 + $tmp['edit_times']*5+$tmp['fenxiang_times']*2;
            $tmp['t']=date('Ymd',$zp['zp_creat_time']);

            $tmp['userid']=$zp['userid'];



            $new[]= $tmp;
            $redu_times[$key]  =$tmp['remen_times'];
            $edition[$key] = $zp['zuopin_id'];
        }
        // 将数据根据 volume 降序排列，根据 edition 升序排列
        array_multisort($redu_times, SORT_DESC, $edition, SORT_DESC, $new);
        return $new;
    }

    protected function zhengli_redu_tuijian($zps){

        $new=array();
        foreach ($zps as $key => $zpgroup) {
            $tmp=$this->paixu_redu($zpgroup);

            $new[]=$tmp;
        }
        return $new;
    }

    //增加通知
    protected function add_notify($userid,$zpid,$type){
        $Notify_=$this->load('notify');
        $Notify_->add_notify($userid,$zpid,$type);
    }

    protected function get_comments_by_comment_id($commont_id){
        $C_=$this->load('comment');
        return $C_->get_comments_by_comment_id($commont_id);
    }
    //显示某个作品的所有评论
    protected function get_comments_by_zpid($zuopinid){
        $C_=$this->load('comment');
        return $C_->get_comments_by_zpid($zuopinid);
    }


    public function add_notify_tuisong_xiangguan($userid,$zuopinid){
        $C_=$this->load('comment');
        $Notify_=$this->load('notify');
        $userids=$C_->get_userids_comments_xiangguan_by_zpid($zuopinid);
        $zpuser=$this->Zuopin_->get_zp_userid($zuopinid);
        foreach ($userids as $key => $my) {
            if ($my['userid'] == $zpuser['userid']) {
                continue;
            }
            if ($my['userid'] == $userid) {
                continue;
            }
            $Notify_->add_notify_xiangguan($userid,$my['userid'],$zuopinid,$zpuser['zuopin_url'],5);
            /*usleep(20000);
            $this->yibu('comment_tuisong_xiangguan',$userid,$my['userid']);*/

        }


    }

    protected function zhengli_comment($comments){
        $new=array();
        foreach ($comments as $key => $comment) {
            $tmp=array();
            $tmp['userid']=$comment['user_id'];
            $tmp['touxiang']=$comment['user_touxiang'];
            $tmp['nickname']=$comment['user_name'];
            $tmp['text']=$comment['comment_text'];
            $tmp['zpid']=$comment['zuopinid'];
            $tmp['commentid']=$comment['comment_id'];
            $tmp['commenttime']=$comment['comment_time'];
            $tmp['name']=$comment['user_name'];
            $new[]=$tmp;
        }
        return $new;
    }




    protected function fenxiang_redis($zuopinid){
        //1初始化redis
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);

        //2取得作品的信息
        $zp=$this->get_zuopin_by_zpid($zuopinid);
        //2.1过滤$zp
        if (empty($zp)) {
            return;
        }
        //3和作品相关的redis排行榜
        //3.1 fenxiang_times加1
        $redis->zIncrBy('fenxiang_times',1,$zuopinid);
        //3.2 userszp_remen::$userid  某个$userid的热门作品排序(SortedSet)
        $redis->zIncrBy('userszp_remen::'.$zp['userid'],2,$zuopinid);
        //3.2 remen_zps 所有的作品热度排序(SortedSet)
        $redis->zIncrBy('remen_zps',2,$zuopinid);
        //3.3每天的日榜
        $redis->zIncrBy('remenzps::'.date('Ymd',$zp['zp_creat_time']),2,$zuopinid);

        //4和用户相关的redis排行榜
        //4.1增加进本周的用户榜
        $num=intval(date('N',time()));
        $today=strtotime(date('Y-m-d 00:00:00'));
        $min=$today-($num-1)*86400;
        $max=$today+(8-$num)*86400;
        if ($zp['zp_creat_time'] < $max && $zp['zp_creat_time'] > $min) {
            $redis->zIncrBy('week',2,$zp['userid']);
            $redis->zIncrBy('week::'.date('YW',$zp['zp_creat_time']),2,$zp['userid']);


        }
        //4.2增加进总的用户榜
        $redis->zIncrBy('zongbang',2,$zp['userid']);

        //5.话题相关
        //5.1话题被点赞数 huati_liketimes
        $htzp=$this->get_htzp_zpid($zuopinid);
        if (!empty($htzp)) {
            $redis->zIncrBy('huati_sharetimes',1,$htzp['htid']);
            //5.2话题作品热门榜  huatizps::$tagid
            $redis->zIncrBy('huatizps::'.$htzp['htid'],2,$zuopinid);
        }

    }

    protected function delete_zan_redis($userid,$zuopinid){
        //1初始化redis
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);

        //2取得作品的信息
        $zp=$this->get_zuopin_by_zpid($zuopinid);
        //2.1过滤$zp
        if (empty($zp)) {
            return;
        }
        //3和作品相关的redis排行榜
        //3.1 zan_times加1
        $redis->zIncrBy('zan_times',-1,$zuopinid);
        //3.2 userszp_remen::$userid  某个$userid的热门作品排序(SortedSet)
        $redis->zIncrBy('userszp_remen::'.$zp['userid'],-5,$zuopinid);
        //3.2 remen_zps 所有的作品热度排序(SortedSet)
        $redis->zIncrBy('remen_zps',-5,$zuopinid);
        //3.3每天的日榜
        $redis->zIncrBy('remenzps::'.date('Ymd',$zp['zp_creat_time']),-5,$zuopinid);

        //4和用户相关的redis排行榜
        //4.1增加进本周的用户榜
        $num=intval(date('N',time()));
        $today=strtotime(date('Y-m-d 00:00:00'));
        $min=$today-($num-1)*86400;
        $max=$today+(8-$num)*86400;
        if ($zp['zp_creat_time'] < $max && $zp['zp_creat_time'] > $min) {
            $redis->zIncrBy('week',-5,$zp['userid']);
            $redis->zIncrBy('week::'.date('YW',$zp['zp_creat_time']),-5,$zp['userid']);

        }
        //4.2增加进总的用户榜
        $redis->zIncrBy('zongbang',-5,$zp['userid']);

        //5.话题相关
        //5.1话题被点赞数 huati_liketimes
        //5.话题相关
        //5.1话题被点赞数 huati_liketimes
        $htzp=$this->get_htzp_zpid($zuopinid);
        if (!empty($htzp)) {
            $redis->zIncrBy('huati_liketimes',-1,$htzp['htid']);
            //5.2话题作品热门榜  huatizps::$tagid
            $redis->zIncrBy('huatizps::'.$htzp['htid'],-5,$zuopinid);
        }

        //6 user_detail相关
        //6.1我喜欢的作品数
        $redis->zIncrBy('userdetail::'.$userid, -1,'zan_number');
        //删除他喜欢的作品
        $redis->zDelete('userdetail::'.$userid,'zan:'.$zuopinid);
        //他的所有作品被喜欢的数
        $redis->zIncrBy('userdetail::'.$zp['userid'], -1,'bei_zan_number');

    }
    protected function get_htzp_zpid($zpid){
        $Htpzs_= $this->load('htzps');
        return $Htpzs_->get_htzp_zpid($zpid);
    }

    public function incr_wenzi($zuopinid){
        $Wenzi_=$this->load('wenzi');
        $Wenzi_->incr_wenzi($zuopinid);
    }

    public function get_remen_page($zps,$page){
        $new=array();
        if (empty($zps)) {
            return $new;
        }
        foreach ($zps as $key => $value) {
            if ($key>=$page*21 && $key<($page+1)*21) {
                $new[]=$value;
            }
        }
        return $new;
    }

    //取出广告
    public function get_ads(){
        $Ads_=$this->load('ads');
        /*$this->load->model('Zan_model');*/
        return  $Ads_->get_ads();

    }

    //增加广告
    public function ad_ads(){
        $zuopinid=$this->getPost('zuopinid');
        $ad=$this->get_ads_byzpid($zuopinid);
        if (empty($ad)) {
            $Ads_=$this->load('ads');
            //$this->load->model('Zan_model');
            $Ads_->ad_ads($zuopinid);
            echo $zuopinid;
            return ;
        }
        echo "数据库里已有";


    }
    //删除广告
    public function delete_ads(){
        $zuopinid=$this->getPost('zuopinid');
        $Ads_=$this->load('ads');
        //$this->load->model('Zan_model');
        $Ads_->delete_ads($zuopinid);
    }
    public function get_ads_byzpid($zpid){
        $Ads_=$this->load('ads');
        //$this->load->model('Zan_model');
        return $Ads_->get_ads_byzpid($zpid);

    }

    public function zhengli_remen_ads($myid){
        $new=array();
        $ads=$this->get_ads();
        foreach ($ads as $key => $zuopin) {
            $tmp=array();
            $tmp['zuopin_id'] = $zuopin['zuopin_id'];
           /* $tmp['tagid'] = $zuopin['tagid'];*/
            $tmp['zuopin_url'] = $zuopin['zuopin_url'];
            /*$tmp['userid'] = $zuopin['userid'];*/
            $tmp['remen_times'] = 0;
            $tmp['comment_times'] = $this->comment_times($zuopin['zuopin_id']);
            $tmp['fenxiang_times'] =$this->get_fenxiang_times($zuopin['zuopin_id']);
            $tmp['edit_times'] = $this->get_edit_times($zuopin['zuopin_id']);
            $tmp['liked_times'] = $this->liked_times($zuopin['zuopin_id']);
            $/*tmp['nickname'] = $zuopin['user_name'];
            $tmp['touxiang'] = $zuopin['user_touxiang'];*/
            $tmp['am_i_zan'] = $this->is_zan($zuopin['zuopin_id'],$myid);
            $tmp['zp_creat_time'] = time();
            $new[]=$tmp;

        }
        return $new;
    }

    public function ad__remen_zhengli($zuopins,$myid){
        $new=array();
        $ads=$this->zhengli_remen_ads($myid);
        $new=$ads;
        foreach ($zuopins as $key => $zuopin) {
            $tmp=array();
            $tmp['zuopin_id'] = $zuopin['zuopin_id'];
            /*$tmp['tagid'] = $zuopin['tagid'];*/
            $tmp['zuopin_url'] = $zuopin['zuopin_url'];
          /*  $tmp['userid'] = $zuopin['userid'];*/
          /*  if ($tmp['userid'] == '6') {
                $tmp['type'] = 1;
                $tmp['webviewurl'] = $this->add_ads($tmp['zuopin_id']);
            }*/
           /* $tmp['remen_times'] = $zuopin['remen_times'];*/
            $tmp['comment_times'] = $zuopin['comment_times'];
            $tmp['fenxiang_times'] = $zuopin['fenxiang_times'];
            $tmp['edit_times'] = $zuopin['edit_times'];
            $tmp['liked_times'] = $zuopin['liked_times'];
           /* $tmp['nickname'] = $zuopin['nickname'];*/
            $tmp['isRecommend'] = $zuopin['isRecommend'];
            $tmp['am_i_zan'] = $zuopin['am_i_zan'];
            $tmp['zp_creat_time'] = $zuopin['zp_creat_time'];

            $new[]=$tmp;

        }
        return $new;
    }

    //移除某个作品的热度
    public  function zuopin_redis_delete($zuopinid){
        //1初始化redis
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);

        //2取得作品的信息
        $zp=$this->get_zuopin_by_zpid($zuopinid);
        //2.1过滤$zp
        if (empty($zp)) {
            return;
        }
        //3获得热度(zan_times edit_times )
        $liked_times = $this->liked_times($zp['zuopin_id']);
        $fenxiang_times = $this->get_fenxiang_times($zp['zuopin_id']);
        $edit_times = $this->get_edit_times($zp['zuopin_id']);
        $redu = $liked_times*5 + $edit_times*5+ $fenxiang_times*2;

        //4先减少，再删除
        $num=intval(date('N',time()));
        $today=strtotime(date('Y-m-d 00:00:00'));
        $min=$today-($num-1)*86400;
        $max=$today+(8-$num)*86400;
        if ($zp['zp_creat_time'] < $max && $zp['zp_creat_time'] > $min) {
            $redis->zIncrBy('week','-'.$redu,$zp['userid']);
            $redis->zIncrBy('week::'.date('YW',$zp['zp_creat_time']),'-'.$redu,$zp['userid']);
        }
        //4.2增加进总的用户榜
        $redis->zIncrBy('zongbang','-'.$redu,$zp['userid']);

        //5.话题相关
        //5.1话题被点赞数 huati_liketimes
        $htzp=$this->get_htzp_zpid($zuopinid);
        if (!empty($htzp)) {
            $redis->zIncrBy('huati_liketimes','-'.$liked_times,$htzp['htid']);
            //5.2话题作品热门榜  huatizps::$tagid
            $redis->zDelete('huatizps::'.$htzp['htid'],$zuopinid);
        }


        //6 user_detail相关
        //6.1我喜欢的作品数
        //获取这个作品被谁点赞了
        $Zan_=$this->load('zan');
        $userids=$Zan_->all_zanlist($zuopinid);
        //print_r($userids);
        if (!empty($userids)) {
            foreach ($userids as $key => $user) {
                if($user['userid']){
                    $redis->zIncrBy('userdetail::'.$user['userid'], -1,'zan_number');
                }

            }
        }
        //他的所有作品被喜欢的数
        $redis->zIncrBy('userdetail::'.$zp['userid'],'-'.$liked_times,'bei_zan_number');
        $redis->zIncrBy('userdetail::'.$zp['userid'],-1,'mineZuopinCount');

        //3和作品相关的redis排行榜
        //3.1 zan_times加1
        $redis->zDelete('zan_times',$zuopinid);
        //3.2 userszp_remen::$userid  某个$userid的热门作品排序(SortedSet)
        $redis->zDelete('userszp_remen::'.$zp['userid'],$zuopinid);
        //3.2 remen_zps 所有的作品热度排序(SortedSet)
        $redis->zDelete('remen_zps',$zuopinid);
        //3.3每天的日榜
        $redis->zDelete('remenzps::'.date('Ymd',$zp['zp_creat_time']),$zuopinid);


    }

    protected function delete_zuopin_by_zuopinid($zuopinid){
        return $this->Zuopin_->delete_zuopin_by_zuopinid($zuopinid);
    }


    //mineZuopinCount: String 类型  ，我的作品总数
    protected function mineZuopinCount($myid){
        return $this->Zuopin_->mineZuopinCount($myid);
    }
    //likeZuopinCount: String 类型 ，我喜欢的作品总数
    protected function likeZuopinCount($userid){
        return $this->Zuopin_->likeZuopinCount($userid);
    }
    //我关注的人的中作品

    protected function guanzhuCount($userid){
        $Guanzhu_=$this->load('guanzhu');
        return $Guanzhu_->guanzhuCount($userid);
    }
    //获得评论总数
    protected function messageCount($userid){
        $Comment_=$this->load('comment');
        return $Comment_->messageCount($userid);
    }


    protected function relatedThemeNum($userid){
        $MyThemeNum = $this->MyThemeNum($userid);
        $MyGuanzhuThemeNum= $this->MyGuanzhuThemeNum($userid);
        $n=$MyThemeNum+$MyGuanzhuThemeNum;
        return $n;
    }
    //我的话题总数
    protected function MyThemeNum($userid){
        $Huati_=$this->load('huati');
        return $Huati_->MyThemeNum($userid);
    }
    //我关注的话题总数
    protected function MyGuanzhuThemeNum($userid){
        $Guanzhuht_=$this->load('guanzhuht');
        return $Guanzhuht_->MyGuanzhuThemeNum($userid);
    }


    public function edit_redis($zuopinid){
        //1初始化redis
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);

        //2取得作品的信息
        $zp=$this->get_zuopin_by_zpid($zuopinid);
        //2.1过滤$zp
        if (empty($zp)) {
            return;
        }
        //3和作品相关的redis排行榜
        //3.1 fenxiang_times加1
        $redis->zIncrBy('edit_times',1,$zuopinid);
        //3.2 userszp_remen::$userid  某个$userid的热门作品排序(SortedSet)
        $redis->zIncrBy('userszp_remen::'.$zp['userid'],5,$zuopinid);
        //3.2 remen_zps 所有的作品热度排序(SortedSet)
        $redis->zIncrBy('remen_zps',5,$zuopinid);
        //3.3每天的日榜
        $redis->zIncrBy('remenzps::'.date('Ymd',$zp['zp_creat_time']),5,$zuopinid);

        //4和用户相关的redis排行榜
        //4.1增加进本周的用户榜
        $num=intval(date('N',time()));
        $today=strtotime(date('Y-m-d 00:00:00'));
        $min=$today-($num-1)*86400;
        $max=$today+(8-$num)*86400;
        if ($zp['zp_creat_time'] < $max && $zp['zp_creat_time'] > $min) {
            $redis->zIncrBy('week',5,$zp['userid']);
            $redis->zIncrBy('week::'.date('YW',$zp['zp_creat_time']),5,$zp['userid']);

        }
        //4.2增加进总的用户榜
        $redis->zIncrBy('zongbang',5,$zp['userid']);

        //5.话题相关
        //5.1话题被点赞数 huati_edittimes
        $htzp=$this->get_htzp_zpid($zuopinid);
        if (!empty($htzp)) {
            $redis->zIncrBy('huati_edittimes',1,$htzp['htid']);
            //5.2话题作品热门榜  huatizps::$tagid
            $redis->zIncrBy('huatizps::'.$htzp['htid'],5,$zuopinid);
        }

    }

    public function get_lingjian_by_zuopinid($zuopinid){
        $Lingjian_=$this->load('lingjian');
        return $Lingjian_->get_lingjian_by_zuopinid($zuopinid);
    }

    public function get_guanxi_by_zuopinid($zuopinid){
        $Guanxi_=$this->load('guanxi');
        return $Guanxi_->get_guanxi_by_zuopinid($zuopinid);
    }

    public function get_zuopin_by_zpid($zuopinid){


        return $this->Zuopin_->get_zuopin_by_zpid($zuopinid);
    }

    protected function zhengli_bangdan_guanzhu($userzps,$myid,$page){
        $new=array();
        if(empty($userzps)){
            return $new;
        }
        $min=$page*11;
        $max=($page+1)*11;
        foreach ($userzps as $key => $userzp) {


            if($key < $min){
                continue;
            }

            if($key >= $max){
                break;
            }


            if (empty($userzp['zuopins'])) {
                continue;
            }

            $tmp=array();
            $tmp['uid']=$userzp['uid'];
            $tmp['attended']=$this->is_i_guanzhu($myid,$userzp['uid']);
            $tmp['nickname']=$userzp['nickname'];
            $tmp['touxiang']=$userzp['touxiang'];
            $zps=array();
            foreach($userzp['zuopins'] as $key => $zp){
                $t=array();
                $t['zuopin_id']=$zp['zuopin_id'];
                $t['zuopin_url']=$zp['zuopin_url'];
                $t['isRecommend']=$zp['isRecommend'];



                $zps[]=$t;
            }
            $tmp['zuopins']= $zps;
            $tmp['redu']=$userzp['redu'];

            $new[]=$tmp;

        }
        return $new;
    }

    protected function get_myid_bang_yue($myid){
        if ($myid == 0) {
            return 0;
        }
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $bang=$redis->zRevRank('zongbang',$myid);
        if (empty($bang) && $bang !==0) {
            return -1;
        }
        $bang=$bang+1;
        return $bang;
    }

    protected function get_myid_bang_week($myid){
        if ($myid == 0) {
            return 0;
        }

        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $bang=$redis->zRevRank('week',$myid);

        if (empty($bang) && $bang !==0) {
            return -1;
        }

        $bang=$bang+1;
        return $bang;
    }

    public function show_zhengli_remen2($zuopins,$myid){
        $new=array();
        foreach ($zuopins as $key => $zuopin) {
            $tmp=array();
            //$user=$this->user_getbyzpid($zuopin['zuopin_id']);
            //$zp=$this->get_zuopin_by_zpid($zuopin['zuopin_id']);
            $tmp['zuopin_id'] = $zuopin['zuopin_id'];
            $tmp['zuopin_url'] = $zuopin['zuopin_url'];
           /* $tmp['tagid'] = $zp['tagid'];
            $tmp['userid'] = $zp['userid'];*/
            if ($zuopin['userid'] == '6') {
                /*$tmp['type'] = 1;
                $tmp['webviewurl'] = $this->add_ads($tmp['zuopin_id']);*/
                continue;
            }
            /*$tmp['liked_times'] = $this->liked_times($zuopin['zuopin_id']);;
            $tmp['comment_times'] = $this->comment_times($zuopin['zuopin_id']);
            $tmp['fenxiang_times'] = $this->get_fenxiang_times($zuopin['zuopin_id']);
            $tmp['edit_times'] = $this->get_edit_times($zuopin['zuopin_id']);*/
            $tmp['liked_times'] = $this->liked_times($zuopin['zuopin_id']);
            $tmp['comment_times'] = $this->comment_times($zuopin['zuopin_id']);;
            $tmp['fenxiang_times'] =$zuopin['fenxiang_times'];
            $tmp['edit_times'] =$zuopin['edit_times'];
            /*$tmp['remen_times'] = $tmp['liked_times']*5 + $tmp['edit_times']*5+$tmp['fenxiang_times']*2;*/
            //$tmp['remen_times']=$zuopin['redu_times'];
           /* $tmp['nickname'] = $zuopin['user_name'];
            $tmp['touxiang'] = $zuopin['user_touxiang'];*/
            $tmp['isRecommend'] = $zuopin['isRecommend'];
            $tmp['am_i_zan']=$this->is_zan($zuopin['zuopin_id'],$myid);
            if (empty($zuopin['zp_creat_time'])) {
                $tmp['zp_creat_time']=time();
            }else{
                $tmp['zp_creat_time']=(int)$zuopin['zp_creat_time'];
            }
            //一个过滤，我举报的作品不显示

            $new[]=$tmp;

        }

        return $new;

    }


    //整理新的数组
    protected function show_zhengli($zuopins,$myid){
        $new=array();
        foreach ($zuopins as $key => $zuopin) {
           // $am_i_jubao=$this->am_i_jubao($zuopin['zuopin_id'],$myid);
            /*if ($am_i_jubao){
                continue;
            }*/

            $tmp=array();
            $tmp['zuopin_id'] = $zuopin['zuopin_id'];
            //$tmp['tagid'] = $zuopin['tagid'];
            $tmp['zuopin_url'] = $zuopin['zuopin_url'];
            //$tmp['userid'] = $zuopin['userid'];
          /*  if ($tmp['userid'] == '6') {*/
                /*$tmp['type'] = 1;
                $tmp['webviewurl'] = $this->add_ads($tmp['zuopin_id']);*/
               /* continue;*/
          /*  }*/

            if (intval($zuopin['quxiao_remen']) == 2) {
                $tmp['isRecommend'] =true;
            }else{
                $tmp['isRecommend'] =false;
            }
            $tmp['liked_times'] = $this->liked_times($zuopin['zuopin_id']);
            $tmp['comment_times'] = $this->comment_times($zuopin['zuopin_id']);
            $tmp['fenxiang_times'] = $this->get_fenxiang_times($zuopin['zuopin_id']);
            $tmp['edit_times'] = $this->get_edit_times($zuopin['zuopin_id']);
            //$tmp['remen_times'] = $tmp['liked_times'] + $tmp['comment_times']+ $tmp['edit_times'];
           /* $tmp['nickname'] = $zuopin['user_name'];
            $tmp['touxiang'] = $zuopin['user_touxiang'];*/
            $tmp['am_i_zan']=$this->is_zan($zuopin['zuopin_id'],$myid);

            if (empty($zuopin['zp_creat_time'])) {
                $tmp['zp_creat_time']=time();
            }else{
                $tmp['zp_creat_time']=(int)$zuopin['zp_creat_time'];
            }

            $new[]=$tmp;


        }
        return $new;
    }

    protected function add_ads($zpid){
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        return $redis->hGet('ads', $zpid);
    }

    //判断我是否举报了，该作品，举报了的话，对我不显示
    protected function am_i_jubao($zuopinid,$myid){
        $Jubao_=$this->load('jubao');
       // $this->load->model('Zan_model');
        return $Jubao_->am_i_jubao($zuopinid,$myid);
    }


    public function callboardstest(){
        $c=array();
        $c['imageUrl']='callboardsad71463665433.jpg';

        $c0 = $c;
        $c0['type']=0;
        $c0['webUrl']='http://www.qq.com';
        $new[]=$c0;

        $c1 = $c;
        $c1['type']=1;
        $c1['zpid']='10001';
        $new[]=$c1;


        $c2 = $c;
        $c2['type']=2;
        $c2['userid']='3';
        $new[]=$c2;

        $c4 = $c;
        $c4['type']=4;
        $c4['rankIndex']=1;
        $new[]=$c4;


        $c5 = $c;
        $c5['type']=5;
        $c5['tmemeid']=41;
        $c5['themename']='美女们快来报到，报表情';
        $new[]=$c5;


        $c6 = $c;
        $c6['type']=6;
        $c6['selectid']=1;
        $c6['selectname']='test';
        $new[]=$c6;


        $c7 = $c;
        $c7['type']=7;
        $c7['qquin']="481863166";
        $c7['qqkey']="7147d2b336736627289faa8e5d923e8754aa51f1185acdc8683e3a8c6dcbcff3";
        $c7['qqandroidkey']="NwCFlLmg9A1bpLgeBqYZNb_vXhfh5KrC";
        $new[]=$c7;


        $c8 = $c;
        $c8['type']=8;
        $c8['webUrl']='http://api.all-appp.com/hi/pemojizhuanfa';
        $new[]=$c8;


        $c9 = $c;
        $c9['type']=9;
        $c9['pasteString']='点击这里复制内容到剪贴板';
        $new[]=$c9;

        $c10 = $c;
        $c10['type']=10;
        $new[]=$c10;





        return $new;
    }

    public function allcallboardsad($version){
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $allKeys = $redis->keys('callboardsad::*');

        $new=array();
        foreach ($allKeys as $key => $value) {
            $tmp = array();
            $tmp = $redis->hGetAll($value);
            $new[ltrim($value,"callboardsad::")]=$tmp;
        }
        //print_r($new);

        $new2=array();
        foreach ($new as $key => $value) {
            if ($value['isHide'] == 'true') {
                continue;
            }
            $tmp=array();
            $tmp=$value;
            if ($value['type'] == '1') {
                $callboards=array();
                $myid=0;
                $zpid = $redis->hGet('callboardsad::'.$key, 'zpid');
                $tmp7=array();
                $zp=$this->remen_zp_by_zpid($zpid);
                $tmp7['zuopin_id'] = $zpid;
                $tmp7['zuopin_url'] = $zp['zuopin_url'];
                $tmp7['tagid'] = $zp['tagid'];
                $tmp7['userid'] = $zp['userid'];
                $tmp7['liked_times'] = $this->liked_times($zp['zuopin_id']);
                $tmp7['comment_times'] = $this->comment_times($zp['zuopin_id']);
                $tmp7['fenxiang_times'] =$this->get_fenxiang_times($zp['zuopin_id']);
                $tmp7['edit_times'] = $this->get_edit_times($zp['zuopin_id']);
                $tmp7['remen_times'] = $tmp7['liked_times']*5 + $tmp7['edit_times']*5+$tmp7['fenxiang_times']*2;
                //$$tmp7['remen_times']=$zp['redu_times'];
                $tmp7['nickname'] = $zp['user_name'];
                $tmp7['touxiang'] = $zp['user_touxiang'];
                $tmp7['isRecommend'] = false;
                $tmp7['am_i_zan']=$this->is_zan($zp['zuopin_id'],$myid);
                if (empty($zp['zp_creat_time'])) {
                    $tmp7['zp_creat_time']=time();
                }else{
                    $tmp7['zp_creat_time']=(int)$zp['zp_creat_time'];
                }
                //一个过滤，我举报的作品不显示
                $tmp['zuopin']=$tmp7;
            }

            if ($value['type'] == '5') {
                if (!empty($themeid)) {

                    $Huati_=$this->load('huati');
                    //$this->load->model('tag_model');
                    $theme=$Huati_->get_theme_by_themeid($themeid);


                    if (!empty($theme)) {
                        $tmp['themename']=$theme['name'];
                    }

                }
            }
            if (isset($tmp['rankIndex'])) {
                $tmp['rankIndex']= intval($value['rankIndex']);
            }

            if ($value['type'] == '6') {
                $app_version=$redis->hGet('see_version', '1');
                if (version_compare($app_version,$version) > 0) {
                    continue;
                }

            }
            //版本大约 10
            if (intval($value['type']) >= 10) {
                if (version_compare($version,'0.2.2') >= 0) {
                    $tmp['type']= intval($value['type']);
                    $new2[]=$tmp;
                }else{
                    $tmp['type']=0;
                    $new2[]=$tmp;
                }
            }else{
                $tmp['type']= intval($value['type']);
                $new2[]=$tmp;
            }
        }

        return $new2;
    }

    //取得某个userid的所有关注人的作品
    protected function get_userids_byuserid_fromguanzhu($userid){
        $Guanzhu_=$this->load('guanzhu');
        //$this->load->model('zan_model');
        $zpids=$Guanzhu_->get_userids_byuserid_fromguanzhu($userid);
        return $zpids;
    }

    public function zhengli_remen_by_redu($zpids,$myid){
        $new=array();
        foreach ($zpids as $key => $zpid) {
            if (empty($zpid)) {
                continue;
            }
            $zuopin=$this->Zuopin_->geturl($zpid);
            if (empty($zuopin)) {
                continue;
            }
            if (intval($zuopin['pingbi']) == 1) {
                continue;
            }
            $tmp=array();
            $tmp['zuopin_id'] = $zpid;
            $tmp['zuopin_url'] = $zuopin['zuopin_url'];
           /* $tmp['tagid'] = $zuopin['tagid'];
            $tmp['userid'] = $zuopin['userid'];*/
          /*if ($tmp['userid'] == '6') {
             $tmp['type'] = 1;
                $tmp['webviewurl'] = $this->add_ads($tmp['zuopin_id']);
                continue;
            }*/
            $tmp['liked_times'] = $this->liked_times($zpid);
            $tmp['comment_times'] = $this->comment_times($zpid);
            $tmp['fenxiang_times'] = $this->get_fenxiang_times($zpid);
            $tmp['edit_times'] = $this->get_edit_times($zpid);
            /*$tmp['remen_times'] = $tmp['liked_times']*5 + $tmp['edit_times']*5+$tmp['fenxiang_times']*2;*/
           /* $tmp['nickname'] = $zuopin['user_name'];
            $tmp['touxiang'] = $zuopin['user_touxiang'];*/
            if (intval($zuopin['quxiao_remen']) == 2) {
                $tmp['isRecommend'] = true;
            }else{
                $tmp['isRecommend'] = false;
            }
            $tmp['am_i_zan']=$this->is_zan($zpid,$myid);

            $tmp['zp_creat_time']=time();
            $new[]=$tmp;

        }
        return $new;
    }

    public function zhengli_remen_by_redu_redis($zpids,$myid){
        if (empty($zpids)) {
            return array();
        }
        $new=array();
        foreach ($zpids as $key => $zpid) {
            $zuopin=$this->remen_zp_by_zpid($zpid);
            if ($zuopin['userid'] !== $myid && $zuopin['jubao_times'] >= 5) {
                continue;
            }
            if (empty($zuopin['zuopin_id'])) {
                continue;
            }


            $tmp=array();
            $tmp['zuopin_id'] = $zuopin['zuopin_id'];
            $tmp['zuopin_url'] = $zuopin['zuopin_url'];
            $tmp['tagid'] = $zuopin['tagid'];
            $tmp['userid'] = $zuopin['userid'];
            if ($tmp['userid'] == '6') {
                $tmp['type'] = 1;
                $tmp['webviewurl'] = $this->add_ads($tmp['zuopin_id']);
            }
            $tmp['liked_times'] = $this->liked_times($zuopin['zuopin_id']);
            $tmp['comment_times'] = $this->comment_times($zuopin['zuopin_id']);
            $tmp['fenxiang_times'] = $this->get_fenxiang_times($zuopin['zuopin_id']);
            $tmp['edit_times'] = $this->get_edit_times($zuopin['zuopin_id']);
            $tmp['remen_times'] = $tmp['liked_times']*5 + $tmp['edit_times']*5+$tmp['fenxiang_times']*2;
            $tmp['nickname'] = $zuopin['user_name'];
            $tmp['touxiang'] = $zuopin['user_touxiang'];
            if(intval($zuopin['quxiao_remen']) == 2) {
                $tmp['isRecommend'] = true;
            }else{
                $tmp['isRecommend'] = false;
            }

            $tmp['am_i_zan']=$this->is_zan($zuopin['zuopin_id'],$myid);

            if (empty($zuopin['zp_creat_time'])) {
                $tmp['zp_creat_time']=time();
            }else{
                $tmp['zp_creat_time']=(int)$zuopin['zp_creat_time'];
            }
            $new[]=$tmp;

            //重置屏蔽
            if (intval($zuopin['pingbi'] == 1)) {
                //userszp_remen
                $redis = new Redis();
                $redis->connect('127.0.0.1',6379);
                $redis->zAdd('userszp_remen::'.$zuopin['userid'],$tmp['remen_times'],$zuopin['zuopin_id']);
                //remen_zps
                $redis = new Redis();
                $redis->connect('127.0.0.1',6379);
                $redis->zAdd('remen_zps',$tmp['remen_times'],$zuopin['zuopin_id']);
            }

        }
        return $new;
    }

    public function get_pid($userid){
        //0
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $pid=$redis->hGet('pid', $userid);
        if(!empty($pid)){
            return $pid;
        }
        //1
        $rd=rand(100,999).rand(100,999).$userid;
        var_dump($rd);
        $md5id=md5($rd);
        $redis->hSet('pid', $userid,$md5id);
        return $md5id;
    }

	 public function user_getbyzpid($zpid){
        $U_=$this->load('users');
        $userinfo = $U_->user_getbyzpid($zpid);
        return $this->zhengli_userinfo($userinfo);

    }

 public    function zan_tuisong($userid,$zuopinid){

        $user=$this->user_getbyid($userid);
		$my=$this->user_getbyzpid($zuopinid);
		//print_r($zuopinid);
		if ($userid == intval($my['userid'])) {
            return ;
        }
        //安卓判断
        if($user['platform'] == 2){
            $hi='hi '.$my['user_name'].' , '.$user['nickname'].' 点赞了您的作品';
            exec('/usr/local/php/bin/php /usr/local/nginx/html/jpush/jp.php '.$my['user_id'].' "'.$hi.'"');
            return;
        }


    }






    //生成最新作品第一页缓存
   public function shownewredis(){
       $zuopins=$this->show_new(0);
       $redis = new Redis();
       $redis->connect('127.0.0.1',6379);
       $redis->set('shownewredis',json_encode($zuopins));
   }


}
