
# 提供波场TRON api 接口  trc20 usdt 支付接口代码，扫区块通知回调api
# Tron-api-trx-trc20-tron-usdt
- Tron-api-Tron接口源码-PHP版-ThinkPHP，如果你想得到这个源码，你可以通过Telegram（https ://t.me/zhang8080）或者email（cqhaibo800@gmail.com）联系我获取源码，只要部署成功，就可以通过api调用所有接口,重点解决：生成钱包地址，USDT交易转账，扫区块链对地址异步通知。

## tronapi:当前最新版Tron-api-波场接口源码-PHP版本
## 文档地址：https://pchaibo.github.io/Tron-api
===============
>### 1.运行环境要求PHP7.4
>### 2.安装gmp扩展，否则转账不成功
>### 3.配置项目伪静态
>### 4.ThinkPHP5基本运行要求
## 重点解决功能
* 生成钱包地址

* USDT交易转账

* 扫区块链对Trx地址Trc20(USDT)异步通知回调

## 目前功能
* 生成钱包地址
* 获取USDT余额
* 获取TRX余额
* 资金自动归集
* 查询订单详情
* 私钥获取地址
* 查询最新区块
* USDT交易转账
* TRX交易转账
* 查询最新交易（USDT）
* 扫区块链对地址异步通知

------------

## 安装联系
* address：TSPuEaWv1JM51r1yznPRRa9yPJRz96aHYA
* Telegram: @zhang8080
* qq :386378183
* 邮箱:cqhaibo800@gmail.com
------------
## 后台显示
### USDT（300~USDT）

##### address：TSPuEaWv1JM51r1yznPRRa9yPJRz96aHYA


![image](https://github.com/pchaibo/Tron-api/blob/master/address.png)

* 地址列表

![image](https://github.com/pchaibo/usdtpay/blob/master/add.png)

* 转账记录

![image](https://github.com/pchaibo/usdtpay/blob/master/order.png)
------------
## TRC20
------------
##### 简要描述

- 生成地址接口

##### 请求URL
- ` http://127.0.0.203/api/Address/add`
  
##### 请求方式
- GET 


##### 返回示例 

```
{
     "msg": "新增成功",
     "data": {
          "address": "TDJWn6GRCDGfF4Xphg1b4LETbUAUtkZ93F",
          "hex": "41248f1ea08809106a0a0a18eb369b0fee4f2f82be",
          "key": "95de772bb721cb7bb140f1f3c16c8a8c7efeef4c4d3d4f3f4c52881865712ba3"
     },
     "code": 1
}
```

##### 返回参数说明 

|参数名|类型|说明|
|:-----  |:-----|-----                           |
|Key |string   |私钥 |
|address |string   |钱包地址 |


------------

##### 简要描述

- 获取USDT余额接口

##### 请求URL
- ` http://127.0.0.203/api/trc20/AddressBalance?address=TChFoH1BGwyRQbtouijE2BBCYjrCQfM3VV `
  
##### 请求方式
- GET 

##### 参数

|参数名|必选|类型|说明|
|:----    |:---|:----- |-----   |
|address |是  |string |需要查询的钱包地址   |

##### 返回示例 

```
{
  "msg": "ok",
  "data": {
    "amount": "977.700"
  },
  "code": 1
}
```

##### 返回参数说明 

|参数名|类型|说明|
|:-----  |:-----|-----                           |
|amount |float   |钱包USDT余额 |


------------

- USDT转账接口

##### 请求URL
- ` http://127.0.0.203/api/trc20/send?from_address=TF6G3uUhJGyaVLBzwjV66YaMAXkAvsamKH&address=TANLLpnqPMSuZTYeoe2rCuPw1R6usnHTJH&amount=3.2 `
  
##### 请求方式
- GET 

##### 参数

|参数名|必选|类型|说明|
|:----    |:---|:----- |-----   |
|address |是  |string |需要转账的钱包地址   |
|from_address |是  |string |收款到账的钱包地址   |
|amount |是  |float |USDT金额   |

##### 返回示例 

```
{
  "msg": "转账成功",
  "data": {
    "from_address": "TF6G3uUhJGyaVLBzwjV66YaMAXkAvsamKH",
    "address": "TANLLpnqPMSuZTYeoe2rCuPw1R6usnHTJH",
    "amount": 3.2,
    "txid": "02fcb79bc528b2647e8bcc0cd8242c1843cad512b3daedade567cd5eeba69604",
    "symbol": "usdt",
    "type": 2,
    "add_time": 1731656831,
    "transfer": {
      "result": true,
      "txid": "02fcb79bc528b2647e8bcc0cd8242c1843cad512b3daedade567cd5eeba69604",
      "visible": false,
      "txID": "02fcb79bc528b2647e8bcc0cd8242c1843cad512b3daedade567cd5eeba69604",
      "raw_data": {
        "contract": [
          {
            "parameter": {
              "value": {
                "data": "a9059cbb000000000000000000000041045fb1e5336ac98ea9a1a91404ef1827d76779040000000000000000000000000000000000000000000000002c68af0bb1400000",
                "owner_address": "41382e15fd316598ddf6c97997ff36b83a4bec5a7c",
                "contract_address": "4137349aeb75a32f8c4c090daff376cf975f5d2eba"
              },
              "type_url": "type.googleapis.com/protocol.TriggerSmartContract"
            },
            "type": "TriggerSmartContract"
          }
        ],
        "ref_block_bytes": "cdd4",
        "ref_block_hash": "748e608dfc8f1409",
        "expiration": 1731656889000,
        "fee_limit": 10000000,
        "timestamp": 1731656829151
      },
      "raw_data_hex": "0a02cdd42208748e608dfc8f140940a8e5acf6b2325aae01081f12a9010a31747970652e676f6f676c65617069732e636f6d2f70726f746f636f6c2e54726967676572536d617274436f6e747261637412740a1541382e15fd316598ddf6c97997ff36b83a4bec5a7c12154137349aeb75a32f8c4c090daff376cf975f5d2eba2244a9059cbb000000000000000000000041045fb1e5336ac98ea9a1a91404ef1827d76779040000000000000000000000000000000000000000000000002c68af0bb140000070df91a9f6b232900180ade204",
      "signature": [
        "4abd7275c9c0007e56a6363f864ec1c27bd96d5d9faddcfbdc5b8122f095e31763680421a4eec488fc6bf1de072df0d0dda0d1bd9bc5981b77f44d63c6e2141100"
      ]
    }
  },
  "code": 1
}

```

##### 返回参数说明 

|参数名|类型|说明|
|:-----  |:-----|-----                           |
|txid |string   |交易哈希，用于异步查询订单交易状态 |
|result |bool   |这个参数只有为true的时候才算成功 |



