
# 提供波场TRON api 接口  trc20 usdt 支付接口代码，扫区块通知回调api
# Tron-api-trx-trc20-tron-usdt-trc10
- Tron-api-Tron interface source code-PHP version-ThinkPHP5-layui version. If you want to get this source code, you can transfer USDT (300U) to me through your tron ​​wallet (myaddress: TSPuEaWv1JM51r1yznPRRa9yPJRz96aHYA), and then you can contact me through Telegram (https://t.me/zhang8080) or email (cqhaibo800@gmail.com) or QQ (386378183@gmail.com) to get the source code. As long as the deployment is successful, all interfaces can be called through the api, focusing on: generating wallet addresses, USDT transaction transfers, scanning blockchains for asynchronous notifications of addresses.
- Tron-api-Tron接口源码-PHP版-ThinkPHP5-layui版，如果你想得到这个源码，你可以通过你的tron钱包（myaddress：TSPuEaWv1JM51r1yznPRRa9yPJRz96aHYA）给我转USDT（300U），然后你可以通过Telegram（https ://t.me/zhang8080）或者email（cqhaibo800@gmail.com）或者qq（386378183@gmail.com）联系我获取源码，只要部署成功，就可以通过api调用所有接口,重点解决：生成钱包地址，USDT交易转账，扫区块链对地址异步通知。

# tronapi:当前最新版Tron-api-波场接口源码-PHP版本-ThinkPHP5 版本。
### 温馨提示：本仓库代码为本人维护的最新代码，区别于别的仓库的代码，请勿买盗版，盗版有后门概不负责。
===============
>### 1.运行环境要求PHP7.4
>### 2.安装gmp扩展，否则转账不成功
>### 3.配置项目伪静态
>### 4.ThinkPHP5基本运行要求
## 重点解决功能
* 生成钱包地址

* USDT交易转账

* 扫区块链对地址异步通知回调

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
* Telegram: @zhang8080
* qq :386378183
* 邮箱:cqhaibo800@gmail.com
------------
## 后台显示

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



