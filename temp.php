<?php

include_once "lib/Snoopy.class.php";


//$s = new Snoopy();
//
//$s->proxy_host = '58.218.200.223';
//$s->proxy_port = '30275';
//
//$s->fetch("http://myip.ipip.net/");
//print_r($s->getResults());
//
//echo "111";
$code_file_name = './code.txt';


//while (true) {
//    $file = fopen($code_file_name, 'w');
//    if (!flock($file, LOCK_EX )) {
//        有锁
//        echo "lock \n";
//    } else {
//        echo "no lock \n ";
//    }
//    fclose($file);
//    sleep(1);
//}


$code_file_name  = './code.txt';

$file = fopen($code_file_name, 'w');

flock($file,LOCK_EX);

sleep(5);

flock($file,LOCK_UN);

fclose($file);