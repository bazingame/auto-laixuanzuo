<?php

class Log {

    const SDK_LOG   = 'laixuanzuo';

    const PATH      = '/var/www/temp/laixuanke/logs'; // 日志目录

    public static $process = NULL;

    // 日志级别 从上到下，由低到高
    const EMERG     = 'EMERG';  // 严重错误: 导致系统崩溃无法使用
    const ALERT     = 'ALERT';  // 警戒性错误: 必须被立即修改的错误
    const CRIT      = 'CRIT';  // 临界值错误: 超过临界值的错误，例如一天24小时，而输入的是25小时这样
    const ERR       = 'ERR';  // 一般错误: 一般性错误
    const WARN      = 'WARN';  // 警告性错误: 需要发出警告的错误
    const NOTICE    = 'NOTIC';  // 通知: 程序可以运行但是还不够完美的错误
    const INFO      = 'INFO';  // 信息: 程序输出信息
    const DEBUG     = 'DEBUG';  // 调试: 调试信息
    const SQL       = 'SQL';  // SQL：SQL语句 注意只在调试模式开启时有效

    public static function warn($message, $filename = self::SDK_LOG) {
        self::_write($message, $filename, self::WARN);
    }

    public static function info($message, $filename = self::SDK_LOG) {
        self::_write($message, $filename, self::INFO);
    }

    public static function err($message, $filename = self::SDK_LOG) {
        self::_write($message, $filename, self::ERR);
    }

    //记录报警历史，用于统计和分析
    public static function alarmHistoryLog($message){
        $message = is_array($message) ? json_encode($message,JSON_UNESCAPED_UNICODE) : $message;
        print_r($message);
        $file       = self::PATH.DIRECTORY_SEPARATOR.'alarm_history_'.date("Y-m-d").".log";
        $content    = $message.PHP_EOL;
        error_log($content, 3, $file);
    }

    /**
     * 日志直接写入
     * @static
     * @access public
     * @param string $message 日志信息
     * @param string $level  日志级别
     * @return void
     */
    private static function _write($message, $filename, $level = self::ERR) {
        $file       = self::PATH.DIRECTORY_SEPARATOR.$filename.'_'.date("Y-m-d").".log";
        $prefix     = empty(self::$process) ? "[".date("Y-m-d H:i:s")."][".$level."]" : "[".date("Y-m-d H:i:s")."][".self::$process."][".$level."]";

        $content    = $prefix.$message.PHP_EOL;
        error_log($content, 3, $file);
    }

}
