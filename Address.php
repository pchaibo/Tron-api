<?php


namespace app\api\controller;
use IEXBase\TronAPI\Tron;

use app\api\services\AddressService;

class Address extends Base
{
  
    /**
     * TRX地址详情 
     * @catalog 自动文档/TRX地址
     * @title TRX地址 详情
     * @description TRX地址详情的接口
     * @method get
     * @url 接口域名/api/Address/detail
     * @param id 必选 string 记录ID
     * @return {"code":1,"msg":"ok","data":{"id":"id","address":"地址base58","hex":"hex","key":"key","trx":"trx","usdt":"usdt","trx_old":"trx_old","usdt_old":"usdt_old","notifyurl":"商家异步通知地址","callbackurl":"商家页面通知地址","num":"已补发次数","create_time":"创建时间","update_time":"更新时间","status":"status"}}
     * @return_param id int id
     * @return_param address string 地址base58
     * @remark 这里是备注信息
     * @number 2
     */
    function detail(){
        $params = $this->request->get();
        $this->validate($params);//数据效验
        if($data = AddressService::getDetail($params)){
            return json(getJson('ok',1,$data));
        }
        return json(getJson('记录不存在',0,$params));
    }

    /**
     * TRX地址新增数据 
     * @catalog 自动文档/TRX地址
     * @title TRX地址 新增
     * @description TRX地址新增的接口
     * @method get
     * @url 接口域名/api/Address/add
     * @param address 必选 string 地址base58 
     * @return {"msg":"新增成功","data":{"address":"TW1kcbnaqTAWaTMF6McxpyWY2LtNaU55c1","hex":"41dbddbc935fb91c04b7aa4f870fa5cb9d25040f10","key":"c1ae1d64ef114067916249e7b6177620b36c305233aa708d2e0b610f5ad50caa"},"code":1}
     * @remark 这里是备注信息
     * @number 3
     */
    function add(){
        //生成地址
        $tron = new tron();
        $adddata =array();
        $createAddress = $tron->generateAddress();
        $adddata['address'] =  $createAddress->getAddress(true);//T
        $adddata['hex'] = $createAddress->getAddress();//41
        $adddata['key'] = $createAddress->getPrivateKey();
        $params = $adddata;
        $this->validate($params);//数据效验
        if($data = AddressService::add($params)){
            return json(getJson('新增成功',1,$adddata));
        }else{
            return json(getJson('新增失败'));
        }
    }





}