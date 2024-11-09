<?php
/**
 * Created by PhpStorm.
 * User: gaoxi
 * Date: 2017-08-22
 * Time: 14:34
 */
namespace User\Controller;

use Think\Page;

/**
 * 订单管理控制器
 * Class OrderController
 * @package User\Controller
 */
class OrderController extends UserController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign("Public", MODULE_NAME); // 模块名称
    }

    public function index()
    {

        //通道
        $products = M('ProductUser')
            ->join('LEFT JOIN __PRODUCT__ ON __PRODUCT__.id = __PRODUCT_USER__.pid')
            ->where(['pay_product_user.status'=>1,'pay_product_user.userid'=>$this->fans['uid']])
            ->field('pay_product.name,pay_product.id,pay_product.code')
            ->select();
        $this->assign("banklist", $products);

        $where = array();
        $orderid = I("request.orderid");
        if ($orderid) {
            $where['out_trade_id'] = $orderid;
        }
        $ddlx = I("request.ddlx","");
        if($ddlx != ""){
            $where['ddlx'] = array('eq',$ddlx);
        }
        $tongdao = I("request.tongdao");
        if ($tongdao) {
            $where['pay_tongdao'] = array('eq',$tongdao);
        }
        $status = I("request.status",0,'intval');
        if ($status) {
            $where['pay_status'] = array('eq',$status);
        }
        $createtime = urldecode(I("request.createtime"));
        if ($createtime) {
            list($cstime,$cetime) = explode('|',$createtime);
            $where['pay_applydate'] = ['between',[strtotime($cstime),strtotime($cetime)?strtotime($cetime):time()]];
        }
        $successtime = urldecode(I("request.successtime"));
        if ($successtime) {
            list($sstime,$setime) = explode('|',$successtime);
            $where['pay_successdate'] = ['between',[strtotime($sstime),strtotime($setime)?strtotime($setime):time()]];
        }
        $where['isdel'] = 0;
        $where['pay_memberid'] = $this->fans['memberid'];
        $count = M('Order')->where($where)->count();
        $page = new Page($count,15);
        $list = M('Order')
            ->where($where)
            ->limit($page->firstRow.','.$page->listRows)
            ->order('id desc')
            ->select();

        $this->assign("list", $list);
        $this->assign('page',$page->show());
        C('TOKEN_ON',false);
        $this->display();
    }

    /**
     * 查看订单
     */
    public function show()
    {
        $id = I("get.oid",0,'intval');
        if($id){
            $order = M('Order')
                ->where(['id'=>$id])
                ->find();
        }
        $this->assign('order',$order);
        $this->display();
    }

    /**
     *  伪删除订单
     */
    public function delOrder()
    {
        if(IS_POST){
            $id = I('post.id',0,'intval');
            if($id){
                $res = M('Order')->where(['id'=>$id,'pay_memberid'=>$this->fans['memberid']])->setField('isdel',1);
            }
            $this->ajaxReturn(['status'=>$res]);
        }
    }
    
    /**
     * 导出交易订单
     * */
    public function exportorder()
    {   
        //print_r($this->fans['memberid']);
        if(!$this->fans['uid']){
            exit("请登录!");
        }
        $memberid = $this->fans['memberid'];
        if ($memberid) {
            $where['pay_memberid'] = array('eq',$memberid);
        }
        $orderid = I("request.orderid");
        if ($orderid) {
            $where['out_trade_id'] = $orderid;
        }
        $ddlx = I("request.ddlx","");
        if($ddlx != ""){
            $where['ddlx'] = array('eq',$ddlx);
        }
        $tongdao = I("request.tongdao");
        if ($tongdao) {
            $where['pay_tongdao'] = array('eq',$tongdao);
        }
        $bank = I("request.bank",'','strip_tags');
        if ($bank) {
            $where['pay_bankname'] = array('eq',$bank);
        }
    
        $status = I("request.status",0,'intval');
        if ($status) {
            $where['pay_status'] = array('eq',$status);
        }
        $createtime = urldecode(I("request.createtime"));
        if ($createtime) {
            list($cstime,$cetime) = explode('|',$createtime);
            $where['pay_applydate'] = ['between',[strtotime($cstime),strtotime($cetime)?strtotime($cetime):time()]];
        }
        $successtime = urldecode(I("request.successtime"));
        if ($successtime) {
            list($sstime,$setime) = explode('|',$successtime);
            $where['pay_successdate'] = ['between',[strtotime($sstime),strtotime($setime)?strtotime($setime):time()]];
        }
    
        $title = array('订单号','流水号','商户编号','交易金额','手续费','实际金额','提交时间','成功时间','通道','状态');
        $data = M('Order')->where($where)->select();
        foreach ($data as $item){
    
            switch ($item['pay_status']){
                case 0:
                    $status = '未处理';
                    break;
                case 1:
                    $status = '成功，未返回';
                    break;
                case 2:
                    $status = '成功，已返回';
                    break;
            }
             $list[] = array(
                'out_trade_id '=>"\t".$item['out_trade_id'],
                'pay_orderid'=>"\t".$item['pay_orderid'],
                'pay_memberid'=>$item['pay_memberid'],
                'pay_amount'=>$item['pay_amount'],
                'pay_poundage'=>$item['pay_poundage'],
                'pay_actualamount'=>$item['pay_actualamount'],
                'pay_applydate'=>date('Y-m-d H:i:s',$item['pay_applydate']),
                'pay_successdate'=>$item['pay_successdate'] ? date('Y-m-d H:i:s',$item['pay_successdate']) :" ",
                'pay_zh_tongdao'=>$item['pay_bankname'],
                'pay_status'=>$status,
            );
        }
        exportCsv($list,$title);
       
    }
    
}
?>
