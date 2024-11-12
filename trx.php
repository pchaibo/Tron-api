<?php
/**
 * trx 交易
 * 开启 
 * php.ini
 * gextension=gmp
 * curl.cainfo ="D:/wamp/php/cacert.pem"
 */
include_once './vendor/autoload.php';
$url ="https://nile.trongrid.io";

$fullNode = new \IEXBase\TronAPI\Provider\HttpProvider($url);
$solidityNode = new \IEXBase\TronAPI\Provider\HttpProvider($url);
$eventServer = new \IEXBase\TronAPI\Provider\HttpProvider($url);

try {
    $tron = new \IEXBase\TronAPI\Tron($fullNode, $solidityNode, $eventServer);
} catch (\IEXBase\TronAPI\Exception\TronException $e) {
    exit($e->getMessage());
}

$tron->setAddress('TF6G3uUhJGyaVLBzwjV66YaMAXkAvsamKH');
$tron->setPrivateKey('eb03b56b307ebfaba853aae10015a672d73bd572799f3d7d45d0232ba9838952');

try {
   // print_r($tron);
    $transfer = $tron->send( 'TL6nuuG1FQ8rVn3wx87kY2vcWT6VCU99XC', 4);
} catch (\IEXBase\TronAPI\Exception\TronException $e) {
    die($e->getMessage());
}

echo "<pre/>";
print_r($transfer);