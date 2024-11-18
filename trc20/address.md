# 生成地址接口

##### 请求URL
- ` http://127.0.0.203/api/Address/add`
  
##### 请求方式
- GET 


##### 返回示例 

```js
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


