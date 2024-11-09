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
 * 商家结算控制器
 * Class WithdrawalController
 * @package User\Controller
 */

class WithdrawalController extends UserController
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 结算记录
     */
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
        $tongdao = I("request.tongdao");
        if ($tongdao) {
            $where['payapiid'] = array('eq',$tongdao);
        }
        $T = I("request.T");
        if ($T != "") {
            $where['t'] = array('eq',$T);
        }
        $status = I("request.status",0,'intval');
        if ($status) {
            $where['status'] = array('eq',$status);
        }
        $createtime = urldecode(I("request.createtime"));
        if ($createtime) {
            list($cstime,$cetime) = explode('|',$createtime);
            $where['sqdatetime'] = ['between',[$cstime,$cetime?$cetime:date('Y-m-d')]];
        }
        $successtime = urldecode(I("request.successtime"));
        if ($successtime) {
            list($sstime,$setime) = explode('|',$successtime);
            $where['cldatetime'] = ['between',[$sstime,$setime?$setime:date('Y-m-d')]];
        }
        $where['userid'] = $this->fans['uid'];
        $count = M('Tklist')->where($where)->count();
        $page = new Page($count,15);
        $list = M('Tklist')
            ->where($where)
            ->limit($page->firstRow.','.$page->listRows)
            ->order('id desc')
            ->select();

        $this->assign("list", $list);
        $this->assign("page", $page->show());
        $this->display();
    }

    /**
     *  申请结算
     */
    public function clearing()
    {
        //结算方式：
        $tkconfig = M('Tikuanconfig')->where(['userid'=>$this->fans['uid']])->find();
        if(!$tkconfig || $tkconfig['tkzt']!=1){
            $tkconfig = M('Tikuanconfig')->where(['issystem'=>1])->find();
        }

        //可用余额
        $info = M('Member')->where(['id'=>$this->fans['uid']])->find();

        //银行卡
        $bankcards = M('Bankcard')->where(['userid'=>$this->fans['uid']])->select();


        $this->assign('tkconfig',$tkconfig);
        $this->assign('bankcards',$bankcards);
        $this->assign('info',$info);
        $this->display();
    }

    /**
     * 计算手续费
     */
    public function calculaterate()
    {
        if(IS_POST && I('post.userid') == $this->fans['uid']){
            $type = I('post.tktype');
            $feilv = I('post.feilv');
            $balance = I('post.balance');
            $money = I('post.money');
            if($balance<$money){
                $this->ajaxReturn(['status'=>0,'msg'=>'金额输入错误!']);
            }
            //结算方式：
            $tkconfig = M('Tikuanconfig')->where(['userid'=>$this->fans['uid']])->find();
            if(!$tkconfig || $tkconfig['tkzt']!=1){
                $tkconfig = M('Tikuanconfig')->where(['issystem'=>1])->find();
            }
            //单笔最小提款金额
            if($tkconfig['tkzxmoney']>$money){
                $this->ajaxReturn(['status'=>0,'msg'=>'单笔最低提款额度：'.$tkconfig['tkzxmoney']]);
            }
            //单笔最大提款金额
            if($tkconfig['tkzdmoney']<$money){
                $this->ajaxReturn(['status'=>0,'msg'=>'单笔最大提款额度：'.$tkconfig['tkzdmoney']]);
            }
            if($type){
                $data['amount'] = $money - $feilv;
                $data['brokerage'] = $feilv;
            }else{
                $data['amount'] = $money * (1-($feilv/100));
                $data['brokerage'] = $money * ($feilv/100);
            }
            $this->ajaxReturn(['status'=>1,'data'=>$data]);
        }
    }

    /**
     * 提现申请
     */
    public function saveClearing()
    {
        if(IS_POST){
            $userid = I('post.userid',0,'intval');
            $tktype = I('post.tktype');
            $feilv = I('post.feilv');
            $u = I('post.u');
            //个人信息
            $info = M('Member')->where(['id'=>$userid])->find();
            //结算方式：
            $tkconfig = M('Tikuanconfig')->where(['userid'=>$userid])->find();
            if(!$tkconfig || $tkconfig['tkzt']!=1){
                $tkconfig = M('Tikuanconfig')->where(['issystem'=>1])->find();
            }

            if($tkconfig['t1zt']){
                $t = 1;
            }else{
                $t = 0;
            }
            //处理短信 code
            if(!$u['code']){
               // $this->ajaxReturn(['status'=>0,'msg'=>'请输入短信验证码!']);
            }
            $shortmessage = M('shortmessage')->where(['mobile'=>$info['mobile'],'message'=>$u['code']])->find();
            if(!$shortmessage['message']){
              //  $this->ajaxReturn(['status'=>0,'msg'=>'请输入短信验证码不正确!'.$u['code']]);
            }
            
            if(!$u['bank']){
                $this->ajaxReturn(['status'=>0,'msg'=>'请选择结算银行卡!']);
            }
            //支付密码
            if(md5($u['password']) != $info['paypassword']){
                $this->ajaxReturn(['status'=>0,'msg'=>'支付密码有误!']);
            }
            //是否在许可的提现时间
            $hour = intval(date('H'));
            if($tkconfig['allowend'] == 0){
                $tkconfig['allowend'] = 24;
            }
            if($tkconfig['allowstart'] > $hour || $tkconfig['allowend']<= $hour){
                $this->ajaxReturn(['status'=>0,'msg'=>'不在提现时间，请换个时间再来!']);
            }
            //单笔最小提款金额
            $tkzxmoney = $tkconfig['tkzxmoney'];
            if($tkzxmoney>$u['money']){
                $this->ajaxReturn(['status'=>0,'msg'=>'单笔最低提款额度：'.$tkzxmoney]);
            }
            //单笔最大提款金额
            $tkzdmoney =  $tkconfig['tkzdmoney'];
            if($tkzdmoney<$u['money']){
                $this->ajaxReturn(['status'=>0,'msg'=>'单笔最大提款额度：'.$tkzdmoney]);
            }
            //当日最大总金额
            $dayzdmoney =  $tkconfig['dayzdmoney'];
            //总次数
            $dayzdnum = $tkconfig['dayzdnum'];
            //今日总金额，总次数
            $yesterday = date('Y-m-d',strtotime('yesterday'));
            $today = date('Y-m-d');
            $map['userid'] = $userid;
            if($tktype){
                $map['sqdatetime'] = ['between', array($yesterday,$today)];
            }else{
                $map['sqdatetime'] = ['egt',$today];
            }
            $curtknum = M('Tklist')->where($map)->count();
            $curwtnum = M('Wttklist')->where($map)->count();
            $curtksum  = M('Tklist')->where($map)->sum('tkmoney');
            $curwtsum  = M('Wttklist')->where($map)->sum('tkmoney');
            if(($curtknum+$curwtnum)>=$dayzdnum){
                $this->ajaxReturn(['status'=>0,'msg'=>"超出当日提款次数！"]);
            }
            if(($curtksum+$curwtsum)>=$dayzdmoney){
                $this->ajaxReturn(['status'=>0,'msg'=>"超出当日提款额度！"]);
            }

            //减用户余额
            $res = M('Member')->where(['id'=>$userid])->save(['balance'=>array('exp','balance-'.$u['money'])]);

            if($res){
                //银行卡信息
                $bank = M('Bankcard')->where(['id'=>$u['bank']])->find();
                //提现记录
                $data = [
                    "bankname" => $bank["bankname"],
                    "bankzhiname" => $bank["subbranch"],
                    "banknumber" => $bank["cardnumber"],
                    "bankfullname" => $bank['accountname'],
                    "sheng" => $bank["province"],
                    "shi" => $bank["city"],
                    "userid" => $userid,
                    "sqdatetime" => date("Y-m-d H:i:s"),
                    "status" => 0,
                    'tkmoney'=>$u['money'],
                    "t" => $t,
                    "money" => $u['amount']
                ];
                if($tktype){
                    //$data['tkmoney'] = $u['money'] - $feilv;
                    $data['sxfmoney'] = $feilv;
                }else{
                    //$data['tkmoney'] = $u['money'] * ((1-$feilv)/100);
                    $data['sxfmoney'] = $u['money'] * ($feilv/100);
                }
                $result = M('Tklist')->add($data);
                if($result){
                    $rows = [
                        "userid" => $userid,
                        "money" => $u['money'],
                        "datetime" => date("Y-m-d H:i:s"),
                        "transid" => "",
                        "orderid" => "",
                        "lx" => 6,
                        'contentstr'=> date("Y-m-d H:i:s") .'提现操作'
                    ];
                    M('Moneychange')->add($rows);
                }
                $this->ajaxReturn(['status'=>$res]);
            }else{
                $this->ajaxReturn(['status'=>0]);
            }
        }
    }

    /**
     *  委托结算记录
     */
    public function payment()
    {
        //通道
        $products = M('ProductUser')
            ->join('LEFT JOIN __PRODUCT__ ON __PRODUCT__.id = __PRODUCT_USER__.pid')
            ->where(['pay_product_user.status'=>1,'pay_product_user.userid'=>$this->fans['uid']])
            ->field('pay_product.name,pay_product.id,pay_product.code')
            ->select();
        $this->assign("banklist", $products);

        $where = array();
        $tongdao = I("request.tongdao");
        if ($tongdao) {
            $where['payapiid'] = array('eq',$tongdao);
        }
        $T = I("request.T");
        if ($T != "") {
            $where['t'] = array('eq',$T);
        }
        $status = I("request.status",0,'intval');
        if ($status) {
            $where['status'] = array('eq',$status);
        }
        $createtime = urldecode(I("request.createtime"));
        if ($createtime) {
            list($cstime,$cetime) = explode('|',$createtime);
            $where['sqdatetime'] = ['between',[$cstime,$cetime?$cetime:date('Y-m-d')]];
        }
        $successtime = urldecode(I("request.successtime"));
        if ($successtime) {
            list($sstime,$setime) = explode('|',$successtime);
            $where['cldatetime'] = ['between',[$sstime,$setime?$setime:date('Y-m-d')]];
        }
        $where['userid'] = $this->fans['uid'];
        $count = M('Wttklist')->where($where)->count();
        $page = new Page($count,15);
        $list = M('Wttklist')
            ->where($where)
            ->limit($page->firstRow.','.$page->listRows)
            ->order('id desc')
            ->select();

        $this->assign("list", $list);
        $this->assign("page", $page->show());
        $this->display();
    }


    /**
     *  委托结算
     */
    public function entrusted(){
        //结算方式：
        $tkconfig = M('Tikuanconfig')->where(['userid'=>$this->fans['uid']])->find();
        if(!$tkconfig || $tkconfig['tkzt']!=1){
            $tkconfig = M('Tikuanconfig')->where(['issystem'=>1])->find();
        }
        //可用余额
        $info = M('Member')->where(['id'=>$this->fans['uid']])->find();

        $this->assign('tkconfig',$tkconfig);
        $this->assign('info',$info);
        $this->display();
    }

    /**
     *  保存委托申请
     */
    public function saveEntrusted()
    {
        if(IS_POST){
            $userid = I('post.userid',0,'intval');
            $balance = I('post.balance');
            $tktype = I('post.tktype');
            $feilv = I('post.feilv');
            $password = I('post.password');
            if(!$password){
                $this->error('支付密码有误！');
            }

            //上传文件
            $upload = new Upload();
            $upload->maxSize = 3145728 ;
            $upload->exts = array('xls', 'xlsx');
            $upload->savePath = '/wtjsupload/';
            $info = $upload->uploadOne($_FILES["file"]);
            if (! $info) { // 上传错误提示错误信息
                $this->error($upload->getError());
            } else {
                $file = './Uploads/wtjsupload/'.$info['savename'];
                $excel = $this->importExcel($file);

                //个人信息
                $user = M('Member')->where(['id'=>$userid])->find();
                //结算方式：
                $tkconfig = M('Tikuanconfig')->where(['userid'=>$userid])->find();
                if(!$tkconfig || $tkconfig['tkzt']!=1){
                    $tkconfig = M('Tikuanconfig')->where(['issystem'=>1])->find();
                }
                if($tkconfig['t1zt']){
                    $t = 1;
                }else{
                    $t = 0;
                }
                if($user['balance']<$excel['tkmoney']){
                    $this->error('金额错误，可用余额不足!');
                }
                //支付密码
                if(md5($password) != $user['paypassword']){
                    $this->error('支付密码有误!');
                }
                //是否在许可的提现时间
                $hour = date('H');
                if($tkconfig['allowend'] == 0){
                    $tkconfig['allowend'] = 24;
                }
                if($tkconfig['allowstart']>=$hour || $tkconfig['allowend']<=$hour){
                    $this->error('不在提现时间，请换个时间再来!');
                }
                //单笔最小提款金额
                $tkzxmoney = $tkconfig['tkzxmoney'];
                //单笔最大提款金额
                $tkzdmoney =  $tkconfig['tkzdmoney'];
                if($excel['tkmoney']<$tkzxmoney || $excel['tkmoney']>$tkzdmoney){
                    $this->error("提款金额不符合提款额度要求！");
                }
                //当日最大总金额
                $dayzdmoney =  $tkconfig['dayzdmoney'];
                //总次数
                $dayzdnum = $tkconfig['dayzdnum'];
                //今日总金额，总次数
                $yesterday = date('Y-m-d',strtotime('yesterday'));
                $today = date('Y-m-d');
                $map['userid'] = $userid;
                if($tktype){
                    $map['sqdatetime'] = ['between', array($yesterday,$today)];
                }else{
                    $map['sqdatetime'] = ['egt',$today];
                }
                $curtknum = M('Tklist')->where($map)->count();
                $curwtnum = M('Wttklist')->where($map)->count();
                $curtksum  = M('Tklist')->where($map)->sum('tkmoney');
                $curwtsum  = M('Wttklist')->where($map)->sum('tkmoney');
                if(($curtknum+$curwtnum)>=$dayzdnum){
                    $this->error("超出当日提款次数！");
                }
                if(($curtksum+$curwtsum)>=$dayzdmoney){
                    $this->error("超出当日提款额度！");
                }

                //减用户余额
                $res = M('Member')->where(['id'=>$userid])->save(['balance'=>array('exp','balance-'.$excel['tkmoney'])]);

                if($res){
                    if($tktype){
                        $amount = $excel['tkmoney'] - $feilv;
                        $sxfmoney = $feilv;
                    }else{
                        $amount = $excel['tkmoney'] * ((1-$feilv)/100);
                        $sxfmoney = $excel['tkmoney'] * ($feilv/100);
                    }
                    //提现记录
                    $data = [
                        "bankname" => $excel["bankname"],
                        "bankzhiname" => $excel["bankzhiname"],
                        "banknumber" => $excel["banknumber"],
                        "bankfullname" => $excel['bankfullname'],
                        "sheng" => $excel["sheng"],
                        "shi" => $excel["shi"],
                        "userid" => $userid,
                        "sqdatetime" => date("Y-m-d H:i:s"),
                        "status" => 0,
                        "t" => $t,
                        'tkmoney'=>$excel['tkmoney'],
                        'sxfmoney'=>$sxfmoney,
                        "money" =>$amount
                    ];

                    $result = M('Wttklist')->add($data);
                    if($result){
                        $rows = [
                            "userid" => $userid,
                            "money" => $excel['tkmoney'],
                            "datetime" => date("Y-m-d H:i:s"),
                            "transid" => "",
                            "orderid" => "",
                            "lx" => 6,
                            'contentstr'=> date("Y-m-d H:i:s") .'委托提现操作'
                        ];
                        M('Moneychange')->add($rows);
                    }
                    $this->success("申请成功!");
                }else{
                    $this->error("申请失败!");
                }

            }
        }
    }

    /**
     * 导入EXCEL
     */
    public function importExcel($file)
    {
        header("Content-type: text/html; charset=utf-8");
        vendor("PHPExcel.PHPExcel");
        $objReader = \PHPExcel_IOFactory::createReader('Excel5');
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($file,$encode='utf-8');
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        $highestColumn = $sheet->getHighestColumn(); // 取得总列数
        for($i=2;$i<=$highestRow;$i++)
        {
            $data['bankname'] = $objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
            $data['bankzhiname'] = $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
            $data['bankfullname']= $objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
            $data['banknumber']= $objPHPExcel->getActiveSheet()->getCell("D".$i)->getValue();
            $data['sheng']= $objPHPExcel->getActiveSheet()->getCell("E".$i)->getValue();
            $data['shi']= $objPHPExcel->getActiveSheet()->getCell("F".$i)->getValue();
            $data['tkmoney']= $objPHPExcel->getActiveSheet()->getCell("G".$i)->getValue();
        }
        return $data;
    }

    public function excel($filePath,$a,$t,$paypaiid,$sxf,$sxflx){
    
        vendor("PHPExcel.PHPExcel");
    
        //$filePath = "Book1.xls";
        //建立reader对象
        $PHPReader = new \PHPExcel_Reader_Excel2007();
        if(!$PHPReader->canRead($filePath)){
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if(!$PHPReader->canRead($filePath)){
                echo 'no Excel';
                return ;
            }
        }

        //建立excel对象，此时你即可以通过excel对象读取文件，也可以通过它写入文件
        $PHPExcel = $PHPReader->load($filePath);
    
        /**读取excel文件中的第一个工作表*/
        $currentSheet = $PHPExcel->getSheet(0);
        /**取得最大的列号*/
        $allColumn = $currentSheet->getHighestColumn();
        /**取得一共有多少行*/
        $allRow = $currentSheet->getHighestRow();
         
        $summoney = 0;  //总金额
        
        switch ($a){
            case 1:   //获取总金额
                //循环读取每个单元格的内容。注意行从1开始，列从A开始
                for($rowIndex=2;$rowIndex<=$allRow;$rowIndex++){
                    for($colIndex='A';$colIndex<=$allColumn;$colIndex++){
                        $addr = $colIndex.$rowIndex;
                        $cell = $currentSheet->getCell($addr)->getValue();
                        if($cell instanceof PHPExcel_RichText){     //富文本转换字符串
                            $cell = $cell->__toString();
                        }
                        if($colIndex == "G"){
                            $summoney = $summoney + floatval($cell);
                        }
                    }
                }
                return  $summoney;
                break;
            case 2:
                //循环读取每个单元格的内容。注意行从1开始，列从A开始
                for($rowIndex=2;$rowIndex<=$allRow;$rowIndex++){
                    //金额
                    $addr = "G".$rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if($cell instanceof PHPExcel_RichText){     //富文本转换字符串
                        $cell = $cell->__toString();
                    }
                    $tkmoney = floatval($cell);
                    $tkmoney = sprintf("%.2f", $tkmoney);
                    $sxfmoney = 0;

                    if($sxflx == 1){
                        $sxfmoney = $sxf;
                    }else{
                        $sxfmoney = $tkmoney * ($sxf/100);
                    }

                    $sxfmoney = sprintf("%.2f", $sxfmoney);
                    ($tkmoney-$sxfmoney)>0 ? $money=($tkmoney-$sxfmoney): $money = 0; //实际到账金额

                    //银行名称
                    $addr = "A".$rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if($cell instanceof PHPExcel_RichText){     //富文本转换字符串
                        $cell = $cell->__toString();
                    }
                    $bankname = $cell;

                    //支行名称
                    $addr = "B".$rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if($cell instanceof PHPExcel_RichText){     //富文本转换字符串
                        $cell = $cell->__toString();
                    }
                    $bankzhiname = $cell;

                    //开户名
                    $addr = "C".$rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if($cell instanceof PHPExcel_RichText){     //富文本转换字符串
                        $cell = $cell->__toString();
                    }
                    $bankfullname = $cell;

                    //银行账号
                    $addr = "D".$rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if($cell instanceof PHPExcel_RichText){     //富文本转换字符串
                        $cell = $cell->__toString();
                    }
                    $banknumber = $cell;

                    //所在省
                    $addr = "E".$rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if($cell instanceof PHPExcel_RichText){     //富文本转换字符串
                        $cell = $cell->__toString();
                    }
                    $sheng = $cell;

                    //所在市
                    $addr = "F".$rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if($cell instanceof PHPExcel_RichText){     //富文本转换字符串
                        $cell = $cell->__toString();
                    }
                    $shi = $cell;

                    if(!is_numeric($banknumber)){
                        $this->error('银行账号格式错误');
                    }
                    $Apimoney = M("Apimoney");
                    $yuemoney = $Apimoney->where("userid=" . session("userid") . " and payapiid=" . $paypaiid)->getField("money");
                    $data = array();
                    $data["money"] = sprintf("%.2f", ($yuemoney - $tkmoney));
                    if ($Apimoney->where("userid=" . session("userid") . " and payapiid=" . $paypaiid)->save($data)) {
                        //写入提款记录
                        $Wttklist = M("Wttklist");
                        $data = array();
                        $data["bankname"] = $bankname;
                        $data["bankzhiname"] = $bankzhiname;
                        $data["banknumber"] = intval($banknumber);
                        $data["bankfullname"] = $bankfullname;
                        $data["sheng"] = $sheng;
                        $data["shi"] = $shi;
                        $data["userid"] = session("userid");
                        $data["sqdatetime"] = date("Y-m-d H:i:s");
                        $data["status"] = 0;
                        $data["tkmoney"] = $tkmoney;
                        $data["sxfmoney"] = $sxfmoney;
                        $data["t"] = $t;
                        $data["money"] = $money;
                        $data["payapiid"] = $paypaiid;
                        $res = $Wttklist->add($data);
                        if ($res) {
                            $ArrayField = array(
                                "userid" => session("userid"),
                                "ymoney" => $yuemoney,
                                "money" => $tkmoney * (- 1),
                                "gmoney" => ($yuemoney - $tkmoney),
                                "datetime" => date("Y-m-d H:i:s"),
                                "tongdao" => $paypaiid,
                                "transid" => "",
                                "orderid" => "",
                                "lx" => 10
                            );                            ;
                            $Moneychange = M("Moneychange");
                            foreach ($ArrayField as $key => $val) {
                                $data[$key] = $val;
                            }
                            $Moneychange->add($data);
                           // exit("ok");
                        }
                    }
                }
                unlink($filePath);
                $this->success("委托结算提交成功！",U('Tikuan/wttklist'));
                break;
        }
        
        
       
    }
    
    
    
    public function exceldf($filePath,$a,$t,$paypaiid,$sxf,$sxflx){
    
        vendor("PHPExcel.PHPExcel");
    
        //$filePath = "Book1.xls";
    
        //建立reader对象
        $PHPReader = new \PHPExcel_Reader_Excel2007();
        if(!$PHPReader->canRead($filePath)){
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if(!$PHPReader->canRead($filePath)){
                echo 'no Excel';
                return ;
            }
        }
    
    
        //建立excel对象，此时你即可以通过excel对象读取文件，也可以通过它写入文件
        $PHPExcel = $PHPReader->load($filePath);
    
        /**读取excel文件中的第一个工作表*/
        $currentSheet = $PHPExcel->getSheet(0);
        /**取得最大的列号*/
        $allColumn = $currentSheet->getHighestColumn();
        /**取得一共有多少行*/
        $allRow = $currentSheet->getHighestRow();
         
        $summoney = 0;  //总金额
    
        switch ($a){
            case 1:   //获取总金额
                /////////////////////////////////////////////////////////
                //循环读取每个单元格的内容。注意行从1开始，列从A开始
                for($rowIndex=2;$rowIndex<=$allRow;$rowIndex++){
                    for($colIndex='A';$colIndex<=$allColumn;$colIndex++){
                        $addr = $colIndex.$rowIndex;
                        $cell = $currentSheet->getCell($addr)->getValue();
                        if($cell instanceof PHPExcel_RichText){     //富文本转换字符串
                            $cell = $cell->__toString();
                        }
                        if($colIndex == "G"){
                            $summoney = $summoney + floatval($cell);
                        }
    
                    }
                }
    
                return  $summoney;
                ////////////////////////////////////////////////////////
                break;
            case 2:
                /////////////////////////////////////////////////////////
                //循环读取每个单元格的内容。注意行从1开始，列从A开始
                $batchContent = "";
                $keynum = 0;
                $batchsummoney = 0;
                for($rowIndex=2;$rowIndex<=$allRow;$rowIndex++){
                     
                    $addr = "G".$rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if($cell instanceof PHPExcel_RichText){     //金额
                        $cell = $cell->__toString();
                    }
    
                    $tkmoney = floatval($cell);
                    
                    $batchsummoney = $batchsummoney + $tkmoney;
                    
                    $tkmoney = sprintf("%.2f", $tkmoney);
                 
                    if($sxflx == 1){
                        $sxfmoney = $sxf;
                    }else{
                        $sxfmoney = $tkmoney*$sxf;
                    }
                    $sxfmoney = sprintf("%.2f", $sxfmoney);
                    $tkmoney-$sxfmoney>0?$money=$tkmoney-$sxfmoney:$money=0; //实际到账金额
    
                    $addr = "D".$rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if($cell instanceof PHPExcel_RichText){     //银行名称
                        $cell = $cell->__toString();
                    }
                    $bankname = $cell;
    
                    $addr = "E".$rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if($cell instanceof PHPExcel_RichText){     //分行名称
                        $cell = $cell->__toString();
                    }
                    $bankfenname = $cell;
    
                    $addr = "F".$rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if($cell instanceof PHPExcel_RichText){     //支行名称
                        $cell = $cell->__toString();
                    }
                    $bankzhiname = $cell;
    
                    $addr = "C".$rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if($cell instanceof PHPExcel_RichText){     //用户名
                        $cell = $cell->__toString();
                    }
                    $bankfullname = $cell;
    
                    $addr = "B".$rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if($cell instanceof PHPExcel_RichText){     //银行卡号
                        $cell = $cell->__toString();
                    }
                    $banknumber = $cell;
    
                    $addr = "H".$rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if($cell instanceof PHPExcel_RichText){     //富文本转换字符串
                        $cell = $cell->__toString();
                    }
                    $sheng = $cell;
    
    
                    $addr = "I".$rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if($cell instanceof PHPExcel_RichText){     //富文本转换字符串
                        $cell = $cell->__toString();
                    }
                    $shi = $cell;
                    
                    $addr = "J".$rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if($cell instanceof PHPExcel_RichText){     //手机号
                        $cell = $cell->__toString();
                    }
                    $shoujihao = $cell;
                  
                    $zhlx = "私";
                    
                   
                    $bizhong = "CNY";
                    $keynum = $keynum + 1;
                    $batchContent = $batchContent."$keynum,$banknumber,$bankfullname,$bankname,$bankfenname,$bankzhiname,$zhlx,$tkmoney,$bizhong,$sheng,$shi,$shoujihao,,,,,,|";
                    
                    $Apimoney = M("Apimoney");
                    $yuemoney = $Apimoney->where("userid=" . session("userid") . " and payapiid=" . $paypaiid)->getField("money");
                    $data = array();
                    $data["money"] = sprintf("%.2f", ($yuemoney - $tkmoney));
                    if ($Apimoney->where("userid=" . session("userid") . " and payapiid=" . $paypaiid)->save($data)) {
                        /**
                         * 写入提款记录
                         */
                        $Dflist = M("Dflist");
                        $data = array();
                        $data["bankname"] = $bankname;
                        $data["bankfenname"] = $bankfenname;
                        $data["bankzhiname"] = $bankzhiname;
                        $data["banknumber"] = $banknumber;
                        $data["bankfullname"] = $bankfullname;
                        $data["sheng"] = $sheng;
                        $data["shi"] = $shi;
                        $data["userid"] = session("userid");
                        $data["sqdatetime"] = date("Y-m-d H:i:s");
                        $data["cldatetime"] = date("Y-m-d H:i:s");
                        $data["status"] = 2;
                        $data["tkmoney"] = $tkmoney;
                        $data["sxfmoney"] = $sxfmoney;
                        $data["t"] = $t;
                        $data["money"] = $money;
                        $data["payapiid"] = $paypaiid;
                        if ($Dflist->add($data)) {
                            $ArrayField = array(
                                "userid" => session("userid"),
                                "ymoney" => $yuemoney,
                                "money" => $tkmoney * (- 1),
                                "gmoney" => ($yuemoney - $tkmoney),
                                "datetime" => date("Y-m-d H:i:s"),
                                "tongdao" => $paypaiid,
                                "transid" => "",
                                "orderid" => "",
                                "lx" => 11
                            ) // 代付结算
                            ;
                            $Moneychange = M("Moneychange");
                            foreach ($ArrayField as $key => $val) {
                                $data[$key] = $val;
                            }
                            $Moneychange->add($data);
                            // exit("ok");
                        }
                    }
    
    
                }
                /////////////////////////////////////////////////////////////////

               // vendor("RongBao.RSA");
                vendor("Rsa");
                $pubKeyFile = './cer/tomcat.cer';
                $prvKeyFile = './cer/100000000003161.p12';
                
               // $pubKeyFile ="D:\\wwwroot\\vhosts\\vip.bank-pay.com.cn\\Application\\User\\Controller\\cer\\tomcat.cer";
               // $prvKeyFile = "D:\\wwwroot\\vhosts\\vip.bank-pay.com.cn\\Application\\User\\Controller\\cer\\100000000003161.p12";
                
                $rsa = new \RSA($pubKeyFile,$prvKeyFile);
                
                $content = $batchContent;
                
               // echo($content."<br>");
              
                $batchContent = '';
                $length = strlen($content);
               // echo("[".$length."]");
                for ($i=0; $i<$length; $i+=100) {
                  //  echo(substr($content,$i,100)."<br>");
                    $x = $rsa->encrypt(substr($content,$i,100));
                    $batchContent .= "$x";
                }
               
               // exit("$pubKeyFile<br>$prvKeyFile-----".$batchContent);
                
                $_input_charset = "utf8";
                $batchBizid = "100000000003161";
                $batchVersion = "00";
                $batchBiztype = "00000";
                $batchDate = date("Ymd");
                //$batchCurrnum = "100000000003161".date("YmdHis").randpw(10,"NUMBER");
                $batchCurrnum =randpw(3,"NUMBER").date("YmdHis").randpw(3,"NUMBER");
                $batchCount =  $keynum;
                $batchAmount =  sprintf("%.2f", $batchsummoney);
                $signType = "MD5";
                
                $keystr = "652de6dgff5f983cg09df820c960e97acc20165dd76e3c56dcf6d2e80d3e183e";
                
                $dataArr['batchBizid'] = $batchBizid;
                $dataArr['batchVersion'] = $batchVersion;
                $dataArr['batchBiztype'] = $batchBiztype;
                $dataArr['batchDate'] = $batchDate;
                $dataArr['batchCurrnum'] = $batchCurrnum;
                $dataArr['batchCount'] = $batchCount;
                $dataArr['batchAmount'] = $batchAmount;
                $dataArr['batchContent'] = $batchContent;
                $dataArr['_input_charset'] = $_input_charset;
                
                $string = '';
                if (is_array($dataArr)){
                    foreach ($dataArr as $key=>$val) {
                        $string .= $key.'='.$val.'&';
                    }
                }
                $string = trim($string,'&');
                
                $sign = md5($string.$keystr);

                ////////////////////////////////////////////////////////////////
                unlink($filePath);
                $fkgate = "http://entrust.reapal.com/agentpay/pay";
               /*  $datastr = "_input_charset=$_input_charset&batchBizid=$batchBizid&batchVersion=$batchVersion&batchBiztype=$batchBiztype&batchDate=$batchDate&batchCurrnum=$batchCurrnum&batchCount=$batchCount&batchAmount=$batchAmount&batchContent=$batchContent&signType=$signType&sign=$sign";
                exit($datastr);
                $tjurl = $fkgate."?".$datastr;
                $contents = fopen($tjurl, "r");
                $contents = fread($contents, 100);
                if (strpos($contents, 'succ')) {
                   exit("代付成功！");
                } */
                ##################################################################
               // echo "发送地址：",$request_url,"\n";
               
                $dataArr["signType"] = $signType;
                $dataArr["sign"] =  $sign;

                $context = array(
                    'http' => array(
                        'method' => 'POST',
                        'header' => 'Content-type: application/x-www-form-urlencoded',
                        'content' => http_build_query($dataArr)
                    )
                );
                # var_dump($context);
                $streamPostData = stream_context_create($context);
                
                $httpResult = file_get_contents($fkgate, false, $streamPostData);
                 if (strpos($httpResult, 'succ')) {
                   $this->success("代付成功！");
                }else{
                    $this->error($httpResult);
                }
                ##################################################################
                //$this->success("委托结算提交成功！");
                ////////////////////////////////////////////////////////
                break;
        }
         
    }
    
}