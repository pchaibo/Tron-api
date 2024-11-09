<?php
namespace Admin\Controller;

use Think\Page;

class ChannelController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->assign("Public", MODULE_NAME); // 模块名称
        $this->assign('paytypes',C('PAYTYPES'));

        //通道
        $channels = M('Channel')
            ->where(['status'=>1])
            ->field('id,code,title,paytype,status')
            ->select();
        $this->assign('channels',$channels);
        $this->assign('channellist',json_encode($channels));
    }

    //供应商接口列表
    public function index()
    {
        $count = M('Channel')->count();
        $Page = new Page($count,15);
        $data = M('Channel')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->order('id DESC')
            ->select();

        $this->assign('list',$data);
        $this->assign('page',$Page->show());
        $this->display();
    }

    /**
     * 保存编辑供应商
     */
    public function saveEditSupplier()
    {
        if(IS_POST){
            $id = I('post.id',0,'intval');
            $papiacc = $_POST['pa'];
            $_request['code'] = trim($papiacc['code']);
            $_request['title'] = trim($papiacc['title']);
            $_request['mch_id'] = trim($papiacc['mch_id']);
            $_request['signkey'] = trim($papiacc['signkey']);
            $_request['appid'] = trim($papiacc['appid']);
            $_request['appsecret'] = trim($papiacc['appsecret']);
            $_request['gateway'] = trim($papiacc['gateway']);
            $_request['pagereturn'] = $papiacc['pagereturn'];
            $_request['serverreturn'] = $papiacc['serverreturn'];
            $_request['defaultrate'] = $papiacc['defaultrate'] ? $papiacc['defaultrate'] : 0;
            $_request['fengding'] = $papiacc['fengding'] ? $papiacc['fengding'] : 0;
            $_request['rate'] = $papiacc['rate'] ? $papiacc['rate'] : 0;
            $_request['updatetime'] = time();
            $_request['unlockdomain'] = $papiacc['unlockdomain'];
            $_request['paytype'] = $papiacc['paytype'];
            $_request['status'] = $papiacc['status'];

            if($id){
                //更新
                $res = M('Channel')->where(array('id'=>$id))->save($_request);
            }else{
                //添加
                $res = M('Channel')->add($_request);
            }
            $this->ajaxReturn(['status'=>$res]);
        }
    }

    //开启供应商接口
    public function editStatus()
    {
        if(IS_POST){
            $pid = intval(I('post.pid'));
            $isopen = I('post.isopen') ? I('post.isopen'):0;
            $res = M('Channel')->where(['id'=>$pid])->save(['status'=>$isopen]);
            $this->ajaxReturn(['status'=>$res]);
        }
    }

    //新增供应商接口
    public function addSupplier()
    {
        $this->display();
    }

    //编辑供应商接口
    public function editSupplier()
    {
        $pid = intval($_GET['pid']);
        if($pid){
            $pa = M('Channel')->where(['id'=>$pid])->find();
        }
        $this->assign('pa',$pa);
        $this->display('addSupplier');
    }
    //删除供应商接口
    public function delSupplier()
    {
        $pid = intval($_POST['pid']);
        if($pid){
            $res = M('Channel')->where(['id'=>$pid])->delete();
            $this->ajaxReturn(['status'=>$res]);
        }
    }

    //编辑费率
    public function editRate()
    {
        if(IS_POST){
            $pa = $_POST['pa'];
            if($_POST['pid']){
                $res = M('Channel')->where(['id'=>$_POST['pid']])->save($_POST['pa']);
                $pa['pid'] = $_POST['pid'];
                $this->ajaxReturn(['status'=>$res,'data'=>$pa]);
            }
        }else {
            $pid = intval(I('get.pid'));
            if ($pid) $data = M('Channel')->where(['id' => $pid])->find();
            $this->assign('pid',$pid);
            $this->assign('pa',$data);
            $this->display();
        }
    }

    //产品列表
    public function product()
    {
        $data = M('Product')->select();
        $this->assign('list',$data);
        $this->display();
    }

    //切换产品状态
    public function prodStatus()
    {
        if(IS_POST){
            $id = intval($_POST['id']);
            $colum = I('post.k');
            $value = I('post.v');
            $res = M('Product')->where(['id'=>$id])->save([$colum=>$value]);
            $this->ajaxReturn(['status'=>$res]);
        }
    }

    //切换用户显示状态
    public function prodDisplay()
    {
        if(IS_POST){
            $id = intval($_POST['id']);
            $colum = I('post.k');
            $value = I('post.v');
            $res = M('Product')->where(['id'=>$id])->save([$colum=>$value]);
            $this->ajaxReturn(['status'=>$res]);
        }
    }
    //添加产品
    public function addProduct()
    {
        $this->display();
    }

    //编辑产品
    public function editProduct()
    {
        $id = I('get.pid',0,'intval');
        $data = M('Product')->where(['id'=>$id])->find();

        //权重
        $weights = [];
        $weights = explode('|', $data['weight']);
        $_tmpWeight = '';
        if (is_array($weights)) {
            foreach ($weights as $value) {
                list($pid, $weight) = explode(':', $value);
                if($pid) {
                    $_tmpWeight[$pid] = ['pid' => $pid, 'weight' => $weight];
                }
            }
        }else{
            list($pid, $weight) = explode(':', $data['weight']);
            if($pid) {
                $_tmpWeight[$pid] = ['pid' => $pid, 'weight' => $weight];
            }
        }
        $data['weight'] = $_tmpWeight;
        //通道
        $channels = M('Channel')->where(["paytype"=>$data['paytype'],"status"=>1])->select();
        $this->assign('channels',$channels);
        $this->assign('pd',$data);
        $this->display('addProduct');
    }

    //保存更改
    public function saveProduct()
    {
        if(IS_POST){
            $id = intval($_POST['id']);
            $rows = $_POST['pd'];
            $weight = $_POST['w'];
            //权重
            $weightStr = '';
            if(is_array($weight)){
                foreach ($weight as $weigths){
                    if($weigths['pid']){
                        $weightStr .= $weigths['pid'].':'.$weigths['weight']."|";
                    }
                }
            }
            $rows['weight'] = trim($weightStr,'|');
            //保存
            if($id){
                $res = M('Product')->where(['id'=>$id])->save($rows);
            }else{
                $res = M('Product')->add($rows);
            }
            $this->ajaxReturn(['status'=>$res]);
        }
    }

    //删除产品
    public function delProduct()
    {
        if(IS_POST )
        {
            $id = I('post.pid',0,'intval');
            $res = M('Product')->where(['id'=>$id])->delete();
            $this->ajaxReturn(['status'=>$res]);
        }
    }

    //接口模式
    public function selProduct()
    {
        if(IS_POST){
            $paytyep = intval($_POST['paytype']);
            //通道
            $data = M('Channel')->where(["paytype"=>$paytyep,"status"=>1])->select();
            $this->ajaxReturn(['status'=>0,'data'=>$data]);
        }
    }
}
?>
