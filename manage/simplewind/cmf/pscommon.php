<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: 彭骦 <547150960@qq.com>
// +----------------------------------------------------------------------
use think\Db;
use plugins\area\model\PluginAreaModel;
use Dompdf\Positioner\Absolute;
use think\Log;
use cmf\phpqrcode\QRcode;

// 应用公共文件
/**
 * 对提供的数据进行urlsafe的base64编码。
 *
 * @param string $data 待编码的数据，一般为字符串
 *
 * @return string 编码后的字符串
 */
function ps_base64_encode($data)
{
    $find = array('+', '/');
    $replace = array('-', '_');
    return str_replace($find, $replace, base64_encode($data));
}

/**
 * 对提供的urlsafe的base64编码的数据进行解码
 *
 * @param string $str 待解码的数据，一般为字符串
 *
 * @return string 解码后的字符串
 */
function ps_base64_decode($str)
{
    $find = array('-', '_');
    $replace = array('+', '/');
    return base64_decode(str_replace($find, $replace, $str));
}


function downloadFile($fullPath)
{
    if (headers_sent()) die('Headers Sent');
    if (ini_get('zlib.output_compression')) ini_set('zlib.output_compression', 'Off');
    if (file_exists($fullPath)) {
        $fsize = filesize($fullPath);
        $path_parts = pathinfo($fullPath);
        $ext = strtolower($path_parts["extension"]);
        switch ($ext) {
            case "pdf":
                $ctype = "application/pdf";
                break;
            case "exe":
                $ctype = "application/octet-stream";
                break;
            case "zip":
                $ctype = "application/zip";
                break;
            case "doc":
                $ctype = "application/msword";
                break;
            case "xls":
                $ctype = "application/vnd.ms-excel";
                break;
            case "ppt":
                $ctype = "application/vnd.ms-powerpoint";
                break;
            case "gif":
                $ctype = "image/gif";
                break;
            case "png":
                $ctype = "image/png";
                break;
            case "jpeg":
            case "jpg":
                $ctype = "image/jpg";
                break;
            default:
                $ctype = "application/force-download";
        }
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false); // required for certain browsers
        header("Content-Type: $ctype");
        header("Content-Disposition: attachment; filename=\"" . basename($fullPath) . "\";");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . $fsize);
        ob_clean();
        flush();
        readfile($fullPath);
    } else {
        die('File Not Found');
    }
}

/**
 * 生成唯一标识
 * @return string
 */
function ps_guid()
{
    if (function_exists('com_create_guid')) {
        return com_create_guid();
    } else {
        mt_srand((float) microtime() * 10000); // optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = ''; //chr ( 45 ); // "-"
        $uuid = // chr(123).// "{"
            substr($charid, 0, 8) . $hyphen .
            substr($charid, 8, 4) . $hyphen .
            substr($charid, 12, 4) . $hyphen .
            substr($charid, 16, 4) . $hyphen .
            substr($charid, 20, 12);
        //.chr(125);// "}"
        return $uuid;
    }
}

/**
 * 生成随机字符串
 * @param int $length 字符串长度
 * @param string $chars	随机字符串范围
 * @return string	返回字符串结果
 */
