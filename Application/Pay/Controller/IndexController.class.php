<?php
namespace Pay\Controller;

class IndexController extends PayController
{
    public $channel;
    public function __construct()
    {
        parent::__construct();
        if(empty($_POST)){
            $this->showmessage('no data!');
        }
        // 判断来源域名
        //$this->domaincheck(I('request.pay_memberid')); // 判断来源域名

        $memberid = I("request.pay_memberid",0,'intval') - 10000;
        // 商户编号不能为空
        if (empty($memberid) || $memberid<=0) {
            $this->showmessage("不存在的商户编号!");
        }
        //银行编码
        $bankcode = I('request.pay_bankcode',0,'intval');
        if(!$bankcode){
            $this->showmessage('不存在的银行编码!',['pay_banckcode'=>$bankcode]);
        }
        $this->channel = M('ProductUser')->where(['pid'=>$bankcode,'userid'=>$memberid,'status'=>1])->find();
        //用户未分配
        if(!$this->channel){
            $this->showmessage('暂时无法连接支付服务器!');
        }
       
        
    }

    public function index()
    {
        //进入支付
        if($this->channel['userid']){
            //是否存在通道文件
            if(!is_file(APP_PATH.'/'.MODULE_NAME.'/Controller/UsdtController.class.php')){
                $this->showmessage('支付通道不存在',['pay_bankcode'=>$this->channel['api']]);
            }
            if(R('Usdt/Pay',[$this->channel])===FALSE){
                $this->showmessage('服务器开小差了...');
            }
        }else{
            $this->showmessage("抱歉......服务器飞去月球了");
        }
    }
}