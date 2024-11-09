<?php
namespace Admin\Controller;

class SystemController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
    }

    //修改管理员密码
	public function editPassword()
    {
        if(IS_POST){
            $ypassword = trim(I("post.ypassword"));
            $newpassword = trim(I("post.newpassword"));
            $newpasswordok = I("post.newpasswordok");
            if(md5($ypassword.C('DATA_AUTH_KEY')) != session('admin_auth')['password']){
                $this->ajaxReturn(['status'=>0,'msg'=>'原密码错误！']);
            }
            if($newpassword != $newpasswordok){
                $this->ajaxReturn(['status'=>0,'msg'=>'两次输入密码不一致！']);
            }
            $userid = session('admin_auth');
            $res = M('Member')->where(['id'=>$userid['uid']])->save(['password'=>md5($newpassword.C('DATA_AUTH_KEY')
            )]);
            $this->ajaxReturn(['status'=>$res,'msg'=>'success']);
        }else{
            $this->display();
        }
    }

    //基本设置
    public function base()
    {
        $Websiteconfig = D("Websiteconfig");
        $list = $Websiteconfig->find();
        $this->assign("vo", $list);
        $this->display();
    }

    public function saveBase()
    {
        if(IS_POST){
            $id = I('post.id');
            $configs = I('post.config');
            $mconfig = M("Websiteconfig");
            if($id){
                $res = $mconfig->where(['id'=>$id])->save($configs);
            }else{
                $res = $mconfig->add($configs);
            }
            if (!$res) {
                $this->ajaxReturn(['status'=>0,'msg'=>"修改失败，请稍后重试！"]);
            }else{
                $websitename = $configs['websitename'];
                $domain = $configs['domain'];
                $directory = $configs['directory'] == "" ? "Admin" : $configs['directory'];
                $login = $configs['login'] == "" ? "Login" : $configs['login'];
                $str = "";

                $str = "<?php \n";
                $str .= "\t\treturn array(\n";
                $str .= "\t\t\t'WEB_TITLE' => '" . $websitename . "',\n";
                $str .= "\t\t\t'DOMAIN' => '" . $domain . "',\n";
                $str .= "\t\t\t'MODULE_ALLOW_LIST'   => array('Home','User','" . ucfirst($directory). "','Install', 'Weixin','Pay','Cashier'),\n";
                if ($directory != "Admin") {
                    $str .= "\t\t\t'URL_MODULE_MAP'  => array('" . strtolower($directory) . "'=>'admin'),\n";
                }
                $str .= "\t\t\t'LOGINNAME' => '" . $login . "',\n";
                $str .= "\t\t\t'HOUTAINAME' => '" . $directory . "',\n";
                $str .= "\t\t);\n";
                $str .= "?>";

                file_put_contents(CONF_PATH.'website.php',$str);
                $this->ajaxReturn(['status'=>1,'msg'=>"修改成功！"]);
            }
        }
    }

    public function email()
    { // 邮箱设置
        $Email = M("Email");
        $list = $Email->find();
        $this->assign("vo", $list);
        $this->display();
    }

    public function saveEmail()
    {
        if(IS_POST){
            $_formdata = array(
                'smtp_host'=>I('post.smtp_host'),
                'smtp_port'=>I('post.smtp_port'),
                'smtp_user'=>I('post.smtp_user'),
                'smtp_pass'=>I('post.smtp_pass'),
                'smtp_email'=>I('post.smtp_email'),
                'smtp_name'=>I('post.smtp_name'),
            );
            $id = I('post.id',0,'intval');
            $email = M("Email");
            if($id){
                $result = $email->where(['id'=>$id])->save($_formdata);
            }else{
                $result = $email->add($_formdata);
            }
            $this->ajaxReturn(['status'=>$result]);
        }

    }

    public function testEmail()
    {
        if(IS_POST){
            $cs_email = I('post.cs_text');
            if (!$cs_email) {
                $this->ajaxReturn(['status'=>0,'msg'=>"测试收件邮箱地址不能为空"]);
            } else {
                $result = sendEmail($cs_email, '测试邮件', '测试邮件');
                if ($result==1) {
                    $this->ajaxReturn(['status'=>1,'msg'=>"测试邮件发送成功，请注意查收！"]);
                } else {
                    $this->ajaxReturn(['status'=>0,'msg'=>"发送失败，错误信息：$result" ]);
                }
            }
        }
    }

    public function smssz()
    {
        $Sms = M("Sms");
        
        $list = $Sms->where("websiteid=0")->select();
        
        $this->assign("list", $list);
        
        $this->display();
    }

    public function smsszedit()
    {
        $Sms = M("Sms");
        
        $Sms->create();
        
        if ($Sms->save()) {
            exit("修改成功！");
        } else {
            exit("修改失败！");
        }
    }

    public function csfasms()
    {
        $cs_email = I('request.cs_text', '');
        if ($cs_email == '') {
            exit("测试接收手机号不能为空");
        } else {
            $ReturnEmail = PHPFetion($cs_email, "测试短信", 0);
            if ($ReturnEmail == 1) {
                exit("测试短信发送成功，请注意查收！");
            } else {
                exit("发送失败，错误信息：" . $ReturnEmail);
            }
            exit($ReturnEmail);
        }
    }
}
?>
