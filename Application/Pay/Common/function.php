<?php

//返回sign;
function createSign($data,$key) {
    $signPars = "";
    ksort($data);
    foreach($data as $k => $v) {
        if("" != $v && "sign" != $k) {
            $signPars .= $k . "=" . $v . "&";
        }
    }
    $signPars .= "key=" .$key;
    $sign = strtoupper(md5($signPars));
    return $sign;
}
 

function http_post_json($url, $jsonStr)
{
    //启动一个CURL会话
    $ch = curl_init();
    // 设置curl允许执行的最长秒数
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
    // 获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    //发送一个常规的POST请求。
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    //要传送的所有数据
    curl_setopt($ch, CURLOPT_POSTFIELDS,$jsonStr);
    // 执行操作
    $res = curl_exec($ch);
    return $res;
}

/*
 * 返回数据
 */
function userjson($data){
    echo json_encode($data,320);
    exit;
}
 