function ps_random($length, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz')
{
    $hash = '';
    $max = strlen($chars) - 1;
    for ($i = 0; $i < $length; $i++) {
        $hash .= $chars[mt_rand(0, $max)];
    }
    return $hash;
}


/**
 * 功能：对二维数组中的某个元素过行排序
 * 日期：2015-6-19
 * @param array  	$multi_array      数组
 * @param string	  		$sort_key  		     进行排序的元素
 * @param int    $sort  			    排序方法:SORT_ASC   SORT_DESC
 * @return array    $multi_array
 * 作者：kql   multi_array_sort(ovt,'age')
 * @return:
 */
function multi_array_sort($multi_array, $sort_key, $sort = SORT_ASC)
{
    if (is_array($multi_array)) {
        foreach ($multi_array as $row_array) {
            if (is_array($row_array)) {
                $key_array[] = $row_array[$sort_key];
            } else {
                return false;
            }
        }
    } else {
        return false;
    }
    array_multisort($key_array, $sort, $multi_array);
    return $multi_array;
}
/**
 * 功能：数组分组
 * 日期： 2017-2-9
 */
function array_group_by($arr, $key)
{
    $grouped = [];
    foreach ($arr as $value) {
        $grouped[$value[$key]][] = $value;
    }
    if (func_num_args() > 2) {
        $args = func_get_args();
        foreach ($grouped as $key => $value) {
            $parms = array_merge([$value], array_slice($args, 2, func_num_args()));
            $grouped[$key] = call_user_func_array('array_group_by', $parms);
        }
    }
    return $grouped;
}



/**
 * 安全返回数组中key的内容
 * @param array $arr 
 * @param string $key 
 * @return mixed
 */
function array_saft_value($arr, $key)
{
    if (!$arr || $arr == null) {
        return null;
    }
    return array_key_exists($key, $arr) ? $arr[$key] : null;
}

/**
 * 求两个日期之间相差的天数
 * (针对1970年1月1日之后，求之前可以采用泰勒公式)
 * @param string $day1
 * @param string $day2
 * @return number
 */
function diffBetweenTwoDays($day1, $day2)
{
    $second1 = strtotime($day1);
    $second2 = strtotime($day2);

    return floor(($second1 - $second2) / 86400);
}

/*
 *function：计算两个日期相隔多少年，多少月，多少天
 *param string $date1[格式如：2011-11-5]
 *param string $date2[格式如：2012-12-01]
 *return array array('年','月','日');
 */
function diffDate($date1, $date2)
{
    if ($date1 && $date2) {
        if (strtotime($date1) > strtotime($date2)) {
            $tmp = $date2;
            $date2 = $date1;
            $date1 = $tmp;
        }
        list($Y1, $m1, $d1) = explode('-', $date1);
        list($Y2, $m2, $d2) = explode('-', $date2);
        $Y = intval($Y2) - intval($Y1);
        $m = intval($m2) - intval($m1);
        $d = intval($d2) - intval($d1);
        if ($d < 0) {
            $d += (int) date('t', strtotime("-1 month $date2"));
            $m--;
        }
        if ($m < 0) {
            $m += 12;
            $Y--;
        }
        return array('year' => $Y, 'month' => $m, 'day' => $d);
    }
    return array('year' => 0, 'month' => 0, 'day' => 0);
}

/*
 *function：计算两个日期相隔多少月(预计多少个月内开业)
 *param string $mindate[格式如：2011-11-5]
 *param string $maxdate[格式如：2012-12-01]
 *return array int('月');
 */
function diffDateForMonth($mindate, $maxdate)
{

    if ($mindate && $maxdate && (strtotime($mindate) < strtotime($maxdate))) {
        $list = diffDate($mindate, $maxdate);
        $month = $list['year'] * 12 + $list['month'] + ($list['day'] > 0 ? 1 : 0);
        return $month == 0 ? 1 : $month;
    }
    return 1;
}

/**
 * 过滤姓名中的性别内容
 * @param string $name 
 * @param string $sex 
 * @return string
 */
function filterRepeatSex($name, $sex)
{
    if (!$name) {
        return "-";
    }

    if (mb_strlen($name) > 3) {
        $name = mb_substr($name, 0, 2);
    } else if (mb_strlen($name) >= 1) {
        $name = mb_substr($name, 0, 1);
    }

    //if(strpos($name, $sex)){
    //    return $name;
    //}
    return $name . $sex;
}

/**
 * 得到星期几
 * @param string $datetime 日期字符串 2019-01-01 01:01:01
 * @return string 星期一
 */
function ps_get_week($datetime = null)
{
    $thistime = $datetime ? strtotime($datetime) : time();
    $weekarray = array("日", "一", "二", "三", "四", "五", "六"); //先定义一个数组
    $weekname = "星期" . $weekarray[date("w", $thistime)];
    return $weekname;
}

/**
 * 获取时间完整字符串，带星期
 * @param string $datetime 
 * @return string
 */
function getFullDate($datetime = null, $format = 'Y年m月d日 H:i:s')
{
    $thistime = $datetime ? strtotime($datetime) : time();
    $weekarray = array("日", "一", "二", "三", "四", "五", "六"); //先定义一个数组
    $weekname = "星期" . $weekarray[date("w", $thistime)];

    return date($format, $thistime) . ' ' . $weekname;
}

/**
 * 字段排序数组
 * @param array $arr 需要排序的数组
 * @param string $field 排序使用数组中的key名称
 * @param string $direction 排序方式 (SORT_ASC,SORT_DESC)有很多种，可以去array_multisort方法源代码里面看
 * @return array
 */
function array_field_sort($arr, $field, $direction)
{
    //$arrSort = array();  
    //foreach($arr AS $uniqid => $row){  
    //    foreach($row AS $key=>$value){  
    //        $arrSort[$key][$uniqid] = $value;  
    //    }  
    //}  
    //if($direction){  
    //    array_multisort($arrSort[$field], constant($direction), $arr);  
    //}  

    //return $arr;  

    return multi_array_sort($arr, $field, constant($direction));
}

/**
 * 获取当月最后一天是多少号
 * @return int
 */
function getmonthlastday()
{
    return intval(date('d', strtotime("last day of this month")));
}



/**
 * 字符串不为空则连接字符串
 * @param string $str 判断不为空字符串
 * @param string $appendstr 连接到后面的字符串
 * @return string
 */
function str_notemptyappend($str, $appendstr)
{
    if ($str) {
        return $str . $appendstr;
    }
    return $str;
}

/**
 * 简单固定数随机产生数字
 * @param int $min 
 * @param int $max 
 * @param int $fixednumber 
 * @return int
 */
function fixed_rand($min, $max, $fixednumber)
{
    $arr = [];
    for ($i = $min; $i <= $max; $i++) {
        $arr[] = $i;
    }
    return $arr[$fixednumber % count($arr)];
}


/**
 * 字符串截取
 * @param string $string 目标字符串
 * @param number $length	截取长度
 * @param string $suffix 超出替换  '...' 默认''
 * @param number $start	 截取开始位置
 * @param charset $charset	 字符串编码 默认utf-8
 * @return string|string|unknown 返回结果
 */
function ps_substr($string, $length, $suffix = '', $start = 0, $charset = 'utf-8')
{
    if ($start) {
        $tmp = ps_substr($string, $start);
        $string = substr($string, strlen($tmp));
    }
    $strlen = strlen($string);
    if ($strlen <= $length) return $string;
    $string = str_replace(array('&quot;', '&lt;', '&gt;'), array('"', '<', '>'), $string);
    $length = $length - strlen($suffix);
    $str = '';
    if (strtolower($charset) == 'utf-8') {
        $n = $tn = $noc = 0;
        while ($n < $strlen) {
            $t = ord($string{
                $n});
            if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                $tn = 1;
                $n++;
                $noc++;
            } elseif (194 <= $t && $t <= 223) {
                $tn = 2;
                $n += 2;
                $noc += 2;
            } elseif (224 <= $t && $t <= 239) {
                $tn = 3;
                $n += 3;
                $noc += 2;
            } elseif (240 <= $t && $t <= 247) {
                $tn = 4;
                $n += 4;
                $noc += 2;
            } elseif (248 <= $t && $t <= 251) {
                $tn = 5;
                $n += 5;
                $noc += 2;
            } elseif ($t == 252 || $t == 253) {
                $tn = 6;
                $n += 6;
                $noc += 2;
            } else {
                $n++;
            }
            if ($noc >= $length) break;
        }
        if ($noc > $length) $n -= $tn;
        $str = substr($string, 0, $n);
    } else {
        for ($i = 0; $i < $length; $i++) {
            $str .= ord($string{
                $i}) > 127 ? $string{
                $i} . $string{
                ++$i} : $string{
                $i};
        }
    }
    $str = str_replace(array('"', '<', '>'), array('&quot;', '&lt;', '&gt;'), $str);
    return $str == $string ? $str : $str . $suffix;
}



/**
 *计算时间和当前时间相差的月数
 * 
 */
function getmonth($time)
{
    $nowDateInfo = getdate();
    $time = is_numeric($time) ? $time : strtotime($time);
    $timeDateInfo = getdate($time);
    if ($nowDateInfo['year'] < $timeDateInfo['year']) {
        return "12个月以后";
    } elseif ($nowDateInfo['year'] > $timeDateInfo['year']) {
        return "1个月内";
    } else {
        return $timeDateInfo['mon'] > $nowDateInfo['mon'] ? ($timeDateInfo['mon'] - $nowDateInfo['mon']) . "个月内" : "1个月内";
    }
}



/**
 * 写入日志
 * @param mix $data
 */
function ps_writelog($data, $title = '', $fileName = "my.log")
{
    $data = is_string($data) ? $data : var_export($data, 1);
    //Log::write($title.':'.date("Y-m-d H:i:s")."-------------------->".PHP_EOL.$data.PHP_EOL.'--------------------------->end');  
    file_put_contents(RUNTIME_PATH . $fileName, PHP_EOL . $title . ':' . date("Y-m-d H:i:s") . "-------------------->start" . PHP_EOL . $data . PHP_EOL . '-------------------------------------->end' . PHP_EOL, FILE_APPEND);
}


/**
 * @param string $url
 * @param string $message - 发送的消息
 * @return bool
 */
function send($url, $message = '')
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    //默认使用iPv4
    if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    }
    $headers = [
        "Content-Type: application/json;charset=UTF-8",
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); //设置header
    $rs = curl_exec($ch);
    curl_close($ch);
    return $rs;
}

/**
  * 发送post请求
  * @param string $url 请求地址
  * @param array $post_data post键值对数据
  * @return string
 */
function send_post($url, $post_data)
{
    $postdata = http_build_query($post_data);
    $options = array();
    $options['http'] = [
        'method' => 'POST',
        'header' => 'Content-type:application/x-www-form-urlencoded',
        'content' => $postdata,
        'timeout' => 1 * 60 // 超时时间（单位:s）
    ];

    $context = stream_context_create($options);

    $result = file_get_contents($url, false, $context);

    return $result;
}


/**
 * 是否显示手机号
 * @param unknown $phone
 * @param unknown $isshowphone
 * @return unknown
 */
