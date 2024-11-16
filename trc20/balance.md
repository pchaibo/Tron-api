# 获取USDT余额接口


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


