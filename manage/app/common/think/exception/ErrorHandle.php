<?php

namespace app\common\think\exception;

use think\Db;
use Exception;
use think\exception\ErrorException;
use think\Log;
use think\Request;

class ErrorHandle extends \think\exception\Handle
{


    /**
     * Report or log an exception.
     *
     * @param  \Exception $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        if ($exception instanceof ErrorException) {
            if (in_array($exception->getSeverity(), [2, 8, 1024, 8192])) {
                return;
            }
        }
        $headers = $this->GetResponseAccessHeader();

        if (!empty($headers)) {
            unset($k);
            unset($v);
            foreach ($headers as $k => $v) {
                header($k . ': ' . $v);
            }
        }

        $request = Request::instance();
        if ($request->isAjax() || strpos($request->header("accept"), 'application/json') !== false) {
            Log::error($exception);
            echo '{"code":0,"msg":"' . $exception->getMessage() . '"}';
            exit();
        }

        parent::report($exception);
    }

    protected function GetResponseAccessHeader()
    {
        $request = null;
        if ($this->request) {
            $request = $this->request;
        } else {
            $request = Request::instance();
        }
        $origin = $request->header("Origin");
        $header = [];
        if (!empty($origin)) {
            $before_hosts = explode(',', config('before_hosts'));
            if (!in_array($origin, $before_hosts)) {
                $origin = '';
            }
            $header['Access-Control-Allow-Origin'] = $origin;
            $header['Access-Control-Allow-Headers'] = 'X-Requested-With,Content-Type,Access-Control-Allow-Origin';
            $header['Access-Control-Allow-Methods'] = 'GET,POST,PATCH,PUT,DELETE,OPTIONS';
            $header['Access-Control-Allow-Credentials'] = "true";
        }

        return $header;
    }
}
