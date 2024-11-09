<?php

namespace Pay\Controller;

use Think\Controller;

class PayController extends Controller
{
    //商家信息
    public $merchants;
    //网站地址
    public $_site;
    //通道信息
    public $channel;

    public function __construct()
    {
        parent::__construct();
        $this->_site = ((is_https()) ? 'https' : 'http') . '://' . C("DOMAIN") . '/';
    }
    /**
     * 创建订单
     * @param $parameter
     * @return array
     */
    public function orderadd($parameter)
    {
        //通道信息
        $this->channel = $parameter['channel'];
        //$this->merchants = $this->channel['userid'];
        //用户信息
        $usermodel = D('Member');
        $this->merchants = $usermodel->get_Userinfo($this->channel['userid']);
        $return = array();
        // 通道名称
        $PayName = $parameter["code"];
        // 交易金额比例
        $moneyratio = $parameter["exchange"];
        //商户编号
        $return["memberid"] = $userid = $this->merchants['id']+10000;
        //费率
        $_userrate = M('Userrate')
            ->where(["userid"=>$this->channel['userid'],"payapiid"=>$this->channel['pid']])
            ->find();
        //银行通道费率
        $syschannel = M('Channel')
            ->where(['id'=>$this->channel['api']])
            ->find();

        //平台通道
        $platform = M('Product')
            ->where(['id'=>$this->channel['pid']])
            ->find();

        //回调参数
        $return = [
            "mch_id" => $syschannel["mch_id"], //商户号
            "signkey" => $syschannel["signkey"], // 签名密钥
            "appid" => $syschannel["appid"], // APPID
            "appsecret" => $syschannel["appsecret"], // APPSECRET
            "gateway" => $syschannel["gateway"] ? $syschannel["gateway"] : $parameter["gateway"], // 网关
            "notifyurl" => $syschannel["serverreturn"] ? $syschannel["serverreturn"] : $this->_site . "Pay_" .
        $PayName . "_notifyurl.html",
            "callbackurl" => $syschannel["pagereturn"] ? $syschannel["pagereturn"] : $this->_site . "Pay_" .
        $PayName . "_callbackurl.html",
            'unlockdomain' => $syschannel['unlockdomian'] ? $syschannel['unlockdomian'] : '', //防封域名
        ];

        //用户优先通道
        $feilv = $_userrate['feilv'] ? $_userrate['feilv'] : $syschannel['defaultrate']; // 交易费率
        $fengding = $_userrate['fengding'] ? $_userrate['fengding'] : $syschannel['fengding']; // 封顶手续费
        $fengding = $fengding == 0 ? 9999999 : $fengding; //如果没有设置封顶手续费自动设置为一个足够大的数字

        //金额格式化
        $pay_amount = I("request.pay_amount", 0);
        if (!$pay_amount or !is_numeric($pay_amount)) {
            $this->showmessage('金额错误');
        }
        $return["amount"] = floatval($pay_amount) * $moneyratio; // 交易金额
        $pay_sxfamount = (($pay_amount * $feilv) > ($pay_amount * $fengding)) ? ($pay_amount*$fengding) :
        ($pay_amount * $feilv); // 手续费
        $pay_shijiamount = $pay_amount - $pay_sxfamount; // 实际到账金额

        //商户订单号
        $out_trade_id = $parameter['out_trade_id'];
        //生成系统订单号
        $pay_orderid = $parameter['orderid'] ? $parameter['orderid'] : get_requestord();

        //验签
        if ($this->verify()) {
            $Order = M("Order");
            $return["bankcode"] = $this->channel['pid'];
            $return['code'] = $platform['code']; //银行英文代码
            $return["orderid"] = $pay_orderid; // 系统订单号
            $return["out_trade_id"] = $out_trade_id; // 外部订单号
            $return["subject"] = $parameter['body']; // 商品标题
            $data["pay_memberid"] = $userid;
            $data["pay_orderid"] = $return["orderid"];
            $data["pay_amount"] = $pay_amount; // 交易金额
            $data["pay_poundage"] = $pay_sxfamount; // 手续费
            $data["pay_actualamount"] = $pay_shijiamount; // 到账金额
            $data["pay_applydate"] = time();
            $data["pay_bankcode"] = $this->channel['pid'];
            $data["pay_bankname"] = $platform['name'];
            $data["pay_notifyurl"] = I("request.pay_notifyurl");
            $data["pay_callbackurl"] = I("request.pay_callbackurl");
            $data["pay_status"] = 0;
            $data["pay_tongdao"] = $syschannel['code'];
            $data["pay_zh_tongdao"] = $syschannel['title'];
            $data["pay_ytongdao"] = $parameter["code"];
            $data["pay_yzh_tongdao"] = $parameter["title"];
            $data["pay_tjurl"] = $_SERVER["HTTP_REFERER"];
            $data["pay_productname"] = I("request.pay_productname");
            $data["pay_productnum"] = I("request.pay_productnum");
            $data["pay_productdesc"] = I("request.pay_productdesc");
            $data["pay_producturl"] = I("request.pay_producturl");
            $data["attach"] = I("request.pay_attach");
            $data["out_trade_id"] = $out_trade_id;
            $data["ddlx"] = I("post.ddlx", 0);
            $data["memberid"] = $return["mch_id"];
            $data["key"] = $return["signkey"];
            $data["account"] = $return["appid"];

            //添加订单
            if ($Order->add($data)) {
                $return['datetime'] = date('Y-m-d H:i:s', $data['pay_applydate']);
                $return["status"] = "success";
                return $return;
            } else {
                $this->showmessage('系统错误');
            }
        } else {
            $this->showmessage('签名验证失败',$_POST);
        }
    }

