---
description: USDT转账接口 Tron_api最新版Tron-trc20-trx-usdt波场接口源码-PHP版本-ThinkPHP6
---

# USDT转账接口


##### 请求URL
- ` https://test.appusdt.xyz/api/trc20/send?apikey=AC8D77F011F05AB4BD2E&address=TANLLpnqPMSuZTYeoe2rCuPw1R6usnHTJH&amount=6&type=usdt `
  
##### 请求方式
- GET 

##### 参数

|参数名|必选|类型|说明|
|:----    |:---|:----- |-----   |
|apikey |是  |string |apikey   |
|type |是  |string |usdt   |
|address |是  |string |需要收款的钱包地址   |
|amount |是  |float |USDT金额   |

##### 返回示例 

```js
{
  "msg": "转账成功",
  "data": {
    "symbol": "usdt",
    "address": "TQme4AecFkR5KxdWGvboiRZT7PWoMPvw4U",
    "amount": 6,
    "user_id": 1,
    "add_time": 1756718865,
    "ip": "127.0.0.1",
    "status": 0,
    "rates": 0.6,
    "total": 6.6,
    "type": 2,
    "id": "18"
  },
  "code": 1
}

```

##### 返回参数说明 

|参数名|类型|说明|
|:-----  |:-----|-----                           |
|code |bool   |这个参数只有为true的时候才算成功 |
|data |string   |交易订单数据 |
|amount |float   | 金额 |
|rates |float   |手续费 |
|total |float   | 总金额 |
|id |int64   | 订单id |
|status |int   |0:提交成完1:处理中2：支付完成 -1:失支付失败 |




