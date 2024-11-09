<?php
namespace User\Controller;

/**
 * 支付通道控制器
 * Class ChannelController
 * @package User\Controller
 */
class ChannelController extends UserController
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 通道费率
     */
    public function index()
    {
        //已开通通道
        $list = M('ProductUser')
            ->join('LEFT JOIN __PRODUCT__ ON __PRODUCT__.id = __PRODUCT_USER__.pid')
            ->where(['pay_product_user.userid'=>$this->fans['uid'],'pay_product_user.status'=>1,'pay_product.isdisplay'=>1])
            ->field('pay_product.name,pay_product.id,pay_product_user.status')
            ->select();

        foreach ($list as $key=>$item){
            $feilv = M('Userrate')->where(['userid'=>$this->fans['uid'],'payapiid'=>$item['id']])->getField('feilv');
            $list[$key]['feilv'] = $feilv;
        }

        //结算方式：
        $tkconfig = M('Tikuanconfig')->where(['userid'=>$this->fans['uid']])->find();
        if(!$tkconfig || $tkconfig['tkzt']!=1){
            $tkconfig = M('Tikuanconfig')->where(['issystem'=>1])->find();
        }

        $this->assign('tkconfig',$tkconfig);
        $this->assign('list',$list);
        $this->display();
    }

    /**
     * 开发文档
     */
    public function apidocumnet()
    {
        $info = M('Member')->where(['id'=>$this->fans['uid']])->find();
        $website = M('websiteconfig')->find();
        $this->assign('website',$website);
        $this->assign('info',$info);
        $this->display();
    }

}