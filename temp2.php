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
$code_file_name  = './code.txt';

//$file = fopen($code_file_name, 'w');
//
//flock($file,LOCK_EX);
//
//sleep(10);
//
//flock($file,LOCK_UN);
//
//fclose($file);

$file = fopen($code_file_name, 'w');
if(flock($file , LOCK_EX)){
//    Log::info("wait for code.txt unlock");
    echo "wait for ...";
//    $this->get_code();
//    return;
} else {
    echo "wait for 2222...";
}
fclose($file);

