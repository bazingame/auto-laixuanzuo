<?php

$name = file_get_contents("res.json");
$name_arr = json_decode($name,1);

$final = [];
foreach ($name_arr as $val) {

    $file_name = $val.'.js';

    $js = file_get_contents("./js/".$file_name);


    if (preg_match('/0123456789ABCD/',$js)){
        preg_match('/dec\(\"(.*?)\"\)/',$js,$matches);
        $var_name = $matches[1];
        preg_match('/\+(\w)\.dec/',$js,$match);
        $function_name = $match[1];

        // console.log(e.dec("OFoiabsyXocpAxbXzo9XK7"))
        $res = preg_replace('/\w\.ajax_get.*?g\)\}\)/','console.log('.$function_name.'.dec("'.$var_name.'"))',$js);
        $res .=  'reserve_seat()';
    } else {
        preg_match('/var (\w{1})\=func/',$js,$matches);
        $function_name = $matches[1];
        
        preg_match('/\;(\w{10})\=/',$js,$matches2);
        $var_name = $matches2[1];
    
        $res = preg_replace('/\w\.ajax_get.*?g\)\}\)/','console.log('.$function_name.'('.$var_name.'))',$js);
        // console.log(W(fwyQGEQrQp))
        $res .=  'reserve_seat()';
    }

    file_put_contents("./js/".$file_name,$res);

    echo $val."\n";

    $run_code = 'node ./js/'.$file_name;
    $run_res = `$run_code`;
    $final[$val] = $run_res;
    echo $run_res."\n";
    // print_r($res);
    // exit();
}

file_put_contents('./final.json',json_encode($final));