<?php


namespace app\api\controller;


use app\api\services\OrderService;
use think\facade\Db;
use IEXBase\TronAPI;
use think\facade\Request;

class Trx extends Base
{
    
    /**
     * 查询trx余额
     * http://127.0.0.203/api/trx/AddressBalance?address=TChFoH1BGwyRQbtouijE2BBCYjrCQfM3VV
     * address 必选 string address 
     * return {"msg":"ok","data":{"amount":1895.09668},"code":1}
     */
    function AddressBalance(){
        $conifg = Config('trx');
        $geturl = new TronAPI\Provider\HttpProvider($conifg['url']);
        $fullNode = $geturl;
        $solidityNode = $geturl;
        $eventServer = $geturl;

        try {
            $tron = new TronAPI\Tron($fullNode, $solidityNode, $eventServer);
        } catch (TronAPI\Exception\TronException $e) {
            return json(getJson('网络有错！',0));
        }

        $params = $this->request->param();
        $address = trim($params['address']);
        if(!$address){return json(getJson('地址不能为空',0));}
        $tron->setAddress($address);
        $balance = $tron->getBalance(null, true);
        $data =array('amount'=>$balance);
        return json(getJson('ok',1,$data));

    }

    /**
     * TRX转账 
     * @catalog 自动文档/交易订单
     * @title TRX转账
     * @description TRX转账的接口
     * @method post
     * @url 接口域名/api/trx/send
     * @data http://127.0.0.203/api/trx/send?from_address=TF6G3uUhJGyaVLBzwjV66YaMAXkAvsamKH&address=TSmqHU26sgNFBxkx13QBEPGwUeoCJZs7Hp&amount=2.6
     * @param amount 必选 string amount 
     * @param from_address 必选 string from_address 
     * @param address 必选 string address 
     * @return {"code":1,"msg":"转账成功"}
     * @remark 这里是备注信息
     * @number 3
     */
    function send(){
        $conifg = Config('trx');
        $type = $conifg['type'];
        $params = $this->request->param();
        $params['address'] = input('get.address','','trim,strip_tags');
        $params['from_address'] = input('get.from_address','','trim,strip_tags');
        $params['amount'] = input('get.amount/f','','trim,strip_tags');
       
        if(!$params['address'] or !$params['from_address'] or !$params['amount'] ){
            return json(getJson('address or from_address or amount  not null',0));
        }
        
        $userdata = Db::name('address')->where(['address'=>$params['from_address'] ])->find();
        if(!$userdata){
            return json(getJson('key 不成在',0));
        }

        $params['key'] = $userdata['key'];
        $params['url'] = $conifg['url'];
        //print_r($userdata);
        $transfer = $this->transfer($params);//调用转账
        if( isset($transfer['code'])){
            return json(getJson('转账失败',0,$transfer));
        }

        $params['txid'] = $transfer['txid'];
        $params['symbol'] = 'trx';//类型
        $params['type'] = $type['trx'];//类型id 1:trx 2:usdt 3:jst
        
        $params['add_time'] = time();
        $symbol = $params['symbol'];
        $amount = $params['amount'];
       
        $resdata = Db::name('order')->where(['txid'=>$params['txid'] ])->find();
        if($resdata){
            return json(getJson('已经成在！'));
        }
        $params['transfer'] =$transfer;//交易信息
        unset($params['key']);
        if($this->orderadd($params)){
            return json(getJson('转账成功',1,$params));
        }else{
            return json(getJson('转账失败',0));
        }
    }

    /**
     * 交易
     * $data['from_address'];
     * $data['key'];
     * $data['address'];
     * $data['amount'];
     * $data['key'];
     * $data['url'];
     * 
     */
    function  transfer(array $data ) :array{
        $url =$data['url'];
        $fullNode = new TronAPI\Provider\HttpProvider($url);
        $solidityNode =  $fullNode;
        $eventServer =  $fullNode;
        try {
            $tron = new TronAPI\Tron($fullNode, $solidityNode, $eventServer);
        } catch (TronAPI\Exception\TronException $e) {
            return $e->getMessage();
        }
        $tron->setAddress($data['from_address']);
        $tron->setPrivateKey($data['key']);

        try {
            $transfer = $tron->send($data['address'],$data['amount']);
        } catch (TronAPI\Exception\TronException $e) {
            return $e->getMessage();
        }
        return $transfer;

    }

