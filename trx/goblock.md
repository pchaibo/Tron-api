---
description: 扫最新区块golang Tron_api最新版Tron-trc20-trx-usdt波场接口源码-PHP版本-ThinkPHP6
---

# 扫最新区块golang

* 使用golang自动扫区块交易数据
* 能量租借使用 api
* 转账支付：TRX和USDT
* 自动归集：TRX和USDT
* 配置异步通代码文件 ./gotrxrpc/.env


```bash

## key
TRONAPI_KEY = ef5a068b-1d99-4388-b589-239a3d13fa57
#站点域名
Stie = http://127.0.0.190

#能量api
CatfeeURL =https://api.catfee.io
CatfeeAPIKey = 4c6442fe-1b83-4a7e-9278-e5361a1f83e4
CatfeeAPISecret = e2165bacd0c1582fcc05c926479d1a5d

#归集最低金额
AmountUsdt = 10
AmountTrx = 10

#归集时到trx做手续费
FeesAmount =1

#usdt归集地址
Totaladd="TF6G3uUhJGyaVLBzwjV66YaMAXkAvsamKH"
Totalkey="eb03b56b307ebfaba853aae10015dddddddddddddddddd"
##mysql
Mysqldns ="root:root@tcp(127.0.0.1:3306)/dbname?charset=utf8mb4"

```

### 程序运行  
* 安装golang环境

```bash

cd ./goblock
go build -o goblock .
nohup  ./goblock >/dev/null 2>&1 &

```
#### 自己喜欢可以更改原代码