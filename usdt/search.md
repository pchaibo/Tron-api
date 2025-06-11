---
description: TxId交易哈希 Tron_api最新版Tron-trc20-trx-usdt波场接口源码-PHP版本-ThinkPHP5 layui 
---

# TxId交易哈希

##### 请求URL
- ` https://test.appusdt.xyz/api/trx/find?txid=14c59c250b91d54a928887928fb2242608b6bd4f24c3c83cddd72d18b0c04026 `
  
##### 请求方式
- GET 


##### 返回示例 

```js
{
  "msg": "记录查询成功",
  "data": {
    "ret": [
      {
        "contractRet": "SUCCESS"
      }
    ],
    "signature": [
      "0e349d6b809345566458a1827648358b26df9286abc094e4bf0480132823609c7bf272540c4d29f2b25cbb38066ac27c6256185383ba61a361eed16f96e1825c00"
    ],
    "txID": "14c59c250b91d54a928887928fb2242608b6bd4f24c3c83cddd72d18b0c04026",
    "raw_data": {
      "contract": [
        {
          "parameter": {
            "value": {
              "data": "a9059cbb000000000000000000000041045fb1e5336ac98ea9a1a91404ef1827d767790400000000000000000000000000000000000000000000000024150e3980040000",
              "owner_address": "41382e15fd316598ddf6c97997ff36b83a4bec5a7c",
              "contract_address": "4137349aeb75a32f8c4c090daff376cf975f5d2eba"
            },
            "type_url": "type.googleapis.com/protocol.TriggerSmartContract"
          },
          "type": "TriggerSmartContract"
        }
      ],
      "ref_block_bytes": "b7e3",
      "ref_block_hash": "eeb8abdfc99bb118",
      "expiration": 1731639330000,
      "fee_limit": 10000000,
      "timestamp": 1731639271451
    },
    "raw_data_hex": "0a02b7e32208eeb8abdfc99bb11840d089fdedb2325aae01081f12a9010a31747970652e676f6f676c65617069732e636f6d2f70726f746f636f6c2e54726967676572536d617274436f6e747261637412740a1541382e15fd316598ddf6c97997ff36b83a4bec5a7c12154137349aeb75a32f8c4c090daff376cf975f5d2eba2244a9059cbb000000000000000000000041045fb1e5336ac98ea9a1a91404ef1827d767790400000000000000000000000000000000000000000000000024150e3980040000709bc0f9edb232900180ade204"
  },
  "code": 1
}

```

##### 返回参数说明 

|参数名|类型|说明|
|:-----  |:-----|-----                           |
|contractRet |string   |SUCCESS：表示转账成功 |



