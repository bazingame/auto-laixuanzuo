<?php
include_once "lib/Snoopy.class.php";

$snoopy = new Snoopy();

$url = 'https://wechat.laixuanzuo.com/index.php/reserve/index.html';

$snoopy->cookies = [
    'ROM_TYPE'=>'weixin',
    'wechatSESS_ID'=>'6ce99c7976f643fbe7dfff9928757772',
    'Hm_lpvt_7838cef374eb966ae9ff502c68d6f098'=>'1560335539',
    'FROM_TYPE' => 'weixin',
    'Hm_lpvt_7838cef374eb966ae9ff502c68d6f098' => '1561331338',
    'Hm_lvt_7838cef374eb966ae9ff502c68d6f098' => '1560055230'
    ];

$snoopy->agent = 'Mozilla/5.0 (Linux; Android 5.0; SM-G900P Build/LRX21T) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/33.0.0.0 Mobile Safari/537.36 MicroMessenger/6.0.0.54_r849063.501 NetType/WIFI';
$snoopy->referer = 'https://wechat.laixuanzuo.com/index.php/reserve/index.html';

$all = file_get_contents('./res.json');

$num = 100;

$name = json_decode($all,1);
while ($num--) {
    $snoopy->fetch($url);

    $res = $snoopy->getResults();
    preg_match('/\/layout\/(.*?)\.js\"/',$res,$match);

    if(in_array($match[1],$name)){
        echo $match[1]."----find\n";
    } else {
        $name[] = $match[1];
        echo $match[1]."----new\n";
    }
}

file_put_contents('./res.json',json_encode($name));