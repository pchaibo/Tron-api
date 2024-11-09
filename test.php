<?php
/**
 * tron sdk
 * https://github.com/iexbase/tron-api
 * 
 * 扫块
 * https://apilist.tronscanapi.com/api/transaction?sort=-timestamp&count=true&limit=20&start=0
 * 
 * u
 * https://apilist.tronscanapi.com/api/transaction?count=true&limit=20&token=TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t
 * 
 */

include_once './vendor/autoload.php';

try {
    $tron = new \IEXBase\TronAPI\Tron();

    $generateAddress = $tron->generateAddress(); // or createAddress()
    $isValid = $tron->isAddress($generateAddress->getAddress());


    echo '地址Address hex: '. $generateAddress->getAddress();
    echo '<br/>Address base58: '. $generateAddress->getAddress(true);
    echo '<br/>Private key: '. $generateAddress->getPrivateKey();
    echo '<br/>Public key: '. $generateAddress->getPublicKey();
    echo '<br/>Is Validate: '. $isValid;
    echo "<pre>";
    echo '<br/>Raw data: ';print_r($generateAddress->getRawData());

} catch (\IEXBase\TronAPI\Exception\TronException $e) {
    echo $e->getMessage();
}