    /**
     * 回调处理订单
     * @param $TransID
     * @param $PayName
     * @param int $returntype
     */
    public function EditMoney($TransID, $PayName, $returntype = 1)
    {
        $Order = M("Order");
        $list = $Order->where(['pay_orderid'=>$TransID])->find();
        $userid = intval($list["pay_memberid"] - 10000); // 商户ID
        if ($list["pay_status"] == 0) {
            //更新订单状态 1 已成功未返回 2 已成功已返回
            $Order->where(['pay_orderid' => $TransID])->save(['pay_status' => 1, 'pay_successdate' => time()]);
            //支付通道
            $syschannel = M('Channel')->where(['mch_id'=>$list['memberid'],'code'=>$list['pay_ytongdao']])->find();
            //通道金额统计
            $Apimoney = M("Apimoney");
            $_apimoney = $Apimoney->where("userid=" . $userid . " and payapiid=" . $syschannel['id'])->find();
            if (!$_apimoney) {
                $data = array();
                $data["userid"] = $userid;
                $data["payapiid"] = $syschannel['id'];
                $Apimoney->add($data);
                $ymoney = 0;
            } else {
                $ymoney = $_apimoney['money'];
            }
            // 通道账户金额
            $moneymoney = floatval($ymoney) + floatval($list["pay_actualamount"]);
            $Apimoney->where("userid=" . $userid . " and payapiid=" . $syschannel['id'])->setField("money", $moneymoney);

            //商户余额、冻结余额
            $tikuanconfig = M('Tikuanconfig')->where(['userid'=>$userid])->find();
            if(!$tikuanconfig || $tikuanconfig['tkzt']!=1){
                $tikuanconfig = M('Tikuanconfig')->where(['issystem'=>1])->find();
            }
            //T+1结算
            if($tikuanconfig['t1zt']==1){
                M('Member')->where(['id'=>$userid])->save(['blockedbalance'=>array('exp',"blockedbalance+{$list['pay_actualamount']}")]);
                $rows = [
                    'userid'=>$userid,
                    'orderid'=>$list['pay_orderid'],
                    'amount'=>$list['pay_actualamount'],
                    'thawtime'=>(strtotime('tomorrow')+rand(0,7200)),
                    'pid'=>$list['pay_bankcode'],
                    'createtime'=>time(),
                    'status'=>0
                ];
                M('Blockedlog')->add($rows);

            }else{ //T+0结算
                M('Member')->where(['id'=>$userid])->save(['balance'=>array('exp', "balance+{$list['pay_actualamount']}")]);
            }
            // 商户充值金额变动
            $arrayField = array(
                "userid" => $userid,
                "money" => $list["pay_actualamount"],
                "datetime" => date("Y-m-d H:i:s"),
                "tongdao" => $list['pay_bankcode'],
                "transid" => $TransID,
                "orderid" => $list["out_trade_id"],
                'contentstr'=>$list['out_trade_id'].'订单充值',
                "lx" => 1
            );
            $this->MoenyChange($arrayField); // 资金变动记录
            // 通道ID
            $arrayStr = array(
                "userid" => $userid, // 用户ID
                "transid" => $TransID, // 订单号
                "money" => $list["pay_amount"], // 金额
                "tongdao" => $list['pay_bankcode'],
            );
            $this->bianliticheng($arrayStr); // 提成处理
        }
        $Md5key = M('Member')->where(["id"=>$userid])->getField("apikey");
        $returnArray = array( // 返回字段
            "memberid" => $list["pay_memberid"], // 商户ID
            "orderid" => $list['out_trade_id'], // 订单号
            'transaction_id'=>$list["pay_orderid"], //支付流水号
            "amount" => $list["pay_amount"], // 交易金额
            "datetime" => date("YmdHis"), // 交易时间
            "returncode" => "00", // 交易状态
        );
        $sign = $this->createSign($Md5key, $returnArray);
        $returnArray["sign"] = $sign;
        $returnArray["attach"] = $list["attach"];

        if ($returntype == 1) {
            //file_put_contents("lg.txt",serialize($returnArray)."\n", FILE_APPEND);
            //acetion_log($list['out_trade_id'],$list["pay_callbackurl"].'?'.http_build_query($returnArray),'跳转下发成功,无返回.');
            $this->setHtml($list["pay_callbackurl"], $returnArray);
        } elseif ($returntype == 0) {
            $notifystr = "";
            foreach ($returnArray as $key => $val) {
                $notifystr = $notifystr . $key . "=" . $val . "&";
            }
            $notifystr = substr($notifystr, 0, -1);
            //$tjurl = $list["pay_notifyurl"] . "?" .$notifystr;
            //file_put_contents("./loga.txt",$tjurl."\n", FILE_APPEND);
            //$contents = fopen($tjurl, "r");
            //$contents = fread($contents, 128);
            /*$ch = curl_init();
            curl_setopt ($ch, CURLOPT_URL, $tjurl);
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT,30);
            $contents = curl_exec($ch);
            curl_close($ch);*/
            //logResult($notifystr);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT,10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, $list["pay_notifyurl"]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $notifystr);
            $contents = curl_exec($ch);
            curl_close($ch);
            //file_put_contents("./return.txt",$contents."\n", FILE_APPEND);
            //acetion_log($list['out_trade_id'],$list["pay_notifyurl"].'?'.$notifystr,'异步下发成功,返回：'.$contents);
            // if($contents == "ok"){
            if (strstr(strtolower($contents), "ok") != false) {
                //更新交易状态
                $Order = M("Order");
                $_orderwhere = array('id'=>$list['id'],'pay_orderid'=>$list["pay_orderid"]);
                $Order->where($_orderwhere)->setField("pay_status", 2);
            } else {
                //$this->jiankong($list['pay_orderid']);
            }
        }
    }

