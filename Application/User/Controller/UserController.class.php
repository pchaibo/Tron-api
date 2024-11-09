<?php
namespace User\Controller;

class UserController extends BaseController
{
    public $fans;
    public function __construct()
    {
        parent::__construct();
        //验证登录
        $user_auth = session("user_auth");
        ksort($user_auth); //排序
        $code = http_build_query($user_auth); //url编码并生成query字符串
        $sign = sha1($code);
        if($sign != session('user_auth_sign') || !$user_auth['uid']){
            $this->error('未登录',U('Login/index'));
        }
        //用户信息
        $this->fans = M('Member')->where(['id'=>$user_auth['uid']])->field('`id` as uid, `username`, `password`, `groupid`, `salt`,`balance`, `blockedbalance`, `email`, `realname`, `authorized`, `apidomain`, `apikey`, `status`')->find();
        $this->fans['memberid'] = $user_auth['uid']+10000;
        $this->assign('fans',$this->fans);
    }
}
?>
