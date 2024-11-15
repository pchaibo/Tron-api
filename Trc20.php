<?php


namespace app\api\controller;


use app\api\services\OrderService;
use think\facade\Db;
use IEXBase\TronAPI;
use think\facade\Request;

class Trc20 extends Base
{
    private $geturl ="https://api.trongrid.io";
    
   
    /**
     * 查询trx余额
     * http://127.0.0.203/api/trc20/AddressBalance?address=TChFoH1BGwyRQbtouijE2BBCYjrCQfM3VV
     * address 必选 string address 
     * return {"msg":"ok","data":{"amount":"983.500"},"code":1}
     */
    function AddressBalance(){
        $conifg = Config('trx');
        $geturl = new TronAPI\Provider\HttpProvider($this->geturl);
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
        //合约地址
        $contract = $tron->contract('TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t');  // Tether USDT https://tronscan.org/#/token20/TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t
       // $contract = $tron->contract('TF17BgPaZYbz8oxbjhriubPDsA7ArKoLX3');  //  jst https://tronscan.org/#/token20/TF17BgPaZYbz8oxbjhriubPDsA7ArKoLX3
        $balance = $contract->balanceOf($address);
        $balance =sprintf("%.03f", $balance);
        $data =array('amount'=>$balance);
        return json(getJson('ok',1,$data));

    }

    /**
     * USDT转账 
     * @catalog 自动文档/交易订单
     * @title USDT转账
     * @description USDT转账的接口
     * @method post
     * @url 接口域名/api/trc20/send
     * @data http://127.0.0.203/api/trc20/send?from_address=TF6G3uUhJGyaVLBzwjV66YaMAXkAvsamKH&address=TANLLpnqPMSuZTYeoe2rCuPw1R6usnHTJH&amount=3.2
     * @param amount 必选 string amount 
     * @param from_address 必选 string from_address 
     * @param address 必选 string address 
     * @return {"msg":"转账成功","data":{"from_address":"TF6G3uUhJGyaVLBzwjV66YaMAXkAvsamKH","address":"TANLLpnqPMSuZTYeoe2rCuPw1R6usnHTJH","amount":3.2,"url":"https:\/\/nile.trongrid.io","txid":"c4f6720f754519adb734d70dffaa745fe9b87ad2a301bfd049fd0147182f00b4","symbol":"usdt","type":2,"add_time":1731639507,"transfer":{"result":true,"txid":"c4f6720f754519adb734d70dffaa745fe9b87ad2a301bfd049fd0147182f00b4","visible":false,"txID":"c4f6720f754519adb734d70dffaa745fe9b87ad2a301bfd049fd0147182f00b4","raw_data":{"contract":[{"parameter":{"value":{"data":"a9059cbb000000000000000000000041045fb1e5336ac98ea9a1a91404ef1827d76779040000000000000000000000000000000000000000000000002c68af0bb1400000","owner_address":"41382e15fd316598ddf6c97997ff36b83a4bec5a7c","contract_address":"4137349aeb75a32f8c4c090daff376cf975f5d2eba"},"type_url":"type.googleapis.com\/protocol.TriggerSmartContract"},"type":"TriggerSmartContract"}],"ref_block_bytes":"b82e","ref_block_hash":"6d85e1dabd436658","expiration":1731639564000,"fee_limit":10000000,"timestamp":1731639505562},"raw_data_hex":"0a02b82e22086d85e1dabd43665840e0ad8beeb2325aae01081f12a9010a31747970652e676f6f676c65617069732e636f6d2f70726f746f636f6c2e54726967676572536d617274436f6e747261637412740a1541382e15fd316598ddf6c97997ff36b83a4bec5a7c12154137349aeb75a32f8c4c090daff376cf975f5d2eba2244a9059cbb000000000000000000000041045fb1e5336ac98ea9a1a91404ef1827d76779040000000000000000000000000000000000000000000000002c68af0bb1400000709ae587eeb232900180ade204","signature":["70a44981cb8af3d10b24348338827b5dfb3b268fca96a8484a41b19b740ec0e5981fac5fb37db64ea39379d58838a2ca1b9c2390604d766453f2f371479753f000"]}},"code":1}
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
        $params['url'] = $this->geturl;
        //print_r($params);
        $transfer = $this->concarttransfer($params);//调用转账
        if( isset($transfer['code'])){
            return json(getJson('转账失败',0,$transfer));
        }

        $params['txid'] = $transfer['txid'];
        $params['symbol'] = 'usdt';//类型
        $params['type'] = $type['usdt'];//类型id 1:trx 2:usdt 3:jst
        
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
     * 合约转账
     * $data['from_address'];
     * $data['key'];
     * $data['address'];
     * $data['amount'];
     * $data['key'];
     * $data['url'];
     */
    function concarttransfer(array $data):array{
        $url =$data['url'];
        $fullNode = new TronAPI\Provider\HttpProvider($url);
        $solidityNode =  $fullNode;
        $eventServer =  $fullNode;
        try {
            $tron = new TronAPI\Tron($fullNode, $solidityNode, $eventServer);
        } catch (TronAPI\Exception\TronException $e) {
            return $e->getMessage();
        }
        //合约地址
        $contract = $tron->contract('TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t');  // Tether USDT https://tronscan.org/#/token20/TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t
        //$contract = $tron->contract('TF17BgPaZYbz8oxbjhriubPDsA7ArKoLX3');  //  jst https://tronscan.org/#/token20/TF17BgPaZYbz8oxbjhriubPDsA7ArKoLX3
        $tron->setPrivateKey($data['key']);
        try {
            $transfer = $contract->transfer($data['address'],$data['amount'], $data['from_address']);
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
     * @url 接口域名http://127.0.0.203/api/trc20/notify?from_address=TF6G3uUhJGyaVLBzwjV66YaMAXkAvsamKH&address=TChFoH1BGwyRQbtouijE2BBCYjrCQfM3VV&txid=82225222&amount=5
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
        $params['type'] = $type['usdt'];
        $params['symbol'] = 'usdt';
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
            Db::name('address')->where(['address'=>$params['address'] ])->update(['usdt' => $userdata['usdt'] + $amount ]  );
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