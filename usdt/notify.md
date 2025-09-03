---
description: Tron_api最新版Tron-trc20-trx-usdt波场接口源码-PHP版本-ThinkPHP6  异步通知数据
---
# 异步通知数据

##### 返回您设置网站URL
- ` http://127.0.0.190/api.php`
  
##### 请求方式
- POST 


##### 返回示例 

```js
 
{
"txid": "a59f461e4d1ba82bd5e795f25147cbeea328892af8aff85b859f26c00dd8f24b",
"symbol": "usdt",
"address": "TQme4AecFkR5KxdWGvboiRZT7PWoMPvw4U",
"from_address": "TF6G3uUhJGyaVLBzwjV66YaMAXkAvsamKH",
"amount": "20.000",
"add_time": "1756718765",
"id": "104"
}

```

##### 返回参数说明 

|参数名|类型|说明|
|:-----  |:-----|-----                           |
|txid |string   |区块id |
|symbol |string   |类型 |
|address |string   |收款地址 |
|from_address |string   |支付地址 |
|amount |float   |金额 |
|add_time |int64   |订单时间 |
|id |int64   |订单id |


