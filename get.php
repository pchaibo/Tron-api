<?php
//查询余额
$url ="https://apilist.tronscanapi.com/api/accountv2?address=TTkiCcxWRX8op8dfxz1AA2ugfx11111111";
$data = file_get_contents($url);
$data  = json_decode($data,true);
print_r($data['withPriceTokens']);