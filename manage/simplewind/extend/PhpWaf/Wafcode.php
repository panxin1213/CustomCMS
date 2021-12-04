<?php
namespace PhpWaf;

use think\Request;


class Wafcode
{
    public static function valid()
    {
        $request = Request::instance();
        $arr = array_merge($_GET, $_POST, $request->header());

        $regex = '/(\b|\s|%\w{2})(?:php_uname|ini_get|ini_set|define|eval|getenv|ini_get|ini_set|get_current_user|forward_static_call_array|forward_static_call|create_function|func_get_args?|register_\w+_function|function_exists|get_defined_functions|call_user_func|call_user_func_array|get_cfg_var|class_exists|urldecode|urlenecode|addslashes|addcslashes|set_time_limit|base64_decode|base64_encode|popen|pclose|proc_open|chmod|chroot|gzinflate|fopen|fread|fclose|fwrite|opendir|readdir|fsockopen|closedir|ob_get_contents|ob_clean|file_put_contents|file_get_contents|\w+sql_connect|\w+sql_create_db|\w+sql_query|include|require|require_once|shell_exec|phpinfo|phpversion|putenv|get_magic_quotes_gpc|pack|passthru|preg_\w+|exec|execute|echo|print|print_r|var_dump|(fp)open|alert|showmodaldialog|magic_quotes|benchmark|unlink|assert|extractvalue|extract|updatexml|socket_create|show_source|syslog|escapeshellcmd|proc_open|scandir|pcntl_exec|chgrp|proc_get_status|ini_alter|ini_alter|ini_restore|openlog|readlink|symlink|popepassthru|stream_socket_server|update_avatar|response\.write)\b/ui';
        foreach ($arr as $k => $v) {
            if (preg_match($regex, $k)) {
                self::response404();
            }

            if(is_array($v)){
                $v = json_encode($v, JSON_UNESCAPED_UNICODE);
            }

            if (preg_match($regex, $v)) {
                self::response404();
            }
        }
    }

    private static function response404()
    {
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        exit;
    }
}

