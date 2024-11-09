<?php
/**
 * Created by PhpStorm.
 * User: gaoxi
 * Date: 2017-08-22
 * Time: 14:34
 */
namespace Home\Controller;

/**
 * 网站入口控制器
 * Class IndexController
 * @package Home\Controller
 * @author 22691513@qq.com
 */
class IndexController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $res = M('websiteconfig')->find();
        $this->assign('website',$res);
    }

    public function index()
    {   
       
        $this->display('login');
    }
    
    //短信发送
    public function shortsms(){
        $mobile = $_REQUEST['mobile'];
        //手机号码
      if(!preg_match("/^1[34578]\d{9}$/", $mobile)){
         exit("mobile not null");
      }
      $where = array();
      $where['mobile'] = $mobile;
      $where['add_time'] = array('Gt',time() - 60*2);//小于两分钟
      
      $res = M('shortmessage')->where($where)->find();
      if($res){
        exit();  
      }
      
       $data =array();
       $data['mobile'] = $mobile;
       $data['message'] = rand(111111,999999);//randpw($len=8,$format='NUMBER');
       $data['add_time'] = time();
       $row = M('shortmessage')->add($data);
       $url = $_SERVER['SERVER_NAME'].'/ali/sms.php';
       //发送信息
      $res = sendForm($url,$data,$referer="");
     echo $res;
    }
    
}