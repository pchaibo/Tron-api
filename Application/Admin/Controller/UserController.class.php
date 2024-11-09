<?php
/**
 * Created by PhpStorm.
 * User: gaoxi
 * Date: 2017-04-02
 * Time: 23:01
 */

namespace Admin\Controller;

use Think\Page;

/**
 * 用户管理控制
 * Class UserController
 * @package Admin\Controller
 */

class UserController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        //通道
        $channels = M('Channel')
            ->where(['status'=>1])
            ->field('id,code,title,paytype,status')
            ->select();
        $this->assign('channels',json_encode($channels));
        $this->assign('channellist',$channels);
    }

    /**
     * 用户列表
     */
    public function index()
    {
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
        
        //提现统计
        $countbalance =  M('Member')->where($where)->sum('balance');
        
        $pbalance = "0.00";
        foreach ($list as $v){
            $pbalance  = $pbalance + $v['balance'];
        }
        $this->assign('pbalance',$pbalance);
        $this->assign('countbalance',$countbalance);
        $this->assign("list", $list);
        $this->assign('page',$page->show());
        //取消令牌
        C('TOKEN_ON',false);
        $this->display();
    }

    public function invitecode()
    {
        $invitecode = I("get.invitecode");
        $fbusername = I("get.fbusername");
        $syusername = I("get.syusername");
        $regtype = I("get.groupid");
        $status = I("get.status");
        if (!empty($invitecode)) {
            $where['invitecode'] = ["like","%" . $invitecode . "%"];
        }
        if (!empty($fbusername)) {
            $fbusernameid = M("Member")->where("username = '" . $fbusername . "'")->getField("id");
            $where['fmusernameid'] = $fbusernameid;
        }
        if (!empty($syusername)) {
            $syusernameid = M("Member")->where("username = '" . $syusername . "'")->getField("id");
            $where['syusernameid'] = $syusernameid;
        }
        if (!empty($regtype)) {
            $where['regtype'] = $regtype;
        }
        $regdatetime = urldecode(I("request.regdatetime"));
        if ($regdatetime) {
            list($cstime,$cetime) = explode('|',$regdatetime);
            $where['fbdatetime'] = ['between',[strtotime($cstime),strtotime($cetime)?strtotime($cetime):time()]];
        }
        if (!empty($status)) {
            $where['status'] = $status;
        }
        $count = M('Invitecode')->where($where)->count();
        $page = new Page($count,10);
        $list = M('Invitecode')
            ->where($where)
            ->limit($page->firstRow.','.$page->listRows)
            ->order('id desc')
            ->select();
        $this->assign("list", $list);
        $this->assign('page',$page->show());
        //取消令牌
        C('TOKEN_ON',false);
        $this->display();
    }

    public function setInvite()
    {
        $data = M("Inviteconfig")->find();
        $this->assign('data',$data);
        $this->display();
    }

    /**
     * 保存邀请码设置
     */
    public function saveInviteConfig()
    {
        if(IS_POST){
            $Inviteconfig = M("Inviteconfig");
            $_formdata['invitezt'] =I('post.invitezt');
            $_formdata['invitetype2number'] = I('post.invitetype2number');
            $_formdata['invitetype2ff'] = I('post.invitetype2ff');
            $_formdata['invitetype5number'] = I('post.invitetype5number');
            $_formdata['invitetype5ff'] = I('post.invitetype5ff');
            $_formdata['invitetype6number'] = I('post.invitetype6number');
            $_formdata['invitetype6ff'] = I('post.invitetype6ff');
            $result = $Inviteconfig->where(array('id'=>I('post.id')))->save($_formdata);
            $this->ajaxReturn(['status'=>$result]);
        }
    }

    /**
     * 添加邀请码
     */
    public function addInvite()
    {
        $invitecode = $this->createInvitecode();
        $this->assign('invitecode',$invitecode);
        $this->assign('datetime',date('Y-m-d H:i:s',time()+86400));
        $this->display();
    }

    /**
     * 邀请码
     * @return string
     */
    private function createInvitecode()
    {
        $invitecodestr = random_str(30);
        $Invitecode = M("Invitecode");
        $id = $Invitecode->where("invitecode = '" . $invitecodestr . "'")->getField("id");
        if (! $id) {
            return $invitecodestr;
        } else {
            $this->createInvitecode();
        }
    }

    /**
     * 添加邀请码
     */
    public function addInvitecode()
    {
       if(IS_POST){
           $invitecode = I('post.invitecode');
           $yxdatetime = I('post.yxdatetime');
           $regtype = I('post.regtype');
           $Invitecode = M("Invitecode");
           $_formdata = array(
               'invitecode'=>$invitecode,
               'yxdatetime'=>strtotime($yxdatetime),
               'regtype'=>$regtype,
               'fmusernameid'=>1,
               'inviteconfigzt'=>1,
               'fbdatetime'=>time(),
           );
           $result = $Invitecode->add($_formdata);
           $this->ajaxReturn(['status'=>$result]);
       }
    }

    /**
     * 删除邀请码
     */
    public function delInvitecode()
    {
        if(IS_POST){
            $id = I('post.id',0,'intval');
            $res = M('Invitecode')->where(['id'=>$id])->delete();
            $this->ajaxReturn(['status'=>$res]);
        }
    }

    public function getRandstr()
    {
        echo random_str();
    }
    public function batchdel()
    {
        $ids = I("post.ids");
        $ids = trim($ids,',');
        $type = M("User")->where(array('id'=>array('in',$ids)))->delete();
        M('Money')->where(array('userid'=>array('in',$ids)))->delete();
        M('userbasicinfo')->where(array('userid'=>array('in',$ids)))->delete();
        M('userpassword')->where(array('userid'=>array('in',$ids)))->delete();
        M('userpayapi')->where(array('userid'=>array('in',$ids)))->delete();
        M('Userrate')->where(array('userid'=>array('in',$ids)))->delete();
        M('userverifyinfo')->where(array('userid'=>array('in',$ids)))->delete();
        if ($type) {
            exit("ok");
        } else {
            exit("no");
        }
    }

    /**
     * 删除用户
     */
    public function delUser()
    {
        if(IS_POST){
            $id = I('post.uid',0,'intval');
            $res = M('Member')->where(['id'=>$id])->delete();
            $this->ajaxReturn(['status'=>$res]);
        }
    }

    //导出用户
    public function exportuser()
    {
        $username = I("get.username");
        $status = I("get.status");
        $authorized = I("get.authorized");
        $parentid = I("get.parentid");
        $groupid = I("get.groupid");

        if(is_numeric($username)){
            $map['id'] = array('eq',intval($username) - 10000);
        }else{
            $map['username'] = array('like','%'.$username.'%');
        }
        if ($status) {
            $map['status'] = array('eq',$status);
        }
        if ($authorized) {
            $map['authorized'] = array("eq", $authorized);
        }
        if ($parentid) {
            if(is_numeric($parentid)){
                $sjuserid = M('Member')->where("id = " . ($parentid - 10000))->getField("id");
            }else{
                $sjuserid = M('Member')->where("username like '%" . $parentid . "%'")->getField("id");
            }
            $map['parentid'] = array('eq',$sjuserid);
        }
        $regdatetime = urldecode(I("request.regdatetime"));
        if ($regdatetime) {
            list($cstime,$cetime) = explode('|',$regdatetime);
            $map['regdatetime'] = ['between',[strtotime($cstime),strtotime($cetime)?strtotime($cetime):time()]];
        }

        $map['groupid'] = $groupid ? array('eq',$groupid) : array('neq',0);

        $title = array('用户名','商户号','用户类型','上级用户名','状态','认证','可用余额','冻结余额','注册时间');
        $data = M('Member')
            ->where($map)
            ->select();
        foreach ($data as $item){
            switch ($item['groupid'])
            {
                case 4:
                    $usertypestr = '商户';
                    break;
                case 5:
                    $usertypestr = '代理商';
                    break;
            }
            switch ($item['status'])
            {
                case 0:
                    $userstatus = '未激活';
                    break;
                case 1:
                    $userstatus = '正常';
                    break;
                case 2:
                    $userstatus = '已禁用';
                    break;
            }
            switch ($item['authorized'])
            {
                case 1:
                    $rzstauts = '已认证';
                    break;
                case 0:
                    $rzstauts = '未认证';
                    break;
                case 2:
                    $rzstauts = '等待审核';
                    break;
            }
            $list[] = array(
                'username'=>$item['username'],
                'userid'=>$item['id']+10000,
                'groupid'=>$usertypestr,
                'parentid'=>getParentName($item['parentid'],1),
                'status'=>$userstatus,
                'authorized'=>$rzstauts,
                'total'=>$item['balance'],
                'total'=>$item['blockedbalance'],
                'regdatetime'=>date('Y-m-d H:i:s',$item['regdatetime'])
            );
        }
        exportCsv($list,$title);
    }

    public function jbxx()
    {
        $userid = I("post.userid");
        $Userbasicinfo = M("Userbasicinfo");
        $list = $Userbasicinfo->where("userid=" . $userid)->find();
        $list['username'] = M('User')->where(array('id'=>$userid))->getField('username');
        $list['usertype'] = M('User')->where(array('id'=>$userid))->getField('usertype');
        $this->ajaxReturn($list, "json");
    }

    public function editjbxx()
    {
        if(IS_POST){
            $rows['fullname'] = I('post.fullname');
            $rows['sfznumber'] = I('post.sfznumber');
            $rows['birthday'] = I('post.birthday');
            $rows['phonenumber'] = I('post.phonenumber');
            $rows['qqnumber'] = I('post.sfznumber');
            $rows['address'] = I('post.address');
            $rows['sex'] = I('post.sex');
            $usertype = I('post.usertype');
            M('User')->where(array('id'=>I('post.userid')))->save(array('usertype'=>$usertype));
            $returnstr = M("Userbasicinfo")->where(array('id'=>I('post.id')))->save($rows);
            if ($returnstr == 1 || $returnstr == 0) {
                exit("ok");
            } else {
                exit("no");
            }
        }
    }

    public function zhuangtai()
    {
        $userid = I("post.userid");
        $User = M("User");
        $status = $User->where("id=" . $userid)->getField("status");
        exit($status);
    }

    public function xgzhuangtai()
    {
        $userid = I("post.userid");
        $status = I("post.status");
        $User = M("User");
        $data["status"] = $status;
        $returnstr = $User->where("id=" . $userid)->save($data);
        if ($returnstr == 1 || $returnstr == 0) {
            exit("ok");
        } else {
            exit("no");
        }
    }

    public function renzheng()
    {
        $userid = I("post.userid");
        $Userverifyinfo = M("Userverifyinfo");
        $list = $Userverifyinfo->where("userid=" . $userid)->find();
        $this->ajaxReturn($list, "json");
    }

    /**
     * 保存认证
     */
    public function editAuthoize()
    {
        if(IS_POST){
           $rows = I('post.u');
           $userid = $rows['userid'];
           unset($rows['userid']);
           $res = M('Member')->where(['id'=>$userid])->save($rows);
           $this->ajaxReturn(['status'=>$res]);
        }
    }

    public function renzhengeditdomain()
    {
        $userid = I("post.userid");
        $domain = I("post.domain");
        $Userverifyinfo = M("Userverifyinfo");
        $data["domain"] = $domain;
        $returnstr = $Userverifyinfo->where("userid=" . $userid)->save($data);
        if ($returnstr == 1 || $returnstr == 0) {
            exit("ok");
        } else {
            exit("no");
        }
    }

    public function renzhengeditmd5key()
    {
        $userid = I("post.userid");
        $md5key = I("post.md5key");
        $Userverifyinfo = M("Userverifyinfo");
        $data["md5key"] = $md5key;
        $returnstr = $Userverifyinfo->where("userid=" . $userid)->save($data);
        if ($returnstr == 1 || $returnstr == 0) {
            exit("ok");
        } else {
            exit("no");
        }
    }

    /**
     * 修改密码
     */
    public function editPassword()
    {
        if(IS_POST){
            $userid = I("post.userid");
            $salt = I("post.salt");
            $groupid = I('post.groupid');
            $u = I('post.u');
            if($u['password']){
                $data['password'] = md5($u['password'].($groupid<4 ? C('DATA_AUTH_KEY'):$salt));
            }
            if($u['paypassword']){
                $data['paypassword'] = md5($u['paypassword']);
            }
            $res = M('Member')->where("id=" . $userid)->save($data);
            $this->ajaxReturn(['status'=>$res]);
        }else{
            $userid = I('get.uid',0,'intval');
            if($userid){
                $data = M('Member')
                    ->where(['id'=>$userid])->find();
                $this->assign('u',$data);
            }

            $this->display();
        }
    }

    public function bankcard()
    {
        $userid = I("post.userid");
        $Bankcard = M("Bankcard");
        $list = $Bankcard->where("userid=" . $userid)->find();
        $this->ajaxReturn($list, "json");
    }

    public function editbankcard()
    {
        if (IS_POST){
            $id = I('post.id');
            $rows = [
                'bankname'=>I('post.bankname','','trim'),
                'bankzhiname'=>I('post.bankzhiname','','trim'),
                'banknumber'=>I('post.banknumber','','trim'),
                'bankfullname'=>I('post.bankfullname','','trim'),
                'sheng'=>I('post.sheng','','trim'),
                'shi'=>I('post.shi','','trim'),
            ];
            $returnstr = M("Bankcard")->where(['id'=>$id])->save($rows);
            if ($returnstr == 1 || $returnstr == 0) {
                exit("ok");
            } else {
                exit("no");
            }
        }
    }

    public function suoding()
    {
        $id = I("post.id");
        $disabled = I("post.disabled");
        $data["disabled"] = $disabled;
        if ($disabled == 0) {
            $data["jdatetime"] = date("Y-m-d H:i:s");
        }
        $Bankcard = M("Bankcard");
        $returnstr = $Bankcard->where("id=" . $id)->save($data);
        if ($returnstr == 1 || $returnstr == 0) {
            exit("ok");
        } else {
            exit("no");
        }
    }

    public function tongdao()
    {
        $userid = I("post.userid");
        $Userpayapi = M("Userpayapi");
        $list = $Userpayapi->where("userid=" . $userid)->find();
        if (! $list) {
            $Payapiconfig = M("Payapiconfig");
            $payapiid = $Payapiconfig->where("`default`=1")->getField("payapiid");
            $Payapi = M("Payapi");
            $en_payname = $Payapi->where("id=" . $payapiid)->getField("en_payname");
            $Userpayapi->userid = $userid;
            $Userpayapi->payapicontent = $en_payname . "|";
            $Userpayapi->add();
            $list = $Userpayapi->where("userid=" . $userid)->find();
        }
        $Payapiconfig = M("Payapiconfig");
        $payapiid = $Payapiconfig->where("`default`=1")->getField("payapiid");
        $Payapi = M("Payapi");
        $en_payname = $Payapi->where("id=" . $payapiid)->getField("en_payname");
        $list["disabled"] = $en_payname;
        
        $Payapiconfig = M("Payapiconfig");
        $payapiidstr = $Payapiconfig->field("payapiid")
            ->where("disabled=1")
            ->select(false);
        $Payapi = M("Payapi");
        $listlist = $Payapi->where("id in (" . $payapiidstr . ")")->select();
        $payapiaccountarray = array();
        foreach ($listlist as $key) {
            
            $Userpayapizhanghao = M("Userrate");
            $val = $Userpayapizhanghao->where("userid=" . $userid . " and payapiid=" . $key["id"])->getField("defaultpayapiuserid");
            if (! $val) {
                $Payapiaccount = M("Payapiaccount");
                $val = $Payapiaccount->where("payapiid=" . $key["id"] . " and defaultpayapiuser=1")->getField("id");
            }
            $payapiaccountarray[$key["en_payname"] . $key["id"]] = $val;
        }
        
        $obj = array(
            'list' => $list,
            'payapiaccountarray' => $payapiaccountarray
        );
        
        $this->ajaxReturn($obj, "json");
    }

    public function edittongdao()
    {
        $userid = I("post.userid");
        $selecttype = I("post.selecttype");
        $payname = I("post.payname");
        
        $Userpayapi = M("Userpayapi");
        $payapicontent = $Userpayapi->where("userid=" . $userid)->getField("payapicontent");
        if ($selecttype == 1) {
            $payapicontent = str_replace($payname . "|", "", $payapicontent);
        } else {
            $payapicontent = $payapicontent . $payname . "|";
        }
        $data["payapicontent"] = $payapicontent;
        $num = $Userpayapi->where("userid=" . $userid)->save($data);
        if ($num) {
            exit("ok");
        } else {
            exit("no");
        }
    }

    public function editdefaultpayapiuser()
    {
        $userid = I("post.userid");
        $payapiid = I("post.payapiid");
        $val = I("post.val");
        
        $Userpayapizhanghao = M("Userrate");
        $list = $Userpayapizhanghao->where("userid=" . $userid . " and payapiid=" . $payapiid)->select();
        if (! $list) {
            $data["userid"] = $userid;
            $data["payapiid"] = $payapiid;
            $data["defaultpayapiuserid"] = $val;
            $Userpayapizhanghao->add($data);
        } else {
            $data["defaultpayapiuserid"] = $val;
            $Userpayapizhanghao->where("userid=" . $userid . " and payapiid=" . $payapiid)->save($data);
        }
        exit("ok");
    }

    public function feilv()
    {
        $userid = I("post.userid");
        $Payapiconfig = M("Payapiconfig");
        $payapiidstr = $Payapiconfig->field("payapiid")
            ->where("disabled=1")
            ->select(false);
        $Payapi = M("Payapi");
        $listlist = $Payapi->where("id in (" . $payapiidstr . ")")->select();
        $payapiaccountarray = array();
        foreach ($listlist as $key) {
            
            $Userpayapizhanghao = M("Userrate");
            $val = $Userpayapizhanghao->where("userid=" . $userid . " and payapiid=" . $key["id"])->getField("feilv");
            if (! $val) {
                $Payapiaccount = M("Payapiaccount");
                $val = $Payapiaccount->where("payapiid=" . $key["id"] . " and defaultpayapiuser=1")->getField("defaultrate");
            }
            
            $val2 = $Userpayapizhanghao->where("userid=" . $userid . " and payapiid=" . $key["id"])->getField("fengding");
            if (! $val2) {
                $Payapiaccount = M("Payapiaccount");
                $val2 = $Payapiaccount->where("payapiid=" . $key["id"] . " and defaultpayapiuser=1")->getField("fengding");
            }
            
            $payapiaccountarray[$key["en_payname"] . $key["id"]] = $val . "|" . $val2;
        }
        
        $this->ajaxReturn($payapiaccountarray, "json");
    }

    public function editfeilv()
    {
        $userid = I("post.userid");
        $payapiid = I("post.payapiid");
        $val1 = I("post.feilvval", "") ? I("post.feilvval", "") : 0;
        $val2 = I("post.fengdingval", "") ? I("post.fengdingval", "") : 0;
        $Userpayapizhanghao = M("Userrate");
        $list = $Userpayapizhanghao->where("userid=" . $userid . " and payapiid=" . $payapiid)->select();
        if (! $list) {
            $data["userid"] = $userid;
            $data["payapiid"] = $payapiid;
            $data["feilv"] = $val1;
            $data["fengding"] = $val2;
            $Userpayapizhanghao->add($data);
        } else {
            $data["feilv"] = $val1;
            $data["fengding"] = $val2;
            $Userpayapizhanghao->where("userid=" . $userid . " and payapiid=" . $payapiid)->save($data);
        }
        exit("ok");
    }

    public function tksz()
    {
        $userid = I("post.userid");
        $User = M("User");
        $usertype = $User->where("id=" . $userid)->getField("usertype");
        $websiteid = $User->where("id=" . $userid)->getField("websiteid");
        $useriduserid = $userid;
        $Payapiconfig = M("Payapiconfig");
        $disabledpayapiid = $Payapiconfig->field('payapiid')->where("disabled=0")->select(false);
        $Payapi = M("Payapi");
        $tongdaolist = $Payapi->where("id not in (" . $disabledpayapiid . ")")->select();
        $datetype = array("b", "w", "j");
        $Tikuanmoney = M("Tikuanmoney");
        $array = array();
        foreach ($tongdaolist as $tongdao) {
            // file_put_contents("loguser.txt",$tongdao["id"]."----", FILE_APPEND);
            for ($i = 0; $i < 2; $i ++) {
                // file_put_contents("loguser.txt",$i."----", FILE_APPEND);
                foreach ($datetype as $val) {
                    // file_put_contents("loguser.txt",$val."||".$userid."||".$websiteid."|||||||", FILE_APPEND);
                    $count = $Tikuanmoney->where("t=" . $i . " and userid=" . $userid . " and payapiid=" . $tongdao["id"] . " and websiteid = " . $websiteid . " and datetype = '" . $val . "'")->count();
                    // file_put_contents("loguser.txt",$count."*********", FILE_APPEND);
                    if ($count <= 0) {
                        $Tikuanmoney->t = $i;
                        $Tikuanmoney->datetype = $val;
                        $Tikuanmoney->userid = $userid;
                        $Tikuanmoney->websiteid = $websiteid;
                        $Tikuanmoney->payapiid = $tongdao["id"];
                        $Tikuanmoney->add();
                        $value = "0.00";
                    } else {
                        $value = $Tikuanmoney->where("t=" . $i . " and userid=" . $userid . " and payapiid=" . $tongdao["id"] . " and websiteid = " . $websiteid . " and datetype = '" . $val . "'")->getField("money");
                    }
                    $array["form" . $tongdao["id"]]["t" . $i . $val] = $value;
                }
            }
            $array["form" . $tongdao["id"]]["tikuanpayapiid"] = $tongdao["id"];
            $array["form" . $tongdao["id"]]["userid"] = $useriduserid;
        }
        
        $Tikuanconfig = M("Tikuanconfig");
        $count = $Tikuanconfig->where("websiteid=" . $websiteid . " and userid=" . $userid)->count();
        if ($count <= 0) {
            $data["websiteid"] = $websiteid;
            $data["userid"] = $userid;
            $Tikuanconfig->add($data);
        }
        $tikuanconfiglist = $Tikuanconfig->where("websiteid=" . $websiteid . " and userid=" . $userid)->find();
        $arraystr = array();
        $arraystr["tikuanconfig"] = $tikuanconfiglist;
        $arraystr["tksz"] = $array;
        $this->ajaxReturn($arraystr, "json");
    }

    public function Edittikuanmoney()
    {
        $userid = I("post.userid");
        
        $User = M("User");
        $usertype = $User->where("id=" . $userid)->getField("usertype");
        $websiteid = $User->where("id=" . $userid)->getField("websiteid");
        /*
         * if($usertype == 2){ //如果用户类型为2 分站管理员
         * $Website = M("Website");
         * $websiteid = $Website->where("userid=".$userid)->getField("id");
         * $useriduserid = $userid;
         * $userid = 0;
         *
         * }else{
         * $websiteid = 0;
         * }
         */
        
        $payapiid = I("post.tikuanpayapiid");
        
        $datetype = array(
            "b",
            "w",
            "j"
        );
        
        $Tikuanmoney = M("Tikuanmoney");
        
        for ($i = 0; $i < 2; $i ++) {
            foreach ($datetype as $val) {
                $Tikuanmoney->money = I("post.t" . $i . $val, 0);
                $Tikuanmoney->where("t=" . $i . " and userid=" . $userid . " and payapiid=" . $payapiid . " and websiteid = " . $websiteid . " and datetype = '" . $val . "'")->save();
            }
        }
        exit("修改成功！");
    }

    /**
     * 用户资金操作
     */
    public function usermoney()
    {
        $userid = I("get.userid");
        $info = M("Member")->where("id=" . $userid)->find();
        $this->assign('info', $info);
        $this->display();
    }

    /**
     * 增加、减少余额
     */
    public function incrMoney()
    {
        $userid = I("request.uid");
        $info = M("Member")->where("id=" . $userid)->find();
        $this->assign('info', $info);

        if(IS_POST){
            $userid = I("post.uid");
            $cztype = I("post.cztype");
            $bgmoney = I("post.bgmoney");
            $contentstr = I("post.memo", "");

            if(($info['balance']-$bgmoney)<0 && $cztype == 4) {
                $this->ajaxReturn(['status' => 0,'msg'=>"账上余额不足" . $bgmoney . "元，不能完成减金操作"]);
            }
            if($cztype==3){
                //停用
               // $data["balance"] = array('exp',"balance+".$bgmoney);
                $this->ajaxReturn(['status'=>0]);
            }elseif($cztype==4){
                $data["balance"] = array('exp',"balance-".$bgmoney);
            }
            $res = M('Member')->where(['id'=>$userid])->save($data);
            if ($res) {
                $arrayField = array(
                    "userid" => $userid,
                    "money" => $bgmoney,
                    "datetime" => date("Y-m-d H:i:s"),
                    "tongdao" => '',
                    "transid" => "",
                    "orderid" => "",
                    "lx" => $cztype, // 增减类型
                    "contentstr" => $contentstr
                );
                moneychangeadd($arrayField);
            }
            $this->ajaxReturn(['status'=>$res]);

        }else{
            $this->display();
        }
    }

    /**
     * 冻结、解冻余额
     */
    public function frozenMoney()
    {
        $userid = I("request.uid");
        $info = M("Member")->where("id=" . $userid)->find();
        $this->assign('info', $info);

        if(IS_POST){
            $userid = I("post.uid");
            $cztype = I("post.cztype");
            $bgmoney = I("post.bgmoney");
            $contentstr = I("post.memo", "");

            if(($info['blockedbalance']-$bgmoney)<0 && $cztype == 8) {
                $this->ajaxReturn(['status' => 0,'msg'=>"账上冻结余额不足" . $bgmoney . "元，不能完成减金操作"]);
            }
            if($cztype==7){
                $data["balance"] = array('exp',"balance-".$bgmoney);
                $data["blockedbalance"] = array('exp',"blockedbalance+".$bgmoney);
            }elseif($cztype==8){
                $data["balance"] = array('exp',"balance+".$bgmoney);
                $data["blockedbalance"] = array('exp',"blockedbalance-".$bgmoney);
            }
            $res = M('Member')->where(['id'=>$userid])->save($data);
            if ($res) {
                $arrayField = array(
                    "userid" => $userid,
                    "money" => $bgmoney,
                    "datetime" => date("Y-m-d H:i:s"),
                    "tongdao" => '',
                    "transid" => "",
                    "orderid" => "",
                    "lx" => $cztype, // 增减类型
                    "contentstr" => $contentstr
                );
                moneychangeadd($arrayField);
            }
            $this->ajaxReturn(['status'=>$res]);

        }else{
            $this->display();
        }
    }

    //切换身份
    public function changeuser()
    {
        $userid = I('get.userid');
        $info = M('Member')->where(['id='.$userid])->find();
        if($info){
            $user_auth = [
                'uid'=>$info['id'],
                'username'=>$info['username'],
                'groupid'=>$info['groupid'],
                'password'=>$info['password']
            ];
            session('user_auth',$user_auth);
            ksort($user_auth); //排序
            $code = http_build_query($user_auth); //url编码并生成query字符串
            $sign = sha1($code);
            session('user_auth_sign',$sign);
            header('Location:'.$this->_site.'User.html');
        }
    }

    //用户状态切换
    public function editStatus()
    {
        if(IS_POST){
            $userid = intval(I('post.uid'));
            $isstatus= I('post.isopen') ? I('post.isopen'):0;
            $res = M('Member')->where(['id'=>$userid])->save(['status'=>$isstatus]);
            $this->ajaxReturn(['status'=>$res]);
        }
    }

    /**
     * 用户认证
     */
    public function authorize()
    {
        $userid = I('get.uid',0,'intval');
        if($userid){
            $data = M('Member')->where(['id'=>$userid])->find();
            //上传图片
            $images = M('Attachment')
                ->where(['userid'=>$userid])
                ->limit(6)
                ->field('path')
                ->select();
            $data['images'] = $images;
            $this->assign('u',$data);
        }
        $this->display();
    }

    //编辑用户级别
    public function editUser()
    {
        $userid = I('get.uid',0,'intval');
        if($userid){
            $data = M('Member')
                ->where(['id'=>$userid])->find();
            $this->assign('u',$data);

            //用户组
            $groups = M('AuthGroup')->field('id,title')->select();
            $this->assign('groups',$groups);
        }
        $this->display();
    }
    //保存编辑用户级别
    public function saveUser()
    {
        if(IS_POST){
            $userid = I('post.userid',0,'intval');
            $u = $_POST['u'];
            $u['birthday'] = strtotime($u['birthday']);
            if($u['birthday']<1){
                $u['birthday'] = 1000000;
            }
            //print_r($u['birthday']);
            $res = M('Member')->where(['id'=>$userid])->save($u);

            //编辑用户组
            if($res){
                M('AuthGroupAccess')->where(['uid'=>$userid])->save(['group_id'=>$u['groupid']]);
            }
            $this->ajaxReturn(['status'=>$res]);
        }
    }

    //编辑用户费率
    public function userRateEdit()
    {
        $userid = I('get.uid',0,'intval');
        //系统产品列表
        $products = M('Product')
            ->where(['status'=>1,'isdisplay'=>1])
            ->field('id,name')
            ->select();
        //用户产品列表
        $userprods = M('Userrate')->where(['userid'=>$userid])->select();
        if($userprods){
            foreach ($userprods as $item){
                $_tmpData[$item['payapiid']] = $item;
            }
        }
        //重组产品列表
        $list = [];
        if($products){
            foreach ($products as $key=>$item){
                $products[$key]['feilv'] = $_tmpData[$item['id']]['feilv']?$_tmpData[$item['id']]['feilv']:'0.000';
                $products[$key]['fengding'] = $_tmpData[$item['id']]['fengding']?$_tmpData[$item['id']]['fengding']:'0.000';
            }
        }
        $this->assign('products',$products);
        $this->display();
    }

    //保存费率
    public function saveUserRate(){
        if(IS_POST){
            $userid = intval(I('post.userid'));
            $rows = $_POST['u'];
            //print_r($rows);
            $datalist = [];
            foreach ($rows as $key=>$item){
                $rates = M('Userrate')->where(['userid'=>$userid,'payapiid'=>$key])->find();
                if($rates){
                    $data_insert[] = ['id'=>$rates['id'],'userid'=>$userid,'payapiid'=>$key,'feilv'=>$item['feilv'],'fengding'=>$item['fengding']];
                }else{
                    $data_update[] = ['userid'=>$userid,'payapiid'=>$key,'feilv'=>$item['feilv'],'fengding'=>$item['fengding']];
                }
            }
            M('Userrate')->addAll($data_insert,[],true);
            M('Userrate')->addAll($data_update,[],true);
            $this->ajaxReturn(['status'=>1]);
        }
    }

    //编辑用户通道
    public function editUserProduct()
    {
        $userid = I('get.uid',0,'intval');
        //系统产品列表
        $products = M('Product')
            ->where(['isdisplay'=>1])
            ->field('id,name,status,paytype')
            ->select();
        //用户产品列表
        $userprods = M('Product_user')->where(['userid'=>$userid])->select();
        if($userprods){
            foreach ($userprods as $key=>$item){
                $_tmpData[$item['pid']] = $item;
            }
        }
        //重组产品列表
        $list = [];
        if($products){
            foreach ($products as $key=>$item) {
                $products[$key]['status'] = $_tmpData[$item['id']]['status'];
                $products[$key]['channel'] = $_tmpData[$item['id']]['channel'];
                $products[$key]['polling'] = $_tmpData[$item['id']]['polling'];
                //权重
                $weights = [];
                $weights = explode('|', $_tmpData[$item['id']]['weight']);
                $_tmpWeight = "";
                if (is_array($weights)) {
                    foreach ($weights as $value) {
                        list($pid, $weight) = explode(':', $value);
                        if($pid) {
                            $_tmpWeight[$pid] = ['pid' => $pid, 'weight' => $weight];
                        }
                    }
                }else{
                    list($pid, $weight) = explode(':', $_tmpData[$item['id']]['weight']);
                    if($pid) {
                        $_tmpWeight[$pid] = ['pid' => $pid, 'weight' => $weight];
                    }
                }
                $products[$key]['weight'] = $_tmpWeight;
            }
        }
        $this->assign('products',$products);
        $this->display();
    }
    //保存编辑用户通道
    public function saveUserProduct()
    {
        if(IS_POST){
            $userid = I('post.userid',0,'intval');
            $u = $_POST['u'];
            foreach ($u as $key=>$item){
                $weightStr = '';
                $status = $item['status'] ? $item['status'] : 0;
                if(is_array($item['w'])){
                    foreach ($item['w'] as $weigths){
                        if($weigths['pid']){
                            $weightStr .= $weigths['pid'].':'.$weigths['weight']."|";
                        }
                    }
                }
                $product = M('Product_user')->where(['userid'=>$userid,'pid'=>$key])->find();
                if($product){
                    $data_insert[] = ['id'=>$product['id'],'userid'=>$userid,'pid'=>$key,'status'=>$status,'polling'=>$item['polling'],'channel'=>$item['channel'],'weight'=>trim($weightStr,'|')];
                }else{
                    $data_update[] = ['userid'=>$userid,'pid'=>$key,'status'=>$status,'polling'=>$item['polling'],'channel'=>$item['channel'],'weight'=>trim($weightStr,'|')];
                }
            }
            M('Product_user')->addAll($data_insert,[],true);
            M('Product_user')->addAll($data_update,[],true);
            $this->ajaxReturn(['status'=>1]);
        }
    }

    //提现
    public function userWithdrawal()
    {
        $userid = I('get.uid',0,'intval');
        $data = M('Tikuanconfig')->where(['userid'=>$userid])->find();
        $this->assign('u',$data);
        $this->display();
    }
    //保存提现规则
    public function saveWithdrawal()
    {
        if(IS_POST){
            $userid = I('post.userid',0,'intval');
            $id = I('post.id',0,'intval');
            if($_POST['u']['systemxz']){
                $rows = $_POST['u'];
            }else{
                $rows['systemxz'] = 0;
            }
            if($id){
                $res = M('Tikuanconfig')->where(['id'=>$id,'userid'=>$userid])->save($rows);
            }else{
                $rows['userid'] = $userid;
                $res = M('Tikuanconfig')->add($rows);
            }
            $this->ajaxReturn(['status'=>$res]);
        }
    }
}
?>
