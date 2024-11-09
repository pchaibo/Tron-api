<?php
/**
 * Created by PhpStorm.
 * User: gaoxi
 * Date: 2017-08-22
 * Time: 14:34
 */
namespace User\Controller;

use Think\Page;
use Think\Upload;

/**
 *  商家账号相关控制器
 * Class AccountController
 * @package User\Controller
 */

class AccountController extends UserController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 编辑个人资料
     */
    public function profile()
    {
        $list = M("Member")->where(['id'=>$this->fans['uid']])->find();
        $this->assign("p", $list);
        $this->display();
    }
    /**
     * 绑定手机号码
     */
    public function mobile(){
        $list =  M("Member")->where(['id'=>$this->fans['uid']])->find();
        //print_r($list);
        $this->assign("p", $list);
        $this->display();
    }
    /**
     * 保存手机号码
     */
    public function savemobile(){
        $p = $_POST['p'];
        $mobile = $p['mobile'];
        if(!preg_match("/^1[34578]\d{9}$/", $mobile)){
            exit("mobile not null");
        }
        if(IS_POST){
            $uid = I('post.id',0,'intval');
            $p = I('post.p');
            $where = array();
            $where['message'] = $p['code'];
            $where['mobile'] = $mobile;
            $res = M('shortmessage')->where($where)->find();
            if(!$res){
                $this->ajaxReturn(['status'=>$res]);
            }
            $p['ismobile'] =1;
            
            $res = M('Member')->where(['id'=>$uid])->save($p);
            $this->ajaxReturn(['status'=>$res]);
        }
    }
    
    /**
     * 保存个人资料
     */
    public function saveProfile()
    {
        if(IS_POST){
            $uid = I('post.id',0,'intval');
            $p = I('post.p');
            $p['birthday'] = strtotime($p['birthday']);
            //删除手机号
            unset($p['mobile']);
            $res = M('Member')->where(['id'=>$uid])->save($p);
            $this->ajaxReturn(['status'=>$res]);
        }
    }

    /**
     *  银行卡列表
     */
    public function bankcard()
    {
        $list = M('Bankcard')
            ->where(['userid'=>$this->fans['uid']])
            ->order('id desc')
            ->select();
        $this->assign("list", $list);
        $this->display();
    }

    /**
     *  添加银行卡
     */
    public function addBankcard()
    {
        $banklist = M("Systembank")->select();
        $this->assign("banklist", $banklist);

        if(IS_POST){
            $id = I('post.id',0,'intval');
            $rows = I('post.b');
            if($id){
                $res = M('Bankcard')->where(['id'=>$id])->save($rows);
            }else{
                $rows['userid'] = $this->fans['uid'];
                $res = M('Bankcard')->add($rows);
            }
            $this->ajaxReturn(['status'=>$res]);
        }else{
            $id = I('get.id',0,'intval');
            if($id){
                $data = M('Bankcard')->where(['id'=>$id])->find();
                $this->assign('b',$data);
            }
            $this->display();
        }

    }

    /**
     *  修改默认
     */
    public function editBankStatus()
    {
        if(IS_POST){
            $id = I('post.id');
            $isdefault = I('post.isopen');
            if($id){
                if($isdefault){
                    M('Bankcard')->where(['userid'=>$this->fans['uid']])->save(['isdefault'=>0]);
                }
                $res = M('Bankcard')->where(['id'=>$id])->save(['isdefault'=>$isdefault]);
                $this->ajaxReturn(['status'=>$res]);
            }
        }
    }
    /**
     *  删除银行卡
     */
    public function delBankcard()
    {
        if (IS_POST){
            $id = I('post.id',0,'intval');
            if($id){
                $res = M('Bankcard')->where(['id'=>$id])->delete();
                $this->ajaxReturn(['status'=>$res]);
            }
        }
    }
    public function bankcardedit()
    {
        if(IS_POST){
            $id = I('post.id');
            $Ip = new \Org\Net\IpLocation('UTFWry.dat'); // 实例化类 参数表示IP地址库文件
            $location = $Ip->getlocation(); // 获取某个IP地址所在的位置

            $Bankcard = M("Bankcard");
            $_formdata = array(
                'userid'=>session("userid"),
                'bankname'=>I('post.bankname'),
                'bankfenname'=>I('post.bankfenname'),
                'bankzhiname'=>I('post.bankzhiname'),
                'banknumber'=>I('post.banknumber'),
                'bankfullname'=>I('post.bankfullname'),
                'sheng'=>I('post.sheng'),
                'shi'=>I('post.shi'),
                'kdatetime'=>date("Y-m-d H:i:s"),
                'jdatetime'=>date("Y-m-d H:i:s", time()+40*3600*24),
                'ip'=>$location['ip'],
                'ipaddress'=>$location['country'] . "-" . $location['area'],
                'disabled'=>1,
            );
            if($id){
                $result = $Bankcard->where(array('id'=>$id))->save($_formdata);
            }else{
                $result = $Bankcard->add($_formdata);
            }
            $Bankcard->getLastSql();
            if ($result) {
                $this->success("银行卡信息修改成功！");
            } else {
                $this->error("银行卡息修改失败！");
            }
        }
    }


    public function loginrecord()
    {
        $maps['userid'] = $this->fans['uid'];
        $count = M('Loginrecord')->where($maps)->count();
        $page = new Page($count,5);
        $list = M('Loginrecord')
            ->where($maps)
            ->limit($page->firstRow.','.$page->listRows)
            ->order('id desc')
            ->select();
        $this->assign("list", $list);
        $this->assign('page',$page->show());
        $this->display();
    }

    /**
     *  商户认证
     */
    public function authorized()
    {
        $authorized = M('Member')->where(['id'=>$this->fans['uid']])->getField('authorized');
        $list = [];
        $list = M('Attachment')->where(['userid'=>$this->fans['uid']])->select();
        $this->assign('list',$list);
        $this->assign('authorized',$authorized);
        $this->display();
    }

    public function upload()
    {
        if(IS_POST){
            $upload = new Upload();
            $upload->maxSize = 2097152;
            $upload->exts = array('jpg', 'gif', 'png');
            $upload->savePath = '/verifyinfo/';
            $info = $upload->uploadOne($_FILES['auth']);
            if (! $info) { // 上传错误提示错误信息
                $this->error($upload->getError());
            } else {
                $data = [
                    'userid'=>$this->fans['uid'],
                    'filename'=>$info['name'],
                    'path'=>'Uploads'.$info['savepath'].$info['savename'],
                ];
                $res = M("Attachment")->add($data);
                $this->ajaxReturn($res);
            }
        }
    }


    public function certification()
    {
        M('Member')->where(['id'=>$this->fans['uid']])->save(['authorized'=>2]);
        $this->success('已申请认证，请等待审核！');
    }


    /**
     *  修改支付密码
     */
    public function editPaypassword()
    {
        $data = M('Member')->where(['id'=>$this->fans['uid']])->find();
        $this->assign('p',$data);

        if(IS_POST){
            $id = I('post.id');
            $p = I('post.p');
            if(!$p['oldpwd'] || !$p['newpwd'] || !$p['secondpwd'] || $p['newpwd']!=$p['secondpwd'] ||
                $data['paypassword'] != md5($p['oldpwd'])){
                $this->ajaxReturn(['status'=>0,'msg'=>'输入错误']);
            }
            $res = M('Member')->where(['id'=>$id])->save(['paypassword'=>md5($p['newpwd'])]);
            $this->ajaxReturn(['status'=>$res]);
        }else{
            $this->display();
        }
    }

    /**
     * 修改密码
     */
    public function editPassword()
    {
        $data = M('Member')->where(['id'=>$this->fans['uid']])->find();
        $this->assign('p',$data);

        if(IS_POST){
            $salt = $data['salt'];
            $id = I('post.id');
            $p = I('post.p');
            if(!$p['oldpwd'] || !$p['newpwd'] || !$p['secondpwd'] || $p['newpwd']!=$p['secondpwd'] || $data['password'] != md5
                ($p['oldpwd'] .$salt)){
                $this->ajaxReturn(['status'=>0,'msg'=>'输入错误']);
            }
            $res = M('Member')->where(['id'=>$id])->save(['password'=>md5($p['newpwd'].$salt)]);
            $this->ajaxReturn(['status'=>$res]);
        }else{
            $this->display();
        }
    }

    /**
     *  资金变动记录
     */
    public function changeRecord()
    {
        //商户支付通道
        $products = M('ProductUser')
            ->join('LEFT JOIN __PRODUCT__ ON __PRODUCT__.id = __PRODUCT_USER__.pid')
            ->where(['pay_product_user.status'=>1,'pay_product_user.userid'=>$this->fans['uid']])
            ->field('pay_product.name,pay_product.id,pay_product.code')
            ->select();
        $this->assign("products", $products);

        $where = array();
        $orderid = I("get.orderid");
        if ($orderid) {
            $where['transid'] = array('eq',$orderid);
        }
        $tongdao = I("request.tongdao");
        if ($tongdao) {
            $where['tongdao'] = array('eq',$tongdao);
        }
        $bank = I("request.bank",'','strip_tags');
        if ($bank) {
            $where['lx'] = array('eq',$bank);
        }
        $createtime = urldecode(I("request.createtime"));
        if ($createtime) {
            list($cstime,$cetime) = explode('|',$createtime);
            $where['datetime'] = ['between',[$cstime,$cetime?$cetime:date('Y-m-d')]];
        }
        $where['userid'] = $this->fans['uid'];
        $count = M('Moneychange')->where($where)->count();
        $page = new Page($count,15);
        $list = M('Moneychange')
            ->where($where)
            ->limit($page->firstRow.','.$page->listRows)
            ->order('id desc')
            ->select();
        $this->assign('list',$list);
        $this->assign('page',$page->show());
        C('TOKEN_ON',false);
        $this->display();
    }
}
?>
