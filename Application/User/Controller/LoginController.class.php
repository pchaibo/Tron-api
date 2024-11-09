<?php
/**
 * Created by PhpStorm.
 * User: gaoxi
 * Date: 2017-08-22
 * Time: 14:34
 */
namespace User\Controller;
use Think\Verify;
/**
 * 用户登录控制器
 * Class LoginController
 * @package Home\Controller
 */
class LoginController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 登录
     */
    public function index()
    {
        $this->display();
    }

    /**
     * 登录验证
     */
    public function checklogin()
    {
        if(IS_POST) {
            $username = I("post.username", "", 'trim');
            $password = I("post.password");
            $varification = I("post.varification");
            $cookiename = I("post.cookiename");
            if (!$username || !$password || !$varification) {
                $this->error( '用户名、密码输入有误！');
            }
            //验证码
            $verify = new Verify();
            if (!$verify->check($varification)) {
                $this->error( '验证码输入有误！');
            }
            $fans = M('Member')->where(['username'=>$username])->find();
            //不存在
            if(!$fans || $fans['status'] != 1){
                $this->error("您的账号正在审核中，请耐心等待！");
            }
            //密码验证
            if(md5($password.$fans['salt']) != $fans['password']){
                $this->error('密码输入有误！');
            }

            //用户登录
            $user_auth = [
                'uid'=>$fans['id'],
                'username'=>$fans['username'],
                'groupid'=>$fans['groupid'],
                'password'=>$fans['password']
            ];
            session('user_auth',$user_auth);
            ksort($user_auth); //排序
            $code = http_build_query($user_auth); //url编码并生成query字符串
            $sign = sha1($code);
            session('user_auth_sign',$sign);

            // 登录记录
            $rows['userid'] = $fans['id'];
            $rows['logindatetime'] = date("Y-m-d H:i:s");
            $Ip = new \Org\Net\IpLocation('UTFWry.dat'); // 实例化类 参数表示IP地址库文件
            $location = $Ip->getlocation(); // 获取某个IP地址所在的位置
            $rows['loginip'] = $location['ip'];
            $rows['loginaddress'] = $location['country'] . "-" . $location['area'];
            M("Loginrecord")->add($rows);
            $this->success('登录成功',U('Index/index'));
        }
    }

    /**
     * 登出
     */
    public function loginout()
    {
        session('user_auth',null);
        session('user_auth_sign',null);
        $this->success('正在退出...',U('Login/index'));
    }

    /**
     * 注册
     */
    public function register()
    {
        $this->display();
    }

    /**
     * 注册表单
     */
    public function checkRegister()
    {
        if(IS_POST){
            $username = I('post.username');
            $password = I('post.password');
            $confirmpassword = I('post.confirmpassword');
            $email = I('post.email');
            $invitecode = I('post.invitecode','','trim');
            
            //手机认证
            $mobile = I('post.mobile');
         /*    if(!$mobile){
                $this->ajaxReturn(['errono'=>10011,'msg'=>'手机号码不能为!']);
            }
            $code = trim(I('post.code'));
            $shortmessage = M('shortmessage')->where(['mobile'=>$mobile])->order('id desc')->find();
            if(!$shortmessage and $code){
                $this->ajaxReturn(['errono'=>10012,'msg'=>'手机认证不能为!']);
            }
            
            if($shortmessage['message'] !=$code){
                $this->ajaxReturn(['errono'=>10012,'msg'=>'手机认证不一致!']);
            } */
            
            
            if($password != $confirmpassword){
                $this->ajaxReturn(['errono'=>10002,'msg'=>'密码输入不一致!']);
            }

            //邀请码验证
            if($this->siteconfig['invitecode']){
                $_verfycode = M('Invitecode')
                    ->where(['invitecode'=>$invitecode,'status'=>1,'yxdatetime'=>array('egt', time())])
                    ->find();
                if(!$_verfycode){
                    $this->ajaxReturn(array('errorno'=>10001,'msg'=>'邀请码无效!'));
                }
            }
			$isuserid = M("Member")->where(['username'=>$username])->getField("id");
			if($isuserid){
				$this->ajaxReturn(array('errorno'=>10005,'msg'=>'用户名重复!'));
			}
            //激活码
            $activatecode = md5(md5($username) . md5($password) . md5($email).C('DATA_AUTH_KEY'));

            //是否需要认证
            $authorized = $this->siteconfig['authorized'] ? 0 : 1;

            $salt = rand(1000,9999);
            //写入
            $userdata = array(
                'username'=>$username,
                'password'=>md5($password.$salt),
                'paypassword'=>md5('123456'),
                'parentid'=>$_verfycode['fmusernameid'] ? $_verfycode['fmusernameid'] : 1 ,
                'email'=>$email,
                'groupid'=> $_verfycode['regtype'] ? $_verfycode['regtype'] :4,
                'regdatetime'=>time(),
                'activate'=>$activatecode,
                'authorized'=>$authorized,
                'apikey'=>random_str(),
                'salt'=>$salt,
                'mobile'=>$mobile,
                'status' =>1,
            );
            $newuid = M('Member')->add($userdata);
            //添加用户组权限
            M('AuthGroupAccess')->add(['uid'=>$newuid,'group_id'=>$_verfycode['regtype'] ? $_verfycode['regtype'] :4]);

            //失效邀请码
            $_failinvitecode = array('syusernameid' => $newuid, 'sydatetime' => time(), 'status' => 2);
            M('Invitecode')->where("invitecode = '" . $invitecode . "'")->save($_failinvitecode);
            //发送注册激活邮件
            //$returnEmail = sendRegemail($username, $email, $activatecode,$this->siteconfig);
            
            if($newuid){
                $tel = $this->siteconfig["tel"];
                $qqlist = $this->siteconfig['qq'];
                $mail = explode('@',$email)[1];
                $this->ajaxReturn(array('errorno' => 0, 'msg' => array('tel' => $tel, 'qq' => $qqlist, 'email' => $email,'mail'=>'http://mail.'.$mail)));
            }else{
                $this->ajaxReturn(['errorno'=>10003,'msg'=>$returnEmail]);
            }
        }else{
            $this->ajaxReturn(array('errorno'=>10004,'msg'=>'注册失败'));
        }
    }

    /**
     * 用户名验证
     */
    public function checkuser()
    {
        $username = I("post.username");
        $userid = M("Member")->where(['username'=>$username])->getField("id");
        $valid = true;
        if ($userid) {
            $valid = false;
            echo json_encode(array('valid' => $valid));
        } else {
            echo json_encode(array('valid' => $valid));
        }
    }

    /**
     * email 验证
     */
    public function checkemail()
    {
        $email = I("post.email");
        $userid = M("Member")->where("email='" . $email . "'")->getField("id");
        $valid = true;
        if ($userid) {
            $valid = false;
            echo json_encode(array('valid' => $valid));
        } else {
            echo json_encode(array('valid' => $valid));
        }
    }
    /**
     * mobile 手机
     */
    public function checkmobile()
    {
        $mobile = I("post.mobile");
        $userid = M("Member")->where("mobile='" . $mobile . "'")->getField("id");
        $valid = true;
        if ($userid) {
            $valid = false;
            echo json_encode(array('valid' => $valid));
        } else {
            echo json_encode(array('valid' => $valid));
        }
    }

    /**
     * 邀请码验证
     */
    public function checkinvitecode()
    {
        $invite_code = I("post.invitecode");
        $Invitecode = M("Invitecode");
        $where['invitecode'] = $invite_code;
        $where['status'] = 1;
        $where['yxdatetime'] = array('egt', time());
        $id = $Invitecode->where($where)->getField("id");
        $valid = true;
        if ($id) {
            echo json_encode(array('valid' => $valid));
        } else {
            $valid = false;
            echo json_encode(array('valid' => $valid));
        }
    }

    /**
     * 验证码
     */
    public function verifycode()
    {
        $config = array(
            'length' => 4, // 验证码位数
            'useNoise' => false, // 关闭验证码杂点
            'useImgBg' => false, // 使用背景图片
            'useZh' => false, // 使用中文验证码
            'useCurve' => false, // 是否画混淆曲线
            'useNoise' => false,// 是否添加杂点
        );
        ob_clean();
        $verify = new Verify($config);
        $verify->entry();
    }

    /**
     * 验证码验证
     */
    public function checkverify()
    {
        $code = I("request.code", "");
        $verify = new Verify();
        if ($verify->check($code)) {
            exit("ok");
        } else {
            exit("no");
        }
    }
}