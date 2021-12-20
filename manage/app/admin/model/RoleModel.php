<?php
//2021-12-10 17:22:02
namespace app\admin\model;

use think\Model;
use think\Cache;
use app\admin\traits\ModuleModelTraits;

class RoleModel extends \app\common\model\RoleModel
{
    use ModuleModelTraits;

    public function menulist()
    {
        $hasmany = $this->hasMany('RoleMenusModel', 'roleid', 'id');

        return $hasmany;
    }


    // JsonSerializable
    public function jsonSerialize()
    {
        $arr = $this->toArray();
        $arr['menuids'] = array_column($arr['menulist'], 'menuid') ?: [];
        return $arr;
    }


    public function editusermodel()
    {
        $hasone =  $this->hasOne("AdminModel", "id", "edit_uid");

        return $hasone;
    }
}
