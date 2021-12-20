<?php
//2021-12-10 17:22:02
namespace app\admin\model;

use think\Model;
use think\Cache;
use app\admin\traits\ModuleModelTraits;

class AdminRoleModel extends \app\common\model\AdminRoleModel
{
    use ModuleModelTraits;

    public function rolemodel()
    {
        $hasone = $this->hasOne("RoleModel", "id", "roleid");

        $hasone->getQuery()->with('menulist');

        return $hasone;
    }
}
