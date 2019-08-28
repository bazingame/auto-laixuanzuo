<?php
include_once "./lib/Snoopy.class.php";

$base_url = 'https://static.wechat.laixuanzuo.com/template/theme2/cache/layout/';
$snoopy = new Snoopy();

$name = file_get_contents("res.json");
$name_arr = json_decode($name, 1);
foreach ($name_arr as $val) {
    $file_name = $val . '.js';
    $snoopy->fetch($base_url . $file_name);
    $content = $snoopy->getResults();

    $file_path = './js/' . $file_name;
    if (is_file($file_path)) {
        echo $val . "------find\n";
        continue;
    }

    $myfile = fopen($file_path, "w");

    fwrite($myfile, $content);

    echo $val . "-----download\n";
}