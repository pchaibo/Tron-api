---
description: TRX转账查询 Tron_api最新版Tron-trc20-trx-usdt波场接口源码-PHP版本-ThinkPHP5 layui 
---

# TRX转账

##### 请求URL
- ` http://127.0.0.203/api/trx/send?from_address=TF6G3uUhJGyaVLBzwjV66YaMAXkAvsamKH&address=TSmqHU26sgNFBxkx13QBEPGwUeoCJZs7Hp&amount=2.6 `
  
##### 请求方式
- GET 

##### 参数

|参数名|必选|类型|说明|
|:----    |:---|:----- |-----   |
|address |是  |string |需要转账的钱包地址   |
|from_address |是  |string |收款到账的钱包地址   |
|amount |是  |float |USDT金额   |

##### 返回示例 

```js
{
  "msg": "转账成功",
  "data": {
    "from_address": "TF6G3uUhJGyaVLBzwjV66YaMAXkAvsamKH",
    "address": "TSmqHU26sgNFBxkx13QBEPGwUeoCJZs7Hp",
    "amount": 2.6,
    "url": "https://nile.trongrid.io",
    "txid": "27f64adcd86a18fcb223497736a5d515d7f6e76051a9ff04c159ecb81d6adc79",
    "symbol": "trx",
    "type": 1,
    "add_time": 1731748010,
    "transfer": {
      "result": true,
      "txid": "27f64adcd86a18fcb223497736a5d515d7f6e76051a9ff04c159ecb81d6adc79",
      "visible": false,
      "txID": "27f64adcd86a18fcb223497736a5d515d7f6e76051a9ff04c159ecb81d6adc79",
      "raw_data": {
        "contract": [
          {
            "parameter": {
              "value": {
                "amount": 2600000,
                "owner_address": "41382e15fd316598ddf6c97997ff36b83a4bec5a7c",
                "to_address": "41b8536f28b11e2035b6d34d668278ce78c34e3e17"
              },
              "type_url": "type.googleapis.com/protocol.TransferContract"
            },
            "type": "TransferContract"
          }
        ],
        "ref_block_bytes": "3fc9",
        "ref_block_hash": "e3423fca3be30114",
        "expiration": 1731748065000,
        "timestamp": 1731748007349
      },
      "raw_data_hex": "0a023fc92208e3423fca3be3011440e8dde9a1b3325a68080112640a2d747970652e676f6f676c65617069732e636f6d2f70726f746f636f6c2e5472616e73666572436f6e747261637412330a1541382e15fd316598ddf6c97997ff36b83a4bec5a7c121541b8536f28b11e2035b6d34d668278ce78c34e3e1718c0d89e0170b59be6a1b332",
      "signature": [
        "6de9a8ab5f952503636707382faf652c02d8f5acb910dd9305f610d1187573f05f23f47a0b107fea2840992c0b76a0eedf8e4dcc5b38801f987c18e05a97020001"
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
|result |bool   |这个参数只有为true的提交成功 |


