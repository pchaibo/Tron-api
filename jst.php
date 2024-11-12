<?php
/***
 * jst 测试币
 */

include_once './vendor/autoload.php';
$url ="https://nile.trongrid.io";

use IEXBase\TronAPI\Tron;

try {
    $fullNode = new \IEXBase\TronAPI\Provider\HttpProvider($url);
    $solidityNode = new \IEXBase\TronAPI\Provider\HttpProvider($url);
    $eventServer = new \IEXBase\TronAPI\Provider\HttpProvider($url);
} catch (\IEXBase\TronAPI\Exception\TronException $e) {
    echo $e->getMessage();
}


try {
    $tron = new Tron($fullNode, $solidityNode, $eventServer, null, null);
    //$contract = $tron->contract('TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t');  // Tether USDT https://tronscan.org/#/token20/TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t
    $contract = $tron->contract('TF17BgPaZYbz8oxbjhriubPDsA7ArKoLX3');  //  jst https://tronscan.org/#/token20/TF17BgPaZYbz8oxbjhriubPDsA7ArKoLX3
    // Data
    echo $contract->name();
    echo "<br/>";
    echo $contract->symbol();
    echo "<br/>";
    echo $contract->balanceOf('TL6nuuG1FQ8rVn3wx87kY2vcWT6VCU99XC');
    echo "<br/>";
   // echo $contract->totalSupply();
    $tron->setPrivateKey('eb03b56b307ebfaba853aae10015a672d73bd572799f3d7d45d0232ba9838952');
    // $res = $contract->transfer('TL6nuuG1FQ8rVn3wx87kY2vcWT6VCU99XC', '3', 'TF6G3uUhJGyaVLBzwjV66YaMAXkAvsamKH');
    // print_r($res);


} catch (\IEXBase\TronAPI\Exception\TronException $e) {
    echo $e->getMessage();
}