    /**
     * 扫区块转账 
     * @catalog 自动文档/交易订单
     * @title 扫区块转账交易
     * @description 扫区块转账交易
     * @method get
     * @url 接口域名http://127.0.0.203/api/trx/notify?from_address=TF6G3uUhJGyaVLBzwjV66YaMAXkAvsamKH&address=TChFoH1BGwyRQbtouijE2BBCYjrCQfM3VV&txid=82225222&amount=5
     * @param txid 必选 string txid 
     * @param amount 必选 string amount 
     * @param from_address 必选 string from_address 
     * @param address 必选 string address 
     * @return {"msg":"转账记录","data":{"from_address":"TF6G3uUhJGyaVLBzwjV66YaMAXkAvsamKH","address":"TChFoH1BGwyRQbtouijE2BBCYjrCQfM3VV","txid":"82225225882","amount":2.6,"type":1,"symbol":"trx","add_time":1731635439},"code":1}
     * @remark 这里是备注信息
     * @number 3
     */
    function notify($params =array()){
        $params = $this->request->param();
        $params['txid'] = input('get.txid','','trim,strip_tags');
        $params['address'] = input('get.address','','trim,strip_tags');
        $params['from_address'] = input('get.from_address','','trim,strip_tags');
        $params['amount'] = input('get.amount/f','','trim,strip_tags');

        $conifg = Config('trx');
        $type = $conifg['type'];
        $params['type'] = $type['trx'];
        $params['symbol'] = 'trx';
        $params['add_time'] = time();
        
        if(!$params['txid'] or !$params['address']  or !$params['from_address'] or !$params['amount']){
            return json(getJson('txid address from_address amount 参数不能为空',0));;
        }
        //$this->validate($params);//数据效验
        $resdata = Db::name('order')->where(['txid'=>$params['txid'] ])->find();
        if($resdata){
            return json(getJson('txid已成在！',0));
        }
        $userdata = Db::name('address')->where(['address'=>$params['address'] ])->find();
        if(!$userdata){
            return json(getJson('地址不成在',0));
        }
        if($this->orderadd($params)){
            if($userdata['notifyurl']){$this->postdata($userdata['notifyurl'],$params);} //异步通知
            return json(getJson('转账记录',1,$params));
        }else{
            return json(getJson('记录增加失败',0));
        }

    }

    /**
     * 异步通知转账记录
     */
    private function postdata($url,$data){
        $url =$url."?". http_build_query($data);
        $res = file_get_contents($url);
    }

    /**
     * 增加转账记录
     */
    private  function orderadd($params){
        if($data = OrderService::add($params)){
            $amount = $params['amount'];
            $userdata = Db::name('address')->where(['address'=>$params['address'] ])->find();
            Db::name('address')->where(['address'=>$params['address'] ])->update(['trx' => $userdata['trx'] + $amount ]  );
            return true;
        }else{
            return false;
        }

    }


    /**
     * 交易订单列表 
     * @catalog 自动文档/交易订单
     * @title 交易订单 列表
     * @description 交易订单列表的接口
     * @method get
     * @url 接口域名/api/Order/lists
     * @param page 可选 string 页码
     * @return {"code":1,"msg":"ok","data":{"total":"总条数","per_page":"每页记录数","current_page":"当前页面","last_page":"总页码","data":[{"id":"id","txid":"txid","symbol":"symbol","type":"type","amount":"amount","from_address":"from_address","address":"address","add_time":"add_time","status":"状态","pay_callbackurl":"商家页面通知地址","num":"已补发次数","key":"支付渠道密钥","pay_notifyurl":"商家异步通知地址"}]}}
     * @return_param symbol string symbol
     * @remark 这里是备注信息
     * @number 1
     */
    function lists(){
        $params = $this->request->get();
        $data = OrderService::getList($params);
        return json(getJson('ok',1,$data));
    }

    /**
     * 交易订单详情 
     * @catalog 自动文档/交易订单
     * @title 交易订单 详情
     * @description 交易订单详情的接口
     * @method get
     * @url 接口域名/api/Order/detail
     * @param id 必选 string 记录ID
     * @return {"code":1,"msg":"ok","data":{"id":"id","txid":"txid","symbol":"symbol","type":"type","amount":"amount","from_address":"from_address","address":"address","add_time":"add_time","status":"状态","pay_callbackurl":"商家页面通知地址","num":"已补发次数","key":"支付渠道密钥","pay_notifyurl":"商家异步通知地址"}}
     * @return_param id int id
     * @remark 这里是备注信息
     * @number 2
     */
    function detail(){
        $params = $this->request->get();
        //$this->validate($params);//数据效验
        if($data = OrderService::getDetail($params)){
            return json(getJson('ok',1,$data));
        }
        return json(getJson('记录不存在',0,$params));
    }

    





}