<?php
/**
 * Created by PhpStorm.
 * User: gaoxi
 * Date: 2017-08-22
 * Time: 14:34
 */
namespace User\Controller;

/**
 * 用户中心首页控制器
 * Class IndexController
 * @package User\Controller
 */

class IndexController extends UserController
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 首页
     */
    public function index()
    {
        $this->display();
    }

    public function main()
    {
        $firstday = date('Y-m-01', time());
        $lastday = date('Y-m-d', strtotime("$firstday +1 month -1 day"));

        //成交金额
        $sql = "SELECT SUM( pay_actualamount ) AS total, FROM_UNIXTIME( pay_successdate,  '%Y-%m-%d' ) AS DATETIME
FROM pay_order WHERE pay_successdate >= UNIX_TIMESTAMP(  '".$firstday."' ) AND pay_successdate < UNIX_TIMESTAMP(  '".
            $lastday."' ) AND pay_status>=1 AND pay_memberid=".($this->fans['memberid'])."  GROUP BY DATETIME";
        $ordertotal = M('Order')->query($sql);

        //成交订单数
        $sql = "SELECT COUNT( id ) AS num, FROM_UNIXTIME( pay_successdate,  '%Y-%m-%d' ) AS DATETIME
FROM pay_order WHERE pay_successdate >= UNIX_TIMESTAMP(  '".$firstday."' ) AND pay_successdate < UNIX_TIMESTAMP(  '".
            $lastday."' ) AND pay_status>=1 AND pay_memberid=".($this->fans['memberid'])."  GROUP BY DATETIME";
        $ordernum = M('Order')->query($sql);
        foreach ($ordernum as $key=>$item){
            $category[] = date('Ymd',strtotime($item['datetime']));
            $dataone[] = $item['num'];
            $datatwo[] = $ordertotal[$key]['total'];
        }
        $this->assign('category','['.implode(',',$category).']');
        $this->assign('dataone','['.implode(',',$dataone).']');
        $this->assign('datatwo','['.implode(',',$datatwo).']');
        
        //实时统计
        $where = array();
        $where['pay_status'] = array('egt',1);
        $where['pay_successdate'] =array('between',array(strtotime('today'),strtotime('tomorrow')));
        $where['pay_memberid'] = $this->fans['memberid'];
        $ddata['num'] = M('Order')->where($where)->count();
        $ddata['amount'] = M('Order')->where($where)->sum('pay_amount');
        $ddata['rate'] = M('Order')->where($where)->sum('pay_poundage');
        $ddata['total'] = M('Order')->where($where)->sum('pay_actualamount');
        $this->assign('ddata',$ddata);
        //文章默认最新2条
        $Article = M("Article");
        $gglist = $Article->where(['status'=> 1])->limit(2)->order("id desc")->select();
        $this->assign("gglist", $gglist);

        $this->display();
    }

    public function showcontent()
    {
        $id = I("get.id");
        $Article = M("Article");
        $find = $Article->where("id=" . $id)->find();
        $this->assign("find", $find);
        $this->display();
    }

    public function gonggao()
    {
        $list = M('Article')->where(['catid'=>4 ,'status'=> 1])->select();
        $this->assign("list", $list);
        $this->display();
    }
}