<?php

namespace app\admin\controller;

use app\admin\service\AdminService;
use app\admin\service\MenusService;

class IndexController extends BaseController
{

    public function getuser()
    {
        $msv = new MenusService();

        $ml = $msv->GetList(['islock' => 0], 0);


        $asv = new AdminService();
        $asv->SetWith("rolemodel");

        $um = $asv->Get($this->admin['id']);
        unset($um['password']);
        if ($um['id'] === 1) {
            $um['menuids'] = array_column($ml, 'id');
        }

        $this->success('SUCCESS', null, ['user' => $um, "menulist" => $ml]);
    }
}