    // 支付签名
    public function get_paysign($arraystr, $key)
    {
        ksort($arraystr);
        $buff = "";
        foreach ($arraystr as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        $string = $buff . "&key=".$key;
        $string = md5($string);
        $sign = strtoupper($string);
        return $sign;
    }

    /**
     *  验证签名
     * @return bool
     */
    protected function verify()
    {
        //POST参数
        $requestarray = array(
            "pay_memberid" => I("request.pay_memberid", 0,'intval'),
            "pay_orderid" => I("request.pay_orderid", ""),
            "pay_amount" => I("request.pay_amount", ""),
            "pay_applydate" => I("request.pay_applydate", ""),
            "pay_bankcode" => I("request.pay_bankcode", ""),
            "pay_notifyurl" => I("request.pay_notifyurl", ""),
            "pay_callbackurl" => I("request.pay_callbackurl", "")
        );
        $md5key = $this->merchants["apikey"];
        $md5keysignstr = $this->createSign($md5key, $requestarray);
        $pay_md5sign = I("request.pay_md5sign");
        if ($pay_md5sign == $md5keysignstr) {
            return true;
        } else {
            return false;
        }
    }

    public function setHtml($tjurl, $arraystr)
    {
        $str = '<form id="Form1" name="Form1" method="post" action="' . $tjurl . '">';
        foreach ($arraystr as $key => $val) {
            $str .= '<input type="hidden" name="' . $key . '" value="' . $val . '">';
        }
        $str .= '</form>';
        $str .=  '<script>';
        $str .= 'document.Form1.submit();';
        $str .= '</script>';
        exit( $str);
    }

    /**
     * 获取验签密钥
     * @param $code
     * @param $mch_id
     * @return mixed
     */
    public function getSignkey($code, $mch_id)
    {
        $signkey = M("Channel")->where(["code"=>$code,"mch_id"=>$mch_id])->getField("signkey");
        return $signkey;
    }

    public function getmd5keykey($PayName, $MemberID)
    {
        $Payapi = M("Payapi");
        $payapiid = $Payapi->where("en_payname='" . $PayName . "'")->getField("id");
        $Payapiaccount = M("Payapiaccount");
        $key = $Payapiaccount->where("payapiid=" . $payapiid . " and sid = '" . $MemberID . "'")->getField("keykey");
        return $key;
    }

    /**
     * 资金变动记录
     * @param $arrayField
     * @return bool
     */
    protected function MoenyChange($arrayField)
    { // 资金变动
        $Moneychange = M("Moneychange");
        foreach ($arrayField as $key => $val) {
            $data[$key] = $val;
        }
        $Moneychange->add($data);
        return true;
    }

    /**
     * 佣金处理
     * @param $arrayStr
     * @param int $num
     * @param int $tcjb
     * @return bool
     */
    private function bianliticheng($arrayStr, $num = 1, $tcjb = 1)
    {
        if ($num <= 0) {
           return false;
        }
        $userid = $arrayStr["userid"];
        $tongdaoid = $arrayStr["tongdao"];
        $feilvfind = $this->huoqufeilv($userid, $tongdaoid);
        if ($feilvfind["status"] == "error") {
            return false;
        } else {
            //商户费率（下级）
            $x_feilv = $feilvfind["feilv"];
            $x_fengding = $feilvfind["fengding"];

            //代理商(上级)
            $parentid = M("Member")->where("id=" . $userid)->getField("parentid");
            if ($parentid <= 1) {
                return false;
            }
            $parentRate = $this->huoqufeilv($parentid, $tongdaoid);
            if ($parentRate["status"] == "error") {
                return false;
            } else {
                //代理商(上级）费率
                $s_feilv = $parentRate["feilv"];
                $s_fengding = $parentRate["fengding"];

                //费率差
                $ratediff = (($x_feilv*1000) - ($s_feilv*1000))/1000;
                if ($ratediff <= 0) {
                    return false;
                } else {
                    $parent = M('Member')->where(['id'=>$parentid])->field('id,balance')->find();
                    $brokerage = $arrayStr['money'] * $ratediff;
                    //代理佣金
                    $rows = [
                        'balance'=>array('exp', "balance+{$brokerage}")
                    ];
                    M('Member')->where(['id'=>$parentid])->save($rows);

                    //代理商资金变动记录
                    $arrayField = array(
                        "userid" => $parentid,
                        "ymoney" => $parent['balance'],
                        "money" => $arrayStr["money"] * $ratediff,
                        "gmoney" => $parent['balance'] + $brokerage,
                        "datetime" => date("Y-m-d H:i:s"),
                        "tongdao" => $tongdaoid,
                        "transid" => $arrayStr["transid"],
                        "orderid" => "tx" . date("YmdHis"),
                        "tcuserid" => $userid,
                        "tcdengji" => $tcjb,
                        "lx" => 9
                    );
                    $this->MoenyChange($arrayField); // 资金变动记录
                    $num = $num - 1;
                    $tcjb = $tcjb + 1;
                    $arrayStr["userid"] = $parentid;
                    $this->bianliticheng($arrayStr, $num, $tcjb);
                }
            }
        }
    }

    private function huoqufeilv($userid, $payapiid)
    {
        $return = array();
        //用户费率
        $userrate = M("Userrate")->where("userid=" . $userid . " and payapiid=" . $payapiid)->find();
        //支付通道费率
        $syschannel = M('Channel')->where(['id'=>$payapiid])->find();

        $feilv = $userrate['feilv'] ? $userrate['feilv'] : $syschannel['defaultrate']; // 交易费率
        $fengding = $userrate['fengding'] ? $userrate['fengding'] : $syschannel['fengding']; // 封顶手续费

        $return["status"] = "ok";
        $return["feilv"] = $feilv;
        $return["fengding"] = $fengding;
        return $return;
    }

    /**
     * 创建签名
     * @param $Md5key
     * @param $list
     * @return string
     */
    protected function createSign($Md5key, $list)
    {
        ksort($list);
        $md5str = "";
        foreach ($list as $key => $val) {
            if(!empty($val)){
                $md5str = $md5str . $key . "=" . $val . "&";
            }
        }
        $sign = strtoupper(md5($md5str . "key=" . $Md5key));
        return $sign;
    }

    public function callbackurl()
    { // 页面跳转返回
        $ReturnArray = array( // 返回字段
            "memberid" => I("request.memberid"), // 商户ID
            "orderid" => I("request.orderid"), // 订单号
            "amount" => I("request.amount"), // 交易金额
            "datetime" => I("request.datetime"), // 交易时间
            "returncode" => I("request.returncode")
        ) // 交易状态
        ;
        $Userverifyinfo = M("Userverifyinfo");
        $Md5key = $Userverifyinfo->where("userid=" . (intval(I("request.memberid")) - 10000))->getField("md5key");
        $sign = $this->md5sign($Md5key, $ReturnArray);
        if ($sign == I("request.sign")) {
            if (I("request.returncode") == "00") {
                $this->assign("factMoney", I("request.amount"));
                $this->assign("TransID", I("request.orderid"));
                $this->assign("SuccTime", date("Y-m-d H:i:s"));
                $this->display();
            }
        }
    }

    public function notifyurl()
    { // 页面跳转返回
        $ReturnArray = array( // 返回字段
            "memberid" => I("request.memberid"), // 商户ID
            "orderid" => I("request.orderid"), // 订单号
            "amount" => I("request.amount"), // 交易金额
            "datetime" => I("request.datetime"), // 交易时间
            "returncode" => I("request.returncode")
        ) // 交易状态
        ;
        $Userverifyinfo = M("Userverifyinfo");
        $Md5key = $Userverifyinfo->where("userid=" . (intval(I("get.memberid")) - 10000))->getField("md5key");
        $sign = $this->md5sign($Md5key, $ReturnArray);
        if ($sign == I("get.sign")) {
            if (I("get.returncode") == "00") {
                exit("ok");
            }
        }
    }

    public function bufa()
    {
        header('Content-type:text/html;charset=utf-8');
        $TransID = I("get.TransID");
        $PayName = I("get.tongdao");
        echo("订单号：" . $TransID . "|" . $PayName . "已补发服务器点对点通知，请稍后刷新查看结果！<a href='javascript:window.close();'>关闭</a>");
        $this->EditMoney($TransID, $PayName, 0);
    }

    public function jiankong($orderid)
    {
        ignore_user_abort(true);
        set_time_limit(3600);
        $Order = M("Order");
        $interval=10;
        do {
            if($orderid){
                $_where['pay_status'] = 1;
                $_where['num'] = array('lt',3);
                $_where['pay_orderid'] = $orderid;
                $find = $Order->where($_where)->find();
            }else{
                $find = $Order->where("pay_status = 1 and num < 3")->order("id desc")->find();
            }
            if ($find) {
                $this->EditMoney($find["pay_orderid"], $find["pay_tongdao"], 0);
                $Order->where("id=" . $find["id"])->save(array('num'=>array('exp','num+1')));
            }
            //file_put_contents("abc.txt", $find["pay_orderid"] . "=>" . $find["pay_tongdao"] . "\n", FILE_APPEND);
            sleep($interval);
        } while (true);
    }

    /**
     * 扫码订单状态检查
     *
     */
    public function checkstatus()
    {
        $orderid = I("post.orderid");
        $Order = M("Order");
        $order = $Order->where("pay_orderid='" . $orderid . "'")->find();
        if ($order['pay_status'] <> 0) {
            echo json_encode(array('status'=>'ok','callback'=>$this->_site."Pay_".$order['pay_tongdao']."_callbackurl.html?orderid="
                .$orderid."&pay_memberid=".$order['pay_memberid'].'&bankcode='.$order['pay_bankcode']));
            exit();
        } else {
            exit("no-$orderid");
        }
    }


    /**
     * 错误返回
     * @param string $msg
     * @param array $fields
     */
    protected function showmessage($msg='', $fields=array())
    {
        header('Content-Type:application/json; charset=utf-8');
        $data = array('status'=>'error', 'msg'=>$msg, 'data'=>$fields);
        echo json_encode($data,320);
        exit;
    }

    /**
     * 来路域名检查
     * @param $pay_memberid
     */
    protected function domaincheck($pay_memberid)
    {
        $referer = $_SERVER["HTTP_REFERER"]; // 获取完整的来路URL
        $domain = $_SERVER['HTTP_HOST'];
        $pay_memberid = intval($pay_memberid) - 10000;
        $User = M("User");
        $num = $User->where("id=" . $pay_memberid)->count();
        if ($num <= 0) {
            $this->showmessage("商户编号不存在");
        } else {
            $websiteid = $User->where("id=" . $pay_memberid)->getField("websiteid");
            $Websiteconfig = M("Websiteconfig");
            $websitedomain = $Websiteconfig->where("websiteid = " . $websiteid)->getField("domain");

            if ($websitedomain != $domain) {
                $Userverifyinfo = M("Userverifyinfo");
                $domains = $Userverifyinfo->where("userid=" . $pay_memberid)->getField("domain");
                if (!$domains) {
                    $this->showmessage("域名错误 ");
                } else {
                    $arraydomain = explode("|", $domains);
                    $checktrue = true;
                    foreach ($arraydomain as $key => $val) {
                        if ($val == $domain) {
                            $checktrue = false;
                            break;
                        }
                    }
                    if ($checktrue) {
                        $this->showmessage("域名错误 ");
                    }
                }
            }
        }
    }
}
?>