<?php
require_once('encodewrapper.php');

$wrapper = new EncodeWrapper('');

$str1 = '<script attr="value1" onerror="value2></script>"';
$str2 = '<script/>';
$str3 = '<script></script>';

echo $wrapper->purify($str1);
echo "\r\n";
echo $wrapper->purify($str2);
echo "\r\n";
echo $wrapper->purify($str3);
echo "\r\n";
?>
