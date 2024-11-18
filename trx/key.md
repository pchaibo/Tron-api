---
description: 波场key设置 Tron_api最新版Tron-trc20-trx-usdt波场接口源码-PHP版本-ThinkPHP5 layui 
---

# 波场key设置

### 以下是apikey的调整的地方，扫描TRX区块设置KEY，不设置后面量大了可能有限制，[官方文档](https://developers.tron.network/reference/background#note)

### 申请地址：https://www.trongrid.io/

#### 修改config/trx.php里key
### url改成:https://api.trongrid.io

```php

<?php
return array (
    'key' =>'11111',
    'url'=>'https://nile.trongrid.io',
    'type'=>array('trx'=>1,'usdt'=>2 ,'jst'=>3),
);

?>
```


