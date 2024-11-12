<?php
/**
 * trx 交易
 * 开启 
 * php.ini
 * gextension=gmp
 * curl.cainfo ="D:/wamp/php/cacert.pem"
 */
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
/**
 * url
 * https://api.trongrid.io
 * https://nile.trongrid.io
 * 
 *查询交易
 * https://api.trongrid.io/v1/accounts/TYPrKF2sevXuE86Xo3Y2mhFnjseiUcybny/transactions/trc20?limit=100&contract_address=TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t
 * https://api.trongrid.io/v1/accounts/TLLM21wteSPs4hKjbxgmH1L6poyMjeTbHm/transactions/trc20?limit=100&contract_address=TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t
 * 
 * 查询余额
 * https://apilist.tronscanapi.com/api/accountv2?address=TK4y9HV3gLyZwKVL4mC3fU9jK8Lspt2tWb
 * 币种余额
 * reun withPriceTokens[]
 * 
 */

include_once './vendor/autoload.php';
/**
 * 地址Address hex: 416f207a2457d127485a5c8d4821443e944e354975
 * Address base58: TL6nuuG1FQ8rVn3wx87kY2vcWT6VCU99XC
 * Private key: 799ba31b040e5568411630e509e1bca2a75843a5f3226a499ba1530be2e0115b
 */
/**
 * TF6G3uUhJGyaVLBzwjV66YaMAXkAvsamKH
 * 41382e15fd316598ddf6c97997ff36b83a4bec5a7c
 * 交易
 * https://nile.trongrid.io/wallet/createtransaction
 * {"to_address": "416f207a2457d127485a5c8d4821443e944e354975", "owner_address": "41D1E7A6BC354106CB410E65FF8B181C600FF14292", "amount": 1000 }
 * 
 */
try {
    //
    $tron = new \IEXBase\TronAPI\Tron();
    
    $hex = $tron->toHex('TF6G3uUhJGyaVLBzwjV66YaMAXkAvsamKH');//TF6G3uUhJGyaVLBzwjV66YaMAXkAvsamKH
    echo "hex:".$hex."<br/>";
    //41e552f6487585c2b58bc2c9bb4492bc1f17132cd0
    // $hex = $tron->fromHex('41e552f6487585c2b58bc2c9bb4492bc1f17132cd0');
    // echo "hex:".$hex."<br/>";

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