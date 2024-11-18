# USDT转账接口


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

```js
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


