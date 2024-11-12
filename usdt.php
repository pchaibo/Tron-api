<?php
/**
 * trx 交易
 * 开启 
 * php.ini
 * gextension=gmp
 * curl.cainfo ="D:/wamp/php/cacert.pem"
 */
include_once './vendor/autoload.php';

use IEXBase\TronAPI\Tron;


try {
    $fullNode = new \IEXBase\TronAPI\Provider\HttpProvider('https://api.trongrid.io');
    $solidityNode = new \IEXBase\TronAPI\Provider\HttpProvider('https://api.trongrid.io');
    $eventServer = new \IEXBase\TronAPI\Provider\HttpProvider('https://api.trongrid.io');
} catch (\IEXBase\TronAPI\Exception\TronException $e) {
    echo $e->getMessage();
}


try {
    $tron = new Tron($fullNode, $solidityNode, $eventServer, null, null);
    $contract = $tron->contract('TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t');  // Tether USDT https://tronscan.org/#/token20/TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t

    // Data
    echo $contract->name();
    echo $contract->symbol();
    //查询
    echo $contract->balanceOf('TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t');
    echo "<br/>";
    //echo $contract->totalSupply();
    //echo  $contract->transfer('to', 'amount', 'from');
    //设置key
    $tron->setPrivateKey('eb03b56b307ebfaba853aae10015a672d73bd572799f3d7d45d0232ba9838952');
    //$res = $contract->transfer('TL6nuuG1FQ8rVn3wx87kY2vcWT6VCU99XC', '3', 'TF6G3uUhJGyaVLBzwjV66YaMAXkAvsamKH');
    //print_r($res);


} catch (\IEXBase\TronAPI\Exception\TronException $e) {
    echo $e->getMessage();
}

