<?php
include_once "./Select.php";


$num = 1;   //进程数
while ($num--) {
    $pid = pcntl_fork();
    if ($pid == 0) {
        $pid = posix_getpid();
        echo "* Process {$pid} was created, and Executed:\n\n";
        $select = new Select($argv);
        $select->main($num);
        exit();
    }
}

while (pcntl_waitpid(0, $status) != -1) {
    $status = pcntl_wexitstatus($status);
    print_r($status);
    echo "Child $status completed\n";
}