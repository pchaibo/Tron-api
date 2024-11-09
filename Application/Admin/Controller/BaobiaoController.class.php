<?php
/* pchaibo
 * 报表
 */
namespace Admin\Controller;
use Think\Page;
class BaobiaoController extends BaseController{
    
    public function index(){
        
    }
    
    //用户列表
    public function userlist(){
        $where['groupid'] = I('get.groupid') ? I('get.groupid'): ['neq',1];
        $username = I("get.username");
        $status = I("get.status");
        $authorized = I("get.authorized");
        $parentid = I('get.parentid');
        $regdatetime = I('get.regdatetime');
        if (!empty($username) && !is_numeric($username)) {
            $where['username'] = ['like',"%".$username."%"];
        }elseif(intval($username) - 10000>0){
            $where['id'] = intval($username) - 10000;
        }
        if(!empty($status)){
            $where['status'] = $status;
        }
        if(!empty($authorized)){
            $where['authorized'] = $authorized;
        }

        if(!empty($parentid) && !is_numeric($parentid)){
            $User = M("Member");
            $pid = $User->where("username = '" . $parentid . "'")->getField("id");
            $where['parentid'] = $pid;
        }elseif($parentid){
            $where['parentid'] = $parentid;
        }
        if($regdatetime){
            list($starttime,$endtime) = explode('|',$regdatetime);
            $where['regdatetime'] = ["between", [strtotime($starttime),strtotime($endtime)]];
        }
        $count = M('Member')->where($where)->count();
        $page = new Page($count,15);
        $list = M('Member')
            ->where($where)
            ->limit($page->firstRow.','.$page->listRows)
            ->order('id desc')
            ->select();
        //处理
        $data = $this->userdata($list);
       // echo "<pre/>";
       // print_r($data);
        $this->assign("list", $data);
        $this->assign('page',$page->show());
        $this->display();
    }
    
    public function userdata($list){
        if(!is_array($list)){exit;}
        $row =array();
        foreach ($list as $k=>$v){
            $row[$k] = $v;
            $row[$k]['tkmoeny'] = sprintf("%.2f",$this->tklist($v['id']));
            $row[$k]['jyamount'] = sprintf("%.2f",$this->userorder($v['id']));
            $row[$k]['lessmoney'] = sprintf("%.2f",$this->lessmoney($v['id'])) ;
            $row[$k]['yue'] =  sprintf("%.2f",$row[$k]['jyamount'] - $row[$k]['tkmoeny'] - $row[$k]['lessmoney']);
            
        }
        return $row;
    }
    //扣钱
    public function lessmoney($userid){
        $where['userid'] = $userid;
        $where['lx'] = 4;//取扣钱
        $res = M('moneychange')->where($where)->SUM('money');
        return $res;
    }
    
    //用户总交易
    public function userorder($userid){
        $userid =intval($userid);
        $where['pay_memberid'] = $userid + 10000;
        $where['pay_status'] = array('egt',1);
        $res = M('order')->where($where)->SUM('pay_actualamount');
        return  $res;
    }
    
    //用户提现
    public function tklist($userid){
        if(!$userid){
            exit;
        }
        $where = array();
        $where['userid'] = $userid;
        $where['status'] = 2;
        $res = M('tklist')->where($where)->SUM('tkmoney');
        return $res;
        
    }
    
  
    
}