---
description: TRX转账查询 Tron_api最新版Tron-trc20-trx-usdt波场接口源码-PHP版本-ThinkPHP5 layui 
---

# TRX转账

##### 请求URL
- ` https://usdt.liangcoin.com/api/trc20/send?apikey=AC8D77F011F05AB4BD2E&address=TQme4AecFkR5KxdWGvboiRZT7PWoMPvw4U&amount=6&type=trx `
  
##### 请求方式
- GET 

##### 参数

|参数名|必选|类型|说明|
|:----    |:---|:----- |-----   |
|apikey |是  |string |apikey   |
|type |是  |string |trx   |
|address |是  |string |需要收款的钱包地址   |
|amount |是  |float |TRX金额   |

##### 返回示例 

```js
{
  "msg": "转账成功",
  "data": {
    "symbol": "trx",
    "address": "TQme4AecFkR5KxdWGvboiRZT7PWoMPvw4U",
    "amount": 6,
    "user_id": 1,
    "add_time": 1756796147,
    "ip": "127.0.0.1",
    "status": 0,
    "rates": 0.6,
    "total": 6.6,
    "type": 1,
    "id": "19"
  },
  "code": 1
}
```

##### 返回参数说明 

|参数名|类型|说明|
|:-----  |:-----|-----                           |
|data |string   |交易哈希，用于异步查询订单交易状态 |
|code |bool   |这个参数只有为true的提交成功 |