function phonehidde($phone, $isshowphone = false)
{
    if (empty($phone)) return '';
    if ($isshowphone) return $phone;
    return substr_replace($phone, '****', 3, 4);
}



/**
 *异步请求
 * @param $url
 * @param array $param
 */
function doRequest($url, $param = array(), $showreturn = false)
{
    $urlinfo = parse_url($url);

    $host = $urlinfo['host'];
    $path = $urlinfo['path'];
    $query = isset($param) ? http_build_query($param) : '';

    $port = $urlinfo['port'] ? $urlinfo['port'] : 80;
    $errno = 0;
    $errstr = '';
    $timeout = 1;

    $fp = fsockopen($host, $port, $errno, $errstr, $timeout);

    $out = "POST " . $path . " HTTP/1.1\r\n";
    $out .= "host:" . $host . "\r\n";
    $out .= "content-length:" . strlen($query) . "\r\n";
    $out .= "content-type:application/x-www-form-urlencoded\r\n";
    $out .= "connection:close\r\n\r\n";
    $out .= $query;
    fputs($fp, $out);
    while ($row = fread($fp, 500)) {
        if ($showreturn) {
            echo $row;
        } else {
            break;
        }
    }
    fclose($fp);
}

function curl_post_raw($url, $rawData)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $rawData);
    //默认使用iPv4
    if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    }
    curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array(
            'Content-Type: text'
        )
    );
    $data = curl_exec($ch);
    curl_close($ch);
    return ($data);
}



/**
 * curl post 请求
 * @param string $url
 * @param array $param
 * @param string $post_file
 * @return mixed|boolean
 */
function cmf_curl_post($url, $param, $post_file = false, $throwErr = false)
{
    $oCurl = curl_init();
    if (stripos($url, "https://") !== false) {
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }
    if (is_string($param) || $post_file) {
        $strPOST = $param;
    } else {
        $strPOST = http_build_query($param);
    }
    //设置60秒超时
    curl_setopt($oCurl, CURLOPT_TIMEOUT, 15);
    // 在尝试连接时等待的秒数
    curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 10);
    // //默认使用iPv4
    if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
        curl_setopt($oCurl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    }
    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($oCurl, CURLOPT_POST, true);
    curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
    $sContent = curl_exec($oCurl);

    if (curl_errno($oCurl) && $throwErr) {
        throw new \Exception(curl_error($oCurl));
    }

    $aStatus = curl_getinfo($oCurl);
    curl_close($oCurl);

    if (intval($aStatus["http_code"]) == 200) {
        return $sContent;
    } else {
        $errmsg = $aStatus["http_code"] . (empty($sContent) ? '' : substr($sContent, 0, 100));
        if ($throwErr) {
            throw new \Exception($errmsg);
        } else {
            Log::error($errmsg);
            return false;
        }
    }
}

function cmf_check_access_byrule($userId, $cname)
{

    $authObj = new \cmf\lib\Auth();
    //没有规则时默认不显示
    return $authObj->check($userId, $cname);
}

/**
 * 替换“'”为“\'”
 * @param string|array $str
 * @return mixed
 */
function safe_check_param($str)
{
    return str_replace("'", "\'", $str);
}

/**
 * redis队列入库
 * @param  string  $job  队列消费类名（方法）
 * @param  array $data  写入数据
 * @param  string  $jobQueueName  队列名称
 * @result  string  入队结果
 */
function queue_push($job, $data, $jobQueueName)
{
    if (!config('queue')) return;
    $result = \think\Queue::push($job, $data, $jobQueueName);
    return $result;
}
//$tdata=['sdfdsfsdf'];
//echo queue_push('app\common\queue\SyncGuanJiaClientQueue', $tdata,'syncGuanJiaClientQueue');
//die;
/**
 * 延迟队列
 * @param int $delay    延迟秒数
 * @param unknown $job
 * @param string $data
 * @param unknown $queue
 */
function queue_later($delay, $job, $data = '', $jobQueueName = null)
{
    if (!config('queue')) return;
    $result = \think\Queue::later($delay, $job, $data, $jobQueueName);
    return $result;
}

/**
 * 获取两个时间的时间差
 * @param unknown $datetime1
 * @param unknown $datetime2
 * @return number[]
 */
function diffDatetime($datetime1, $datetime2 = null)
{
    $datetime1 = is_numeric($datetime1) ? $datetime1 : strtotime($datetime1);
    $datetime2 = !empty($datetime2) ? (is_numeric($datetime2) ? $datetime2 : strtotime($datetime2)) : time();
    if ($datetime1 > $datetime2) {
        $tmp = $datetime2;
        $datetime2 = $datetime1;
        $datetime1 = $tmp;
    }
    $date1 = date('Y-m-d', $datetime1);
    $date2 = date('Y-m-d', $datetime2);

    list($Y1, $m1, $d1) = explode('-', $date1);
    list($Y2, $m2, $d2) = explode('-', $date2);

    $Y = $Y2 - $Y1;
    $m = $m2 - $m1;
    $d = $d2 - $d1;
    if ($d < 0) {
        $d += (int) date('t', strtotime("-1 month $date2"));
        $m--;
    }
    if ($m < 0) {
        $m += 12;
        $Y--;
    }
    $days = floor(($datetime2 - $datetime1) / 86400);
    $t = ($datetime2 - $datetime1) % 86400;
    $H = floor($t / 3600);
    $i = floor($t % 3600 / 60);
    $s = $t % 60;
    return array('Y' => $Y, 'm' => $m, 'd' => $d, 'H' => $H, 'i' => $i, 's' => $s, 'days' => $days);
}

function diffDatetimeGetMinute($datetime, $datetime2 = null)
{
    $diffDate = diffDatetime($datetime, $datetime2);
    extract($diffDate);
    //($Y?$Y.'年':'').($m?$m.'个月':'').($d?$d.'天':'').($H?$H.'小时':'').($i?$i.'分钟':'').($s?$s.'秒':'');
    return ($days ? $days . '天' : '') . ($H ? $H . '小时' : '') . ($i ? $i . '分钟' : '') . ($s ? $s . '秒' : '');
}

function diffDatetimeFormat($datetime1, $datetime2, $formattype = 'i')
{
    $second = abs(strtotime($datetime1) - strtotime($datetime2));

    if ($formattype == 's') {
        return $second;
    } else if ($formattype == 'i') {
        return $second / 60;
    } else if ($formattype == 'H') {
        return $second / 3600;
    }

    return $second;
}


/**
 * 生成安全码
 * @param string $token
 * @param string $timestamp
 * @param string $caller
 * @param string $mobile
 * @return string|boolean
 */
function getSHA1($token, $timestamp, $caller, $mobile)
{
    try {
        $array = array($token, $timestamp, $caller, $mobile);
        sort($array, SORT_STRING);
        $str = implode($array);
        return sha1($str);
    } catch (Exception $e) {
        return false;
    }
}



/**
 * 获取手机归属地
 */
