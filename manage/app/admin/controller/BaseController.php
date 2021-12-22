<?php

namespace app\admin\controller;

use app\admin\service\AdminService;
use think\Controller;
use app\common\traits\ControllerTraits;
use think\Config;

class BaseController extends Controller
{
    use ControllerTraits;

    public $admin;
    protected function _initialize()
    {
        $this->admin = session("admin");

        if (empty($this->admin)) {
            $this->error(['code' => 10001, 'msg' => '请登录']);
        } else {
            $sv = new AdminService();
            $admin = $sv->Get($this->admin['id']);
            if (!$admin || $admin['islock']) {
                session("admin", null);
                $this->error(['code' => 10002, 'msg' => '账号已锁定']);
            } else {
                unset($admin['password']);
                session("admin", $admin);
                $this->admin = $admin;
            }
        }
    }


    
    protected function getResponseType()
    {
        return Config::get('default_ajax_return');
    }
}
