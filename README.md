
提供波场TRON trc20支付接口代码，扫区块通知回调api
===============
>### 1.运行环境要求PHP7.4
>### 2.安装gmp扩展，否则转账不成功
>### 3.配置项目伪静态
>### 4.合约地址不需要动，切记别改
>### 5.ThinkPHP基本运行要求
## 目前功能
* 生成钱包地址
* 获取USDT余额
* 获取TRX余额
* 资金自动归集
* 查询订单详情
* 私钥获取地址
* 查询最新区块
* 根据区块链查询信息
* USDT交易转账
* TRX交易转账（可添加备注）
* 查询最新交易（USDT）
## 设置
* 设置后台admin模块登陆ip: awebsite.php 
* 后台地址 http://127.0.0.189/manage


------------
## TRC20
------------
##### 简要描述

- 生成地址接口

##### 请求URL
- ` http://xx.com/api/trc20/getAddress/token/`
  
##### 请求方式
- GET 


##### 返回示例 

```
{
    "code": 1,
    "data": {
        "privateKey": "41369219ae69fc11ecb589cdd5fdd9a0248918402bdf4d6fe0a7fb6ca752b38b",
        "address": "THJWy3Ej8gv52nmVfWfHZdyRuWX7Qfi3W6",
        "hexAddress": "41506fc7c4241599326c272d26fd08a47f3957d98b"
    }
}
```

##### 返回参数说明 

|参数名|类型|说明|
|:-----  |:-----|-----                           |
|privateKey |string   |私钥 |
|address |string   |钱包地址 |


------------

##### 简要描述

- 获取USDT余额接口

##### 请求URL
- ` http://xx.com/api/trc20/getAddressBalance?address=TGDsEr2cSRC98Zo9WnwNDik2Y5rdboPRvd `
  
##### 请求方式
- GET 

##### 参数

|参数名|必选|类型|说明|
|:----    |:---|:----- |-----   |
|address |是  |string |需要查询的钱包地址   |

##### 返回示例 

```
{
    "code": 1,
    "data": "0.00543"
}
```

##### 返回参数说明 

|参数名|类型|说明|
|:-----  |:-----|-----                           |
|data |float   |钱包USDT余额 |



------------

## 安装
* Telegram: @syga22 
* 邮箱:cqhaibo800@gmail.com