function getPhoneLocation($phone)
{
    $apiUrl = config('phone_location_api');
    $phone_location = '未知归属';
    if (empty(config('isSendShortMessage'))) {
        return $phone_location;
    }
    try {
        $result = json_decode(file_get_contents($apiUrl[0] . $phone), 1);
        if (isset($result['response'][$phone]) && !empty($result['response'][$phone]['location'])) {
            $data = $result['response'][$phone];
            $phone_location = isset($data['detail']['area'][0]) && $data['detail']['area'][0]['city'] == $data['detail']['province'] ? $data['detail']['province'] . $data['detail']['operator'] : $data['location'];
        } else {
            $result = file_get_contents($apiUrl[1] . $phone);
            $result = mb_convert_encoding($result, 'UTF-8', mb_detect_encoding($result, array('UTF-8', 'GBK', 'LATIN1', 'BIG5')));
            preg_match('/carrier:\'(.+?)\'/', $result, $matches);
            if (!empty($matches[1])) $phone_location = $matches[1];
        }
    } catch (Exception $e) {
        return '未知归属';
    }
    return $phone_location;
}


/**
 * 获取时间超期字符串
 * @param int $starttime 
 * @param int $totime
 * @return string 时间格式
 */
function formatovertime($starttime, $totime)
{
    $difference = $totime - $starttime;
    if (($difference / 60) < 60) {
        return '已超期' . floor($difference / 60) . "分钟";
    } elseif (($difference / 3600) < 24) {
        return '已超期' . floor($difference / 3600) . "小时";
    } elseif (($difference / 86400) > 0) {
        return '已超期' . floor($difference / 86400) . "天";
    } else {
        return date('m-d', $starttime);
    }
}

/**
 * 根据时间戳获取时间字符串
 * @param unknown $t
 */
function getHourTime($t)
{
    $s = '';
    $s .= $t > 3600 ? floor($t / 3600) . ':' : '0:';
    $s .= sprintf("%02d", floor($t % 3600 / 60)) . ':';
    $s .= sprintf("%02d", $t % 60); //.'秒';
    return $s;
}

/**
 * 计算剩余天时分。
 * $unixEndTime string 终止日期的Unix时间
 * @author tangxinzhuan
 * @version 2016-10-28
 */
function timeShengYuTianShiFen($unixEndTime = 0, $starttime = null)
{
    $starttime = $starttime ?: time();
    if ($unixEndTime <= $starttime) { // 如果过了活动终止日期
        return '0天0时0分';
    }

    // 使用当前日期时间到活动截至日期时间的毫秒数来计算剩余天时分
    $time = $unixEndTime - $starttime;

    $days = 0;
    if ($time >= 86400) { // 如果大于1天
        $days = (int) ($time / 86400);
        $time = $time % 86400; // 计算天后剩余的毫秒数
    }

    $xiaoshi = 0;
    if ($time >= 3600) { // 如果大于1小时
        $xiaoshi = (int) ($time / 3600);
        $time = $time % 3600; // 计算小时后剩余的毫秒数
    }

    $fen = (int) ($time / 60); // 剩下的毫秒数都算作分

    $str = "";
    if ($days) {
        $str .= $days . '天';
    }
    if ($xiaoshi) {
        $str .= $xiaoshi . '小时';
    }
    if ($fen) {
        $str .= $fen . '分钟';
    }

    return empty($str) ? '1分钟' : $str;
}



function array_sort($array, $keys, $sort = 'asc')
{
    $newArr = $valArr = array();
    foreach ($array as $key => $value) {
        $valArr[$key] = $value[$keys];
    }
    ($sort == 'asc') ? asort($valArr) : arsort($valArr); //先利用keys对数组排序，目的是把目标数组的key排好序
    reset($valArr); //指针指向数组第一个值 
    foreach ($valArr as $key => $value) {
        $newArr[$key] = $array[$key];
    }
    return $newArr;
}


/**
 * 获取中文拼音
 * @param string $text
 * @param string $exp
 * @return string
 */
function gb2py($text, $exp = '')
{
    if (!$text) return '';
    $text = str_replace(['梵'], ['凡'], $text);
    $text = iconv('utf-8', 'gb2312' . "//IGNORE", $text);
    $data = array();
    $tmp = @file(RUNTIME_PATH . '/gb-pinyin.table');
    if (!$tmp) return '';
    $tmps = count($tmp);
    for ($i = 0; $i < $tmps; $i++) {
        $tmp1 = explode("	", $tmp[$i]);
        $data[$i] = array($tmp1[0], $tmp1[1]);
    }
    $r = array();
    $k = 0;
    $textlen = strlen($text);
    for ($i = 0; $i < $textlen; $i++) {
        $p = ord(substr($text, $i, 1));
        if ($p > 160) {
            $q = ord(substr($text, ++$i, 1));
            $p = $p * 256 + $q - 65536;
        }
        if ($p > 0 && $p < 160) {
            $r[$k] = chr($p);
        } elseif ($p < -20319 || $p > -10247) {
            $r[$k] = '';
        } else {
            for ($j = $tmps - 1; $j >= 0; $j--) {
                if ($data[$j][1] <= $p) break;
            }
            $r[$k] = $data[$j][0];
        }
        $k++;
    }
    return implode($exp, $r);
}



function agent_decode($string)
{
    $arr = str_split($string, 2);
    $string = '%' . implode('%', $arr);
    return urldecode($string);
}



//获取浏览器以及版本号
function getbrowser()
{
    global $_SERVER;
    $agent = $_SERVER['HTTP_USER_AGENT'];
    $browser = '';
    $browser_ver = '';

    if (empty($agent)) {
        return ['browser' => '', 'version' => ''];
    } elseif (preg_match('/OmniWeb\/(v*)([^\s|;]+)/i', $agent, $regs)) {
        $browser = 'OmniWeb';
        $browser_ver = $regs[2];
    } elseif (preg_match('/Netscape([\d]*)\/([^\s]+)/i', $agent, $regs)) {
        $browser = 'Netscape';
        $browser_ver = $regs[2];
    } elseif (preg_match('/Chrome\/([^\s]+)/i', $agent, $regs)) {
        $browser = 'Chrome';
        $browser_ver = $regs[1];
    } elseif (preg_match('/safari\/([^\s]+)/i', $agent, $regs)) {
        $browser = 'Safari';
        $browser_ver = $regs[1];
    } elseif (preg_match('/MSIE\s([^\s|;]+)/i', $agent, $regs)) {
        $browser = 'Internet Explorer';
        $browser_ver = $regs[1];
    } elseif (preg_match('/Trident\/7.0.*rv:([^\s|;|)]+)/i', $agent, $regs)) {
        $browser = 'Internet Explorer';
        $browser_ver = $regs[1];
    } elseif (preg_match('/Opera[\s|\/]([^\s]+)/i', $agent, $regs)) {
        $browser = 'Opera';
        $browser_ver = $regs[1];
    } elseif (preg_match('/NetCaptor\s([^\s|;]+)/i', $agent, $regs)) {
        $browser = '(Internet Explorer ' . $browser_ver . ') NetCaptor';
        $browser_ver = $regs[1];
    } elseif (preg_match('/Maxthon/i', $agent, $regs)) {
        $browser = '(Internet Explorer ' . $browser_ver . ') Maxthon';
        $browser_ver = '';
    } elseif (preg_match('/360SE/i', $agent, $regs)) {
        $browser = '(Internet Explorer ' . $browser_ver . ') 360SE';
        $browser_ver = '';
    } elseif (preg_match('/SE 2.x/i', $agent, $regs)) {
        $browser = '(Internet Explorer ' . $browser_ver . ') 搜狗';
        $browser_ver = '';
    } elseif (preg_match('/FireFox\/([^\s]+)/i', $agent, $regs)) {
        $browser = 'FireFox';
        $browser_ver = $regs[1];
    } elseif (preg_match('/Lynx\/([^\s]+)/i', $agent, $regs)) {
        $browser = 'Lynx';
        $browser_ver = $regs[1];
    }
    //微信浏览器
    elseif (preg_match('/MicroMessenger\/([^\s]+)/i', $agent, $regs)) {
        $browser = 'MicroMessenger';
        $browser_ver = $regs[1];
    } elseif (preg_match('/QQBrowser\/([^\s]+)/i', $agent, $regs)) {
        $browser = 'QQBrowser';
        $browser_ver = $regs[1];
    } elseif (preg_match('/QQBrowserLite\/([^\s]+)/i', $agent, $regs)) {
        $browser = 'QQBrowserLite';
        $browser_ver = $regs[1];
    }
    //百度盒子
    elseif (preg_match('/baiduboxapp\/([^\s]+)/i', $agent, $regs)) {
        $browser = 'baiduboxapp';
        $browser_ver = $regs[1];
    } elseif (preg_match('/UCBrowser\/([^\s]+)/i', $agent, $regs)) {
        $browser = 'UCBrowser';
        $browser_ver = $regs[1];
    } elseif (preg_match('/Mac[\s]*OS[\s]*X/i', $agent)) { //特殊处理
        $browser = 'Safari';
        $browser_ver = "";
    }
    if ($browser != '') {
        return ['browser' => $browser, 'version' => $browser_ver];
    } else {
        return ['browser' => '', 'version' => ''];
    }
}
/**
 * 获取图片完整路径
 * @param string $image
 * @param bool $isdefault 是否显示默认图
 * @return string
 */
