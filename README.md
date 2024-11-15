
提供波场TRON api 接口  trc20 usdt 支付接口代码，扫区块通知回调api
===============
>### 1.运行环境要求PHP7.4
>### 2.安装gmp扩展，否则转账不成功
>### 3.配置项目伪静态
>### 4.合约地址不需要动，切记别改
>### 5.ThinkPHP5基本运行要求
## 重点功能
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
## 设置
* 
* 后台地址 http://127.0.0.203/admin


------------

## 安装联系
* Telegram: @zhang8080
* qq :386378183
* 邮箱:cqhaibo800@gmail.com

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



