<?php

include_once "lib/Snoopy.class.php";


$s = new Snoopy();

$s->proxy_host = '58.218.200.223';
$s->proxy_port = '30275';

$s->fetch("http://myip.ipip.net/");
print_r($s->getResults());

echo "111";
