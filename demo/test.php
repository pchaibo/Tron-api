<?php

include'phpqrcode.php';
header('Content-Type: image/png');
$str = base64_decode($_GET['s']);//解密处理
QRcode::png($str, false, 'L', 9, 2);