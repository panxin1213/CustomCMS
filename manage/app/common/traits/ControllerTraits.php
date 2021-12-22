<?php

namespace app\common\traits;

use think\Response;
use think\exception\HttpResponseException;
use think\facade\View;
use think\Request;



trait ControllerTraits
{

    protected function sendresposne($result)
    {
        $type = $this->getResponseType();

        $response = Response::create($result, $type);
        
        $header = $this->GetResponseAccessHeader();
        $response = $response->header($header);
        throw new HttpResponseException($response);
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
            $before_hosts = explode(',', 'http://localhost:8000,http://localhost:3000,http://local.shangwebb.com,http://localhost:4000,http://localhost:4001,http://shangmanage-member.qa8.chinabm.cn,http://ssm.chinajumei.cn:18888,http://shangmanage.qa8.chinabm.cn,http://sgj-me-test.chinajumei.cn,http://sgj.chinajumei.cn,http://sgj-admin-test.chinajumei.cn,http://sgj.local.chinabm.cn,https://sgj-m.chinajumei.cn,https://sgj.chinajumei.cn,http://sgj-test.chinajumei.cn,https://sgj-m.chinafloor.cn');
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

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param mixed $msg 提示信息
     * @param mixed $data 返回的数据
     * @param array $header 发送的Header信息
     * @return void
     */
    protected function success($msg = '', $url = null, $data = '', $wait = 3, array $header = [])
    {
        $code = 1;

        if (method_exists($this, 'dataInit')) {
            $data = $this->dataInit($data);
        }

        $result = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ];

        if (method_exists($this, 'resultInit')) {
            $result = $this->resultInit($result);
        }

        $this->sendresposne($result);
    }

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param mixed $msg 提示信息,若要指定错误码,可以传数组,格式为['code'=>您的错误码,'msg'=>'您的错误消息']
     * @param mixed $data 返回的数据
     * @param array $header 发送的Header信息
     * @return void
     */
    protected function error($msg = '', $url = null, $data = '', $wait = 3, array $header = [])
    {
        $code = 0;
        if (is_array($msg)) {
            $code = $msg['code'];
            $msg = $msg['msg'];
        }

        if (function_exists('dataInit')) {
            $data = $this->dataInit($data);
        }
        $result = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ];

        if (method_exists($this, 'resultInit')) {
            $result = $this->resultInit($result);
        }

        $this->sendresposne($result);
    }


    /**
     * 输出401未登陆信息
     * @return void
     */
    protected function Response401()
    {
        header("HTTP/1.1 401 Unauthorized");
        $headers = $this->GetResponseAccessHeader();

        if (!empty($headers)) {
            unset($k);
            unset($v);
            foreach ($headers as $k => $v) {
                header($k . ': ' . $v);
            }
        }
        exit();
    }
}
