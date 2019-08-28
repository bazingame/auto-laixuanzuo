<?php


//写入文件
//$file = fopen('./code.txt', 'w');
//fwrite($file, "aaaa");

$file = fopen('./code.txt','r');
$code = fread($file,filesize('./code.txt'));

print_r($code);