function imageToFull($image, $isdefault = true)
{
    if (!$image) {
        if ($isdefault) {
            return config('default_image_url') ?: "";
        } else {
            return "";
        }
    }

    if (stripos($image, config('imgurl')) !== false) {
        return $image;
    }

    return config('imgurl') . $image;
}
/**
 * 
 * @param string $path  文件路径
 * @param string $name
 * @param mixed $value
 */
function fileConfig($path, $name = null, $value = null)
{
    if (!file_exists($path)) {
        return false;
    }

    $config = include $path;

    if (empty($name)) {
        return $config;
    }

    if ($value === null && is_string($name)) {
        return isset($config[$name]) ? $config[$name] : null;
    }
    if (is_array($name)) {
        $config = array_merge($config, $name);
    } elseif (is_string($name)) {
        $config[$name] = $value;
    } else {
        return false;
    }

    $data = '<?php return ' . var_export($config, true) . ';';
    return file_put_contents($path, $data);
}


function getUserTableName($tablename)
{
    return $tablename . '_' . session("mcompanyid");
}


function format_percent($fenzi, $fenmu, $xiaoshuw = 2)
{
    return round($fenmu > 0 ? $fenzi / $fenmu * 100 : 0, $xiaoshuw) . '%';
}

function checkHasRoleid($user, $roleid)
{
    return $user['ismain'] || strpos(',' . $user['roleids'] . ',', ',' . $roleid . ',') !== false;
}

/**
 * 获取当前完整host包含http或https
 * @return string
 */
function ps_getfullhostwithssl()
{
    $http_type = getHttpType();
    return $http_type . $_SERVER['HTTP_HOST'];
}


function getHttpType()
{
    return ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
}

function getFullUrl($url)
{
    $http_type = getHttpType();

    if (strpos($url, $http_type) === 0) {
        return $url;
    }
    return $http_type . $url;
}


/**
 * 生成订单号
 * @param number $uid
 * @param number $companyid
 * @return string
 */
function createOrderNo($uid = 0, $companyid = 0)
{
    return dechex((intval(date('Y')) - 2018) % 10) . strtoupper(dechex(date('m'))) . date('d') . sprintf('%02d', ($uid % 100)) . sprintf('%02d', $companyid % 100) . substr(time(), -5) . substr(microtime(), 2, 4) . sprintf('%02d', rand(0, 99));
}

/**
 * 时间获取
 * @param $str
 * @return array
 */
function getTime($str)
{
    $data = array();
    switch ($str) {
        case "今日":
        case "今天":
            $data['fromtime'] = date('Y-m-d');
            $data['totime'] = date('Y-m-d 23:59:59');
            break;
        case "昨日":
        case "昨天":
            $data['fromtime'] = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - 1, date('Y')));
            $data['totime'] = date('Y-m-d 23:59:59', mktime(0, 0, 0, date('m'), date('d') - 1, date('Y')));
            break;
        case "本周":
            $data['fromtime'] = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - date('w') + 7 - 6, date('Y')));
            $data['totime'] = date('Y-m-d 23:59:59', mktime(0, 0, 0, date('m'), date('d') - date('w') + 7, date('Y')));
            break;
        case "上周":
            $data['fromtime'] = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - date('w') + 1 - 7, date('Y')));
            $data['totime'] = date('Y-m-d 23:59:59', mktime(0, 0, 0, date('m'), date('d') - date('w'), date('Y')));
            break;
        case "本月":
            $data['fromtime'] = date('Y-m-1');
            $data['totime'] = date('Y-m-d 23:59:59', mktime(0, 0, 0, date('m') + 1, 0, date('Y')));
            break;
        case "上月":
            $data['fromtime'] = date('Y-m-d', mktime(0, 0, 0, date('m') - 1, 1, date('Y')));
            $data['totime'] = date('Y-m-d 23:59:59', mktime(0, 0, 0, date('m'), 0, date('Y')));
            break;
        case "本季度":
            $data['fromtime'] = date('Y-m-d', mktime(0, 0, 0, ceil((date('n')) / 3) * 3 - 3 + 1, 1, date('Y')));
            $data['totime'] = date('Y-m-d 23:59:59', mktime(0, 0, 0, ceil((date('n')) / 3) * 3 + 1, 0, date('Y')));
            break;
        case "上季度":
            $data['fromtime'] = date('Y-m-d', mktime(0, 0, 0, (ceil((date('n')) / 3) - 1) * 3 - 3 + 1, 1, date('Y')));
            $data['totime'] = date('Y-m-d 23:59:59', mktime(0, 0, 0, ceil((date('n')) / 3) * 3 - 3 + 1, 0, date('Y')));
            break;
        case "今年":
            $data['fromtime'] = date('Y-1-1');
            $data['totime'] = date('Y-m-d 23:59:59', mktime(0, 0, 0, 1, 0, date('Y') + 1));
            break;
        case "去年":
            $data['fromtime'] = date('Y-1-1', strtotime('-1 year'));
            $data['totime'] = date('Y-m-d 23:59:59', mktime(0, 0, 0, 1, 0, date('Y')));
            break;
        default:
            $data['fromtime'] = date('Y-m-d');
            $data['totime'] = date('Y-m-d H:i:s');
            break;
    }
    return $data;
}
/**
 * 验证验证码是否正确
 * @param integer $code  验证码
 * @param boolean $clear 是否清除验证码
 * @return boolean
 */
function check_validatecode($code, $clear = false)
{
    $sessioncode = session("clientdownloadcode");

    if ($clear) {
        session("clientdownloadcode", null);
    }
    if ($code != $sessioncode) {
        if (config('oa_env') === 'development') {
            return true;
        }
        return false;
    } else {

        return true;
    }
}


/**
 * 输出二维码图片
 */
function echoQrcode($qrcodeurl, $downloadpath = null)
{
    if (empty($qrcodeurl)) {
        echo "";
        die;
    }
    if (empty($downloadpath)) {
        QRcode::png($qrcodeurl, false, null, 30);
    }
    QRcode::png($qrcodeurl, $downloadpath);
}


/**
 * 根据浏览器不同转换字符串编码
 * @param string $name
 * @return string
 */
function changeCodeByBrowser($name)
{
    if (stripos($_SERVER["HTTP_USER_AGENT"], "firefox") !== false) {
        $encode = mb_detect_encoding($name, array("ASCII", 'UTF-8', "GB2312", "GBK", 'BIG5'));
        //$name = $encode !='UTF-8' ? iconv($encode, "UTF-8", $name) : $name;//处理编码及空格截断问题
        $name = $encode != 'UTF-8' ? '"' . iconv($encode, "UTF-8", $name) . '"' : '"' . $name . '"'; //处理编码及空格截断问题
    } elseif (chk_ie_browser()) {
        $name = urlencode($name);
        $name = str_replace('+', '%20', $name); //处理空格变加号问题
    } elseif (stripos($_SERVER["HTTP_USER_AGENT"], "chrome") !== false) {
        $name = urlencode($name);
        $name = str_replace('+', '%20', $name); //处理空格变加号问题
        $name = str_replace('%29', ')', str_replace('%28', '(', $name));
        $name = str_replace('%2B', '+', $name);
    }
    return $name;
}

/**
 * 判断ie浏览器及edge浏览器
 * @return boolean
 */
function chk_ie_browser()
{
    $userbrowser = $_SERVER['HTTP_USER_AGENT'];
    //Log::record($userbrowser, 'log');
    if (preg_match('/MSIE/i', $userbrowser) || preg_match('/Trident\/7.0/i', $userbrowser) || preg_match('/Edge/i', $userbrowser)) {
        $usingie = true;
    } else {
        $usingie = false;
    }
    return $usingie;
}



function getimages($str)
{
    $imgs = array();
    preg_match_all("/(((http|https):)?\/\/[a-z0-9\/\-_+=.~!%@?#%&;:$\\()|]+\.(jpg|gif|png|bmp))/isU", $str, $imgs);

    return array_values(array_unique(array_filter($imgs[0])));
}


/**
 * 过滤跨站脚本
 */
function remove_xss($val)
{
    $val = preg_replace('/([\x00-\x08\x0b-\x0c\x0e-\x19])/', '', $val);

    $search = 'abcdefghijklmnopqrstuvwxyz';
    $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $search .= '1234567890!@#$%^&*()';
    $search .= '~`";:?+/={}[]-_|\'\\';
    for ($i = 0; $i < strlen($search); $i++) {

        $val = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val);

        $val = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val);
    }

    $ra1 = array(
        'javascript',
        'vbscript',
        'expression',
        'applet',
        'meta',
        'xml',
        'blink',
        'script',
        'object',
        'iframe',
        'frame',
        'frameset',
        'ilayer',
        'bgsound'
    );
    $ra2 = array(
        'onabort',
        'onactivate',
        'onafterprint',
        'onafterupdate',
        'onbeforeactivate',
        'onbeforecopy',
        'onbeforecut',
        'onbeforedeactivate',
        'onbeforeeditfocus',
        'onbeforepaste',
        'onbeforeprint',
        'onbeforeunload',
        'onbeforeupdate',
        'onblur',
        'onbounce',
        'oncellchange',
        'onchange',
        'onclick',
        'oncontextmenu',
        'oncontrolselect',
        'oncopy',
        'oncut',
        'ondataavailable',
        'ondatasetchanged',
        'ondatasetcomplete',
        'ondblclick',
        'ondeactivate',
        'ondrag',
        'ondragend',
        'ondragenter',
        'ondragleave',
        'ondragover',
        'ondragstart',
        'ondrop',
        'onerror',
        'onerrorupdate',
        'onfilterchange',
        'onfinish',
        'onfocus',
        'onfocusin',
        'onfocusout',
        'onhelp',
        'onkeydown',
        'onkeypress',
        'onkeyup',
        'onlayoutcomplete',
        'onload',
        'onlosecapture',
        'onmousedown',
        'onmouseenter',
        'onmouseleave',
        'onmousemove',
        'onmouseout',
        'onmouseover',
        'onmouseup',
        'onmousewheel',
        'onmove',
        'onmoveend',
        'onmovestart',
        'onpaste',
        'onpropertychange',
        'onreadystatechange',
        'onreset',
        'onresize',
        'onresizeend',
        'onresizestart',
        'onrowenter',
        'onrowexit',
        'onrowsdelete',
        'onrowsinserted',
        'onscroll',
        'onselect',
        'onselectionchange',
        'onselectstart',
        'onstart',
        'onstop',
        'onsubmit',
        'onunload'
    );
    $ra = array_merge($ra1, $ra2);

    $found = true;
    while ($found == true) {
        $val_before = $val;
        for ($i = 0; $i < sizeof($ra); $i++) {
            $pattern = '/';
            for ($j = 0; $j < strlen($ra[$i]); $j++) {
                if ($j > 0) {
                    $pattern .= '(';
                    $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                    $pattern .= '|';
                    $pattern .= '|(&#0{0,8}([9|10|13]);)';
                    $pattern .= ')*';
                }
                $pattern .= $ra[$i][$j];
            }
            $pattern .= '/i';
            $replacement = substr($ra[$i], 0, 2) . ' ' . substr($ra[$i], 2);
            $val = preg_replace($pattern, $replacement, $val);
            if ($val_before == $val) {

                $found = false;
            }
        }
    }
    return $val;
}




#region 中文转拼音



/**
 * 获取全部拼音，返回拼音的数组,如 '张三丰'  ==>  ['zhang','san','feng']
 * @param $chinese
 * @param string $charset
 * @return array
 */
function get_all_py($chinese, $charset = 'utf-8')
{
    if ($charset != 'gb2312') $chinese = _U2_Utf8_Gb($chinese);
    $py = zh_to_pys($chinese);

    return $py;
}

/**
 * 获取拼音首字母，如['zhang','san','feng']  ==> zsf
 * @param $all_pys
 * @return string
 */
function get_first_py($all_pys)
{
    if (count($all_pys) <= 0) {
        return '';
    }

    $result = [];
    foreach ($all_pys as $one) {
        if (is_null($one) || strlen($one) <= 0) {
            continue;
        }
        $result[] = substr($one, 0, 1);
    }

    return join('', $result);
}

function get_referer()
{
    $url = array_saft_value($_SERVER, "HTTP_REFERER"); //获取完整的来路URL   
    if (empty($url)) {
        return "";
    }
    $str = str_replace("http://", "", $url); //去掉http://   

    $str = str_replace("https://", "", $str); //去掉http://   
    $strdomain = explode("/", $str); // 以“/”分开成数组   

    $domain = $strdomain[0]; //取第一个“/”以前的字符  

    $strdomain = explode($domain, $url); // 以“/”分开成数组

    return $strdomain[0] . $domain;
}

function ps_area_pos($areaid, $str = ' &raquo; ', $deep = 0)
{
    if ($areaid) {
        global $AREA;
    } else {
        global $L;
        return $L['allcity'];
    }
    $areaModel = new PluginAreaModel();
    $AREA or $AREA = $areaModel::getCache();
    $arrparentid = $AREA[$areaid]['arrparentid'] ? explode(',', $AREA[$areaid]['arrparentid']) : array();
    $arrparentid[] = $areaid;
    $pos = '';
    if ($deep) $i = 1;
    foreach ($arrparentid as $areaid) {
        if (!$areaid || !isset($AREA[$areaid])) continue;
        if ($deep) {
            if ($i > $deep) continue;
            $i++;
        }
        $pos .= $AREA[$areaid]['areaname'] . $str;
    }
    $_len = strlen($str);
    if ($str && substr($pos, -$_len, $_len) === $str) $pos = substr($pos, 0, strlen($pos) - $_len);
    return $pos;
}

/**
 * @param $lat1
 * @param $lon1
 * @param $lat2
 * @param $lon2
 * @param float $radius  星球半径
 * @return float
 */
function distance($lat1, $lon1, $lat2, $lon2, $radius = 6378.137)
{
    $rad = floatval(M_PI / 180.0);

    $lat1 = floatval($lat1) * $rad;
    $lon1 = floatval($lon1) * $rad;
    $lat2 = floatval($lat2) * $rad;
    $lon2 = floatval($lon2) * $rad;

    $theta = $lon2 - $lon1;

    $dist = acos(
        sin($lat1) * sin($lat2) +
            cos($lat1) * cos($lat2) * cos($theta)
    );

    if ($dist < 0) {
        $dist += M_PI;
    }

    return $dist = $dist * $radius;
}

/**
 * 获取拼音首字母，如['zhang','san','feng']  ==> z
 * @param $all_pys
 * @return string
 */
function get_first_letter($all_pys)
{
    if (count($all_pys) <= 0) {
        return '';
    }

    foreach ($all_pys as $one) {
        if (is_null($one) || strlen($one) <= 0) {
            continue;
        }
        return substr($one, 0, 1);
    }

    return '';
}

function _U2_Utf8_Gb($_C)
{
    $_String = '';
    if ($_C < 0x80) $_String .= $_C;
    elseif ($_C < 0x800) {
        $_String .= chr(0xC0 | $_C >> 6);
        $_String .= chr(0x80 | $_C & 0x3F);
    } elseif ($_C < 0x10000) {
        $_String .= chr(0xE0 | $_C >> 12);
        $_String .= chr(0x80 | $_C >> 6 & 0x3F);
        $_String .= chr(0x80 | $_C & 0x3F);
    } elseif ($_C < 0x200000) {
        $_String .= chr(0xF0 | $_C >> 18);
        $_String .= chr(0x80 | $_C >> 12 & 0x3F);
        $_String .= chr(0x80 | $_C >> 6 & 0x3F);
        $_String .= chr(0x80 | $_C & 0x3F);
    }
    return iconv('UTF-8', 'GBK//IGNORE', $_String);
}

