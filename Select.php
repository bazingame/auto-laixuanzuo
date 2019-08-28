<?php
include_once "lib/Snoopy.class.php";
include_once "lib/Log.php";
include_once "lib/Http.php";

class Select
{
    private $snoopy;

    private $url = 'https://wechat.laixuanzuo.com/index.php/reserve/index.html';
    private $select_url = 'https://wechat.laixuanzuo.com/index.php/reserve/get/yzm=&libid=';

    private $js_name;
    private $codes;
    private $isFound; //加密找到

    private $option;

    private $seats = [
        ['lib' => '11403', 'seat' => '12,15'],//701 74
//                        ['lib'=>'10648','seat'=>'11,15'],//702 04
//                        ['lib'=>'10550','seat'=>'21,15'],//204 13
//                        ['lib'=>'10550','seat'=>'23,15'],//204 16
//                        ['lib'=>'10648','seat'=>'15,15'],//702 8
//                        ['lib'=>'10837','seat'=>'20,15'],//701 6
    ];

    private $lib;
    private $seat;

    const WECHAT_SESSION = '21c91845c292d6f054750eb85d50bea7';

    public function __construct($argv)
    {
        $obj        = new Http();
        $res        = $obj->sCurl($this->options[0]);
        $result     = Utils::jsonDecode($res);


        $this->snoopy = new Snoopy;
        $this->snoopy->cookies = [
            'ROM_TYPE' => 'weixin',
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

    private function get_code()
    {
        Log::info("refresh code");
        $this->snoopy->fetch($this->url);
        $res = $this->snoopy->getResults();
        if (preg_match('/\/layout\/(.*?)\.js\"/', $res, $match)) {
            $this->js_name = $match[1];
            $this->codes = json_decode(file_get_contents("./final.json"), 1);
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

    }

    public function start()
    {
        if (!$this->isFound) {
            Log::info("not find to refresh ");
            $this->get_code();
        }
        if (in_array($this->js_name, array_keys($this->codes))) {
            $this->isFound = 1;
            $code = $this->codes[$this->js_name];
            $final_url = $this->select_url . $this->lib . "&" . $code . "=" . $this->seat;
            $this->snoopy->fetch($final_url);
            $select_res = $this->snoopy->getResults();
            return $select_res;
        } else {
            Log::info("no result : " . $this->js_name . " & retrying....");
            $this->isFound = 0;
            return $this->start();
        }
    }

    public function main($childPid)
    {
        $this->get_code();
        $n = 1000;        //单个进程请求次数
        while ($n--) {
            $res = $this->start();
            $res_arr = json_decode($res, 1);

            if (isset($res_arr['code']) && $res_arr['code'] == 0) {
                Log::info("Process " . $childPid . "\t" . "预定成功");
                break;
            } elseif (isset($res_arr['code']) && $res_arr['code'] == 1 && $res_arr['msg'] == '操作失败, 您已经预定了座位!') {
                Log::info("Process " . $childPid . "\t" . "其他进程预定成功");
                break;
            } elseif (preg_match('/503/', $res)) {
                Log::err("===第 $n 次,503===");
                Log::err("process $childPid is dying......");
                exit($childPid);
            } elseif (preg_match('/该座位不存在/', $res)) {     //位置不存在有可能是因为code过期导致
                Log::info("no seat to refresh");
                $this->get_code();
            } else {
                Log::info("Process " . $childPid . "  " . $n . "  " . $res);
            }
        }

    }

}

//wechatSESS_ID=6a80e1785027959a692b094d24441152
//21c91845c292d6f054750eb85d50bea7
