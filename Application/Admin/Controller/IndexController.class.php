<?php
namespace Admin\Controller;

use Think\Page;

class IndexController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    //首页
    public function index()
    {
        $this->display();
    }

    //main
    public function main()
    {
        //日报
        $_data['today'] = date('Y年m月d日');
        $_data['month'] = date('Y年m月');

        $beginThismonth=mktime(0,0,0,date('m'),1,date('Y'));
        $endThismonth=mktime(23,59,59,date('m'),date('t'),date('Y'));

        //实时统计
        $ddata['num'] = M('Order')->where(array('pay_status'=>array('egt',2),'pay_successdate	'=>array('between',array(strtotime('today'),strtotime('tomorrow')))))->count();
        $ddata['amount'] = M('Order')->where(array('pay_status'=>array('egt',2),'pay_successdate	'=>array('between',array(strtotime('today'),strtotime('tomorrow')))))->sum('pay_amount');
        $ddata['rate'] = M('Order')->where(array('pay_status'=>array('egt',2),'pay_successdate	'=>array('between',array(strtotime('today'),strtotime('tomorrow')))))->sum('pay_poundage');
        $ddata['total'] = M('Order')->where(array('pay_status'=>array('egt',2),'pay_successdate	'=>array('between',array(strtotime('today'),strtotime('tomorrow')))))->sum('pay_actualamount');

        //本月统计
        //$_data['curmonth']['num'] = M('Order')->where(array('pay_status'=>2,'pay_successdate	'=>array('between',array($beginThismonth,$endThismonth))))->count();
        //$_data['curmonth']['total'] = M('Order')->where(array('pay_status'=>2,'pay_successdate	'=>array('between',array($beginThismonth,$endThismonth))))->sum('pay_amount');
       //$_data['curmonth']['rate'] = M('Order')->where(array('pay_status'=>2,'pay_successdate	'=>array('between',array($beginThismonth,$endThismonth))))->sum('pay_poundage');

        //7天统计
        $lastweek = time()-7*86400;
        $sql = "select COUNT(id) as num,SUM(pay_amount) AS amount,SUM(pay_poundage) AS rate,SUM(pay_actualamount) AS total from pay_order where  1=1 and pay_status>=1 and DATE_SUB(CURDATE(), INTERVAL 7 DAY) <= date(FROM_UNIXTIME(pay_successdate,'%Y-%m-%d')) and pay_successdate>=$lastweek; ";
        $wdata = M('Order')->query($sql);

        //按月统计
        $lastyear = strtotime(date('Y-1-1'));
        $sql = "select FROM_UNIXTIME(pay_successdate,'%Y年-%m月') AS month,SUM(pay_amount) AS amount,SUM(pay_poundage) AS rate,SUM(pay_actualamount) AS total from pay_order where  1=1 and pay_status>=1 and pay_successdate>=$lastyear GROUP BY month;  ";
        $_mdata = M('Order')->query($sql);
        $mdata = [];
        foreach ($_mdata as $item)
        {
            $mdata['amount'][] = $item['amount'] ? $item['amount'] : 0;
            $mdata['mdate'][] = "'".$item['month']."'";
            $mdata['total'][] = $item['total'] ? $item['total'] : 0;
            $mdata['rate'][] = $item['rate'] ? $item['rate'] : 0;
        }
        //统计7天的数据
        $lastyear =  time()-5*86400;;
        $sql = "select FROM_UNIXTIME(pay_successdate,'%Y年-%m月-%d') AS month,count(id) as num,SUM(pay_amount) AS amount,SUM(pay_poundage) AS rate,SUM(pay_actualamount) AS total from pay_order where  1=1 and pay_status>=1 and pay_successdate>=$lastyear GROUP BY month;  ";
        $last_row = M('Order')->query($sql);
        $this->assign("last_row",$last_row);
        
        $this->assign('ddata',$ddata);
        $this->assign('wdata',$wdata[0]);
        $this->assign('mdata',$mdata);
        $this->display();
    }
    
    public function dd(){
         $firstday = date('Y-m-01', time());
        $lastday = date('Y-m-d', strtotime("$firstday +1 month -1 day"));

        //成交金额
        $sql = "SELECT SUM( pay_actualamount ) AS total, FROM_UNIXTIME( pay_successdate,  '%Y-%m-%d' ) AS DATETIME
FROM pay_order WHERE pay_successdate >= UNIX_TIMESTAMP(  '".$firstday."' ) AND pay_successdate < UNIX_TIMESTAMP(  '".
            $lastday."' ) AND pay_status>=1   GROUP BY DATETIME";
        $ordertotal = M('Order')->query($sql);

        //成交订单数
        $sql = "SELECT COUNT( id ) AS num, FROM_UNIXTIME( pay_successdate,  '%Y-%m-%d' ) AS DATETIME
FROM pay_order WHERE pay_successdate >= UNIX_TIMESTAMP(  '".$firstday."' ) AND pay_successdate < UNIX_TIMESTAMP(  '".
            $lastday."' ) AND pay_status>=1   GROUP BY DATETIME";
        $ordernum = M('Order')->query($sql);
        foreach ($ordernum as $key=>$item){
            $category[] = date('Ymd',strtotime($item['datetime']));
            $dataone[] = $item['num'];
            $datatwo[] = $ordertotal[$key]['total'];
        }
        echo "<pre/>";
        print_r($ordertotal);
    }

    /**
     * 清除缓存
     */
    public function clearCache()
    {
        $dir = RUNTIME_PATH;
        $this->delCache($dir);
        $this->success('缓存清除成功！');
    }

    /**
     * 删除缓存目录
     * @param $dirname
     * @return bool
     */
    protected function delCache($dirname)
    {
        $result = false;
        if(! is_dir($dirname)){
            echo " $dirname is not a dir!";
            exit(0);
        }
        $handle = opendir($dirname); //打开目录
        while(($file = readdir($handle)) !== false) {
            if($file != '.' && $file != '..'){ //排除"."和"."
                $dir = $dirname.DIRECTORY_SEPARATOR.$file;
                is_dir($dir) ? self::delCache($dir) : unlink($dir);
            }
        }
        closedir($handle);
        $result = rmdir($dirname) ? true : false;
        return $result;
    }

}