<?php
//namespace FalconAlarm\Helper;
include_once "./Log.php";

class Http {

    protected $option           = array();
    private $ch                 = null;
    const CURL_BASE_VERSION     = 0x71B00;

    public function __construct() {
    }

    private function setOpt($option) {
        $this->ch = curl_init();
        $method = isset($option['method']) ? strtoupper($option['method']) : 'GET';
        if ($method != 'POST' && $option['args']) {
            foreach($option['args'] as $key => $value) {
                $option['url'] .= strpos($option['url'], '?') ? '&'.$key.'='.rawurlencode($value) : '?'.$key.'='.rawurlencode($value);
            }
        }
        curl_setopt($this->ch, CURLOPT_URL, $option['url']);
        curl_setopt($this->ch, CURLOPT_HEADER, false);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        $ctimeout = isset($option['ctimeout']) ? $option['ctimeout'] : 1;
        $timeout  = isset($option['timeout']) ? $option['timeout'] : 1;
        $version  = curl_version(CURLVERSION_NOW);
        if ($version['version_number'] >= self::CURL_BASE_VERSION){
            curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT_MS, $ctimeout * 1000);
            curl_setopt($this->ch, CURLOPT_NOSIGNAL, 1);
            curl_setopt($this->ch, CURLOPT_TIMEOUT_MS, $timeout * 1000);
        } else {
            $ctimeout    = 1;
            $timeout     = ($timeout >= 1)? intval($timeout) : 1;
            curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $ctimeout);
            curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);    //执行超时
        }
        switch ($method) {
            case 'POST' :
                curl_setopt($this->ch, CURLOPT_POST, true);
                if (isset($option['args']) && $option['args']) {
                    if (isset($option['multi_part'])) {
                        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $option['args']);
                        unset($option['multi_part']);
                    } else {
                        curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($option['args']));
                    }
                }
                break;
            case 'DELETE' :
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }
        if (isset($option['cookie'])) {
            curl_setopt($this->ch, CURLOPT_COOKIE, $option['cookie']);
        }
        if (isset($option['header'])) {
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $option['header']);
        }
        if (isset($option['ssl_verifypeer'])) {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $option['ssl_verifypeer']);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, $option['ssl_verifypeer']);
        }
    }

    /**
     * 单个url接口远程调用
     *
     * @param array $option    curl_setopt参数
     * @return string
     */
    public function sCurl($option) {
        $start              = microtime(true);
        $errMsg             = '';
        $path               = parse_url($option['url'], PHP_URL_PATH);
        if (isset($option['tryTimes']) && $option['tryTimes'] > 0) {
            $tryTimes       = $option['tryTimes'];
            unset($option['tryTimes']);
        } else {
            $tryTimes       = 1; // 默认请求一次
        }
        $total              = $tryTimes;
        while ($tryTimes) {
            $tryTimes --;
            $handler        = $this->setOpt($option);
            $ch             = $this->ch;
            $output         = curl_exec($ch);
            $curlNo         = curl_errno($ch);
            if (!$tryTimes && $curlNo) { // 记录最后一次的curl_error
                $errMsg     = curl_error($ch);
            }
            if ($curlNo) {
                curl_close($ch);
                $tryTimes && usleep(100);
            } else {
                curl_close($ch);
                break;
            }
        }
        $loginfo            = $path . '|' . sprintf("%.4f", microtime(true) - $start) . '|' . ($total - $tryTimes) . '|' . $errMsg;
        Log::info('sCurl:' . $loginfo);

        return $output;
    }

    /**
     * 优化后的multi_curl，经过ab工具测试分析
     *
     * @param array $options
     * @return array
     */
    public function mCurl($options) {
        $start          = microtime(true);
        $result         = array();
        $map            = array();
        $errorMsg       = array();

        $mch            = curl_multi_init();
        foreach ($options as $i => $opt) {
            $chObj      = $this->setOpt($opt);
            $ch         = $this->ch;
            curl_multi_add_handle ($mch, $ch);
            $map[$i]    = $ch;
        }
        # execute the handles
        $active = null;
        do {
            $mrc = curl_multi_exec($mch, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ( $active && $mrc == CURLM_OK ) {
            if (curl_multi_select($mch, 0.8) == - 1) {      # select超时返回0,失败返回-1,当超时的时候setOpt中的timeout就起作用了,使用man select 查看实现原理
                usleep(100);
            }
            do {
                $mrc = curl_multi_exec($mch, $active);
            } while ( $mrc == CURLM_CALL_MULTI_PERFORM );
        }
        # get results
        foreach ($map as $k => $handle) {
            if (curl_error($handle) == '') {
                $temp   = curl_multi_getcontent($handle);
                $count  = 0;
                while (!$temp) {
                    if ($count > 3)
                        break;
                    usleep (100);
                    $temp = curl_multi_getcontent ($handle);
                    $count ++;
                }
                $result[$k] = $temp;
                curl_multi_remove_handle($mch, $handle);
                curl_close ($handle);
            } else {
                $errorMsg[$options[$k]['url']] = curl_error($handle);
            }
        }
        curl_multi_close($mch);

        $errInfo            = empty($errorMsg) ? '' : serialize($errorMsg);
        $loginfo            = count($options) . '|' . sprintf("%.4f", microtime(true) - $start) . '|' . $errInfo;
        Log::info('mCurl:'.$loginfo);

        return $result;
    }

}