function zh_to_py($num, $blank = '')
{
    $dict_list = array(
        'a' => -20319, 'ai' => -20317, 'an' => -20304, 'ang' => -20295, 'ao' => -20292,
        'ba' => -20283, 'bai' => -20265, 'ban' => -20257, 'bang' => -20242, 'bao' => -20230, 'bei' => -20051, 'ben' => -20036, 'beng' => -20032, 'bi' => -20026, 'bian' => -20002, 'biao' => -19990, 'bie' => -19986, 'bin' => -19982, 'bing' => -19976, 'bo' => -19805, 'bu' => -19784,
        'ca' => -19775, 'cai' => -19774, 'can' => -19763, 'cang' => -19756, 'cao' => -19751, 'ce' => -19746, 'ceng' => -19741, 'cha' => -19739, 'chai' => -19728, 'chan' => -19725, 'chang' => -19715, 'chao' => -19540, 'che' => -19531, 'chen' => -19525, 'cheng' => -19515, 'chi' => -19500, 'chong' => -19484, 'chou' => -19479, 'chu' => -19467, 'chuai' => -19289, 'chuan' => -19288, 'chuang' => -19281, 'chui' => -19275, 'chun' => -19270, 'chuo' => -19263, 'ci' => -19261, 'cong' => -19249, 'cou' => -19243, 'cu' => -19242, 'cuan' => -19238, 'cui' => -19235, 'cun' => -19227, 'cuo' => -19224,
        'da' => -19218, 'dai' => -19212, 'dan' => -19038, 'dang' => -19023, 'dao' => -19018, 'de' => -19006, 'deng' => -19003, 'di' => -18996, 'dian' => -18977, 'diao' => -18961, 'die' => -18952, 'ding' => -18783, 'diu' => -18774, 'dong' => -18773, 'dou' => -18763, 'du' => -18756, 'duan' => -18741, 'dui' => -18735, 'dun' => -18731, 'duo' => -18722,
        'e' => -18710, 'en' => -18697, 'er' => -18696,
        'fa' => -18526, 'fan' => -18518, 'fang' => -18501, 'fei' => -18490, 'fen' => -18478, 'feng' => -18463, 'fo' => -18448, 'fou' => -18447, 'fu' => -18446,
        'ga' => -18239, 'gai' => -18237, 'gan' => -18231, 'gang' => -18220, 'gao' => -18211, 'ge' => -18201, 'gei' => -18184, 'gen' => -18183, 'geng' => -18181, 'gong' => -18012, 'gou' => -17997, 'gu' => -17988, 'gua' => -17970, 'guai' => -17964, 'guan' => -17961, 'guang' => -17950, 'gui' => -17947,
        'gun' => -17931, 'guo' => -17928,
        'ha' => -17922, 'hai' => -17759, 'han' => -17752, 'hang' => -17733, 'hao' => -17730, 'he' => -17721, 'hei' => -17703, 'hen' => -17701, 'heng' => -17697, 'hong' => -17692, 'hou' => -17683, 'hu' => -17676, 'hua' => -17496, 'huai' => -17487, 'huan' => -17482, 'huang' => -17468, 'hui' => -17454,
        'hun' => -17433, 'huo' => -17427,
        'ji' => -17417, 'jia' => -17202, 'jian' => -17185, 'jiang' => -16983, 'jiao' => -16970, 'jie' => -16942, 'jin' => -16915, 'jing' => -16733, 'jiong' => -16708, 'jiu' => -16706, 'ju' => -16689, 'juan' => -16664, 'jue' => -16657, 'jun' => -16647,
        'ka' => -16474, 'kai' => -16470, 'kan' => -16465, 'kang' => -16459, 'kao' => -16452, 'ke' => -16448, 'ken' => -16433, 'keng' => -16429, 'kong' => -16427, 'kou' => -16423, 'ku' => -16419, 'kua' => -16412, 'kuai' => -16407, 'kuan' => -16403, 'kuang' => -16401, 'kui' => -16393, 'kun' => -16220, 'kuo' => -16216,
        'la' => -16212, 'lai' => -16205, 'lan' => -16202, 'lang' => -16187, 'lao' => -16180, 'le' => -16171, 'lei' => -16169, 'leng' => -16158, 'li' => -16155, 'lia' => -15959, 'lian' => -15958, 'liang' => -15944, 'liao' => -15933, 'lie' => -15920, 'lin' => -15915, 'ling' => -15903, 'liu' => -15889,
        'long' => -15878, 'lou' => -15707, 'lu' => -15701, 'lv' => -15681, 'luan' => -15667, 'lue' => -15661, 'lun' => -15659, 'luo' => -15652,
        'ma' => -15640, 'mai' => -15631, 'man' => -15625, 'mang' => -15454, 'mao' => -15448, 'me' => -15436, 'mei' => -15435, 'men' => -15419, 'meng' => -15416, 'mi' => -15408, 'mian' => -15394, 'miao' => -15385, 'mie' => -15377, 'min' => -15375, 'ming' => -15369, 'miu' => -15363, 'mo' => -15362, 'mou' => -15183, 'mu' => -15180,
        'na' => -15165, 'nai' => -15158, 'nan' => -15153, 'nang' => -15150, 'nao' => -15149, 'ne' => -15144, 'nei' => -15143, 'nen' => -15141, 'neng' => -15140, 'ni' => -15139, 'nian' => -15128, 'niang' => -15121, 'niao' => -15119, 'nie' => -15117, 'nin' => -15110, 'ning' => -15109, 'niu' => -14941,
        'nong' => -14937, 'nu' => -14933, 'nv' => -14930, 'nuan' => -14929, 'nue' => -14928, 'nuo' => -14926,
        'o' => -14922, 'ou' => -14921,
        'pa' => -14914, 'pai' => -14908, 'pan' => -14902, 'pang' => -14894, 'pao' => -14889, 'pei' => -14882, 'pen' => -14873, 'peng' => -14871, 'pi' => -14857, 'pian' => -14678, 'piao' => -14674, 'pie' => -14670, 'pin' => -14668, 'ping' => -14663, 'po' => -14654, 'pu' => -14645,
        'qi' => -14630, 'qia' => -14594, 'qian' => -14429, 'qiang' => -14407, 'qiao' => -14399, 'qie' => -14384, 'qin' => -14379, 'qing' => -14368, 'qiong' => -14355, 'qiu' => -14353, 'qu' => -14345, 'quan' => -14170, 'que' => -14159, 'qun' => -14151,
        'ran' => -14149, 'rang' => -14145, 'rao' => -14140, 're' => -14137, 'ren' => -14135, 'reng' => -14125, 'ri' => -14123, 'rong' => -14122, 'rou' => -14112, 'ru' => -14109, 'ruan' => -14099, 'rui' => -14097, 'run' => -14094, 'ruo' => -14092,
        'sa' => -14090, 'sai' => -14087, 'san' => -14083, 'sang' => -13917, 'sao' => -13914, 'se' => -13910, 'sen' => -13907, 'seng' => -13906, 'sha' => -13905, 'shai' => -13896, 'shan' => -13894, 'shang' => -13878, 'shao' => -13870, 'she' => -13859, 'shen' => -13847, 'sheng' => -13831, 'shi' => -13658, 'shou' => -13611, 'shu' => -13601, 'shua' => -13406, 'shuai' => -13404, 'shuan' => -13400, 'shuang' => -13398, 'shui' => -13395, 'shun' => -13391, 'shuo' => -13387, 'si' => -13383, 'song' => -13367, 'sou' => -13359, 'su' => -13356, 'suan' => -13343, 'sui' => -13340, 'sun' => -13329, 'suo' => -13326,
        'ta' => -13318, 'tai' => -13147, 'tan' => -13138, 'tang' => -13120, 'tao' => -13107, 'te' => -13096, 'teng' => -13095, 'ti' => -13091, 'tian' => -13076, 'tiao' => -13068, 'tie' => -13063, 'ting' => -13060, 'tong' => -12888, 'tou' => -12875, 'tu' => -12871, 'tuan' => -12860, 'tui' => -12858, 'tun' => -12852, 'tuo' => -12849,
        'wa' => -12838, 'wai' => -12831, 'wan' => -12829, 'wang' => -12812, 'wei' => -12802, 'wen' => -12607, 'weng' => -12597, 'wo' => -12594, 'wu' => -12585,
        'xi' => -12556, 'xia' => -12359, 'xian' => -12346, 'xiang' => -12320, 'xiao' => -12300, 'xie' => -12120, 'xin' => -12099, 'xing' => -12089, 'xiong' => -12074, 'xiu' => -12067, 'xu' => -12058, 'xuan' => -12039, 'xue' => -11867, 'xun' => -11861,
        'ya' => -11847, 'yan' => -11831, 'yang' => -11798, 'yao' => -11781, 'ye' => -11604, 'yi' => -11589, 'yin' => -11536, 'ying' => -11358, 'yo' => -11340, 'yong' => -11339, 'you' => -11324, 'yu' => -11303, 'yuan' => -11097, 'yue' => -11077, 'yun' => -11067,
        'za' => -11055, 'zai' => -11052, 'zan' => -11045, 'zang' => -11041, 'zao' => -11038, 'ze' => -11024, 'zei' => -11020, 'zen' => -11019, 'zeng' => -11018, 'zha' => -11014, 'zhai' => -10838, 'zhan' => -10832, 'zhang' => -10815, 'zhao' => -10800, 'zhe' => -10790, 'zhen' => -10780, 'zheng' => -10764, 'zhi' => -10587, 'zhong' => -10544, 'zhou' => -10533, 'zhu' => -10519, 'zhua' => -10331, 'zhuai' => -10329, 'zhuan' => -10328, 'zhuang' => -10322, 'zhui' => -10315, 'zhun' => -10309, 'zhuo' => -10307, 'zi' => -10296, 'zong' => -10281, 'zou' => -10274, 'zu' => -10270, 'zuan' => -10262,
        'zui' => -10260, 'zun' => -10256, 'zuo' => -10254
    );

    if ($num > 0 && $num < 160) {
        return chr($num);
    } elseif ($num < -20319 || $num > -10247) {
        return $blank;
    } else {
        foreach ($dict_list as $py => $code) {
            if ($code > $num) break;
            $result = $py;
        }
        return $result;
    }
}

function zh_to_pys($chinese)
{
    $result = array();
    for ($i = 0; $i < strlen($chinese); $i++) {
        $p = ord(substr($chinese, $i, 1));
        if ($p > 160) {
            $q = ord(substr($chinese, ++$i, 1));
            $p = $p * 256 + $q - 65536;
        }
        $result[] = zh_to_py($p);
    }
    return $result;
}

// 过滤掉emoji表情
function filter_Emoji($str)
{
    $str = preg_replace_callback(    //执行一个正则表达式搜索并且使用一个回调进行替换
        '/./u',
        function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
        },
        $str
    );

    return $str;
}

#endregion
