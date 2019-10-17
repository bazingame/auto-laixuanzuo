<?php
include_once "lib/Snoopy.class.php";
include_once "lib/Log.php";

class Select
{
    private $snoopy;

    private $url = 'https://wechat.laixuanzuo.com/index.php/reserve/index.html';
    private $select_url = 'https://wechat.laixuanzuo.com/index.php/reserve/get/yzm=&libid=';

    private $js_name;  //js文件名
    private $code;      //js运行后算出的结果
    private $code_file_name  = './code.txt';

    private $seats = [
        ['lib' => '10837', 'seat' => '12,16'],//701 74
//                        ['lib'=>'10648','seat'=>'11,15'],//702 04
//                        ['lib'=>'10550','seat'=>'21,15'],//204 13
//                        ['lib'=>'10550','seat'=>'23,15'],//204 16
//                        ['lib'=>'10648','seat'=>'15,15'],//702 8
//                        ['lib'=>'10837','seat'=>'20,15'],//701 6
    ];


    private $lib;
    private $seat;

    const WECHAT_SESSION = '66fa66dc3a0983611ae956b6d2068dc9';
//    const WECHAT_SESSION = '6a80e1785027959a692b094d24441152';

    public function __construct($argv)
    {
        $this->snoopy = new Snoopy;
        $this->snoopy->cookies = [
            'wechatSESS_ID' => self::WECHAT_SESSION,
            'FROM_TYPE' => 'weixin',
            'Hm_lpvt_7838cef374eb966ae9ff502c68d6f098' => '1561331338',
            'Hm_lvt_7838cef374eb966ae9ff502c68d6f098' => '1560055230',
        ];
        $this->snoopy->agent = 'Mozilla/5.0 (Linux; Android 5.0; SM-G900P Build/LRX21T) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/33.0.0.0 Mobile Safari/537.36 MicroMessenger/6.0.0.54_r849063.501 NetType/WIFI';
        $this->snoopy->referer = 'https://wechat.laixuanzuo.com/index.php/reserve/index.html';

        $num = isset($argv[1]) ? $argv[1] : 0;

        $this->lib = $this->seats[$num]['lib'];
        $this->seat = $this->seats[$num]['seat'];
    }

    private function read_code(){
        Log::info("read code");
        $file = fopen($this->code_file_name,'r');
        $code = fread($file,filesize($this->code_file_name));
        $this->code = $code;
        fclose($file);
    }

    private function get_code($isForce = false)
    {
        if($isForce) {
            Log::info(posix_getpid() . ":force refresh code");

            //后续进程
            $file = fopen($this->code_file_name, 'w');
            if(!flock($file , LOCK_SH | LOCK_NB)){
                Log::info("check is locked");
                fclose($file);
                $this->read_code();
                return;
            }else{
                Log::info("check no locked");
            }
            fclose($file);


            //第一个进程执行更新并加锁
            $file = fopen($this->code_file_name, 'w');
            flock($file,LOCK_EX);
            Log::info(posix_getpid() . "add LOCK_EX to code.txt");

            $this->snoopy->fetch($this->url);
            $res = $this->snoopy->getResults();
            if (preg_match('/\/layout\/(.*?)\.js\"/', $res, $match)) {
                $this->js_name = $match[1];
                $codes = json_decode(file_get_contents("./final.json"), 1);

                if (in_array($this->js_name, array_keys($codes))) {
                    Log::info(posix_getpid() . ":写入code");
                    $this->code = $codes[$this->js_name];
                    //写入文件
                    fwrite($file, $this->code);

                } else {
                    Log::info("no result : " . $this->js_name . " & retrying....");
                    $this->get_code();
                }
            } elseif (preg_match('/取消/', $res, $match)) {
                Log::err("预定成功:" . $this->lib . $this->seat);
                exit("已经预定成功");
            } elseif (preg_match('/isWeixin/', $res)) {
                Log::err("session error");
                exit();
            } else {
                Log::err("unknow error");
                exit();
            }
//            sleep(5);
            flock($file,LOCK_UN);
            fclose($file);
            Log::info(posix_getpid() . ":UNLOCK");

        } else {
            $this->read_code();
        }

    }

    public function select()
    {
        $final_url = $this->select_url . $this->lib . "&" . $this->code . "=" . $this->seat;
        $this->snoopy->fetch($final_url);
        $select_res = $this->snoopy->getResults();
        return $select_res;
    }

    public function main($childPid)
    {
        $this->get_code();
        $pid = posix_getpid();
        $childPid = $pid;
        $n = 10000;        //单个进程请求次数
        while ($n--) {
            $res = $this->select();
//            Log::info("using code:" . $this->code);
            $res_arr = json_decode($res, 1);

            if (isset($res_arr['code']) && $res_arr['code'] == 0) {
                Log::info("Process " . $childPid . "\t" . "预定成功");
                break;
            } elseif (isset($res_arr['code']) && $res_arr['code'] == 1 && $res_arr['msg'] == '操作失败, 您已经预定了座位!') {
                Log::info("Process " . $childPid . "\t" . "其他进程预定成功");
                break;
            } elseif (preg_match('/503/', $res)) {
//                print_r($res);
                Log::err("===第 $n 次,503===");
//                Log::err("process $childPid is dying......");
                exit($childPid);
            } elseif (preg_match('/该座位不存在/', $res)) {     //位置不存在有可能是因为code过期导致
                Log::info("Process " . $childPid . "  " . $n . "  no seat to refresh");
                $this->get_code(true);
            } else {
                Log::info("Process " . $childPid . "  " . $n . "  " . $res);

            }
        }

    }

}

