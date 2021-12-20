<?php
//2021-12-08 16:56:57
namespace app\admin\model;

use think\Model;
use think\Cache;
use app\admin\traits\ModuleModelTraits;

class AdminModel extends \app\common\model\AdminModel
{
    use ModuleModelTraits;

    public function rolelist()
    {
        $hasmany =  $this->hasMany('AdminRoleModel', 'adminid', 'id');
        $hasmany->getQuery()->with('rolemodel');

        return $hasmany;
    }

    public function rolemodel()
    {
        $hasone =  $this->hasOne('AdminRoleModel', 'adminid', 'id');
        $hasone->getQuery()->with('rolemodel');

        return $hasone;
    }

    public function getroleidAttr()
    {
        if (!empty($this['rolemodel'])) {
            return $this['rolemodel']['roleid'];
        } else {
            return 0;
        }
    }

    public function setroleidAttr($value)
    {
        $this['rolelist'] = [['roleid' => $value]];
        return $value;
    }

    public function getrolenameAttr()
    {
        if (!empty($this['rolemodel']) && !empty($this['rolemodel']['rolemodel'])) {
            return $this['rolemodel']['rolemodel']['rolename'];
        } else {
            return '';
        }
    }

    // JsonSerializable
    public function jsonSerialize()
    {
        $arr = $this->toArray();

        if (!empty($this['roleid'])) {
            $arr['roleid'] = $this['roleid'];
        }
        if (!empty($this['rolename'])) {
            $arr['rolename'] = $this['rolename'];
        }

        if (isset($arr['roleid'])) {
            $arr['rolelist'] = [['roleid' => $arr['roleid']]];
        }

        if (!empty($arr['rolemodel']['rolemodel']['rightlist']) && !$arr['rolemodel']['rolemodel']['islock']) {
            $arr['rightlist'] = $arr['rolemodel']['rolemodel']['rightlist'];
        }
        $arr['menuids'] = [];
        if (!empty($arr['rolemodel']['rolemodel']['menulist']) && !$arr['rolemodel']['rolemodel']['islock']) {
            $arr['menuids'] = array_column($arr['rolemodel']['rolemodel']['menulist'], 'menuid');
        }

        return $arr;
    }



    public function addusermodel()
    {
        $hasone =  $this->hasOne("AdminModel", "id", "add_uid");

        return $hasone;
    }


    public function editusermodel()
    {
        $hasone =  $this->hasOne("AdminModel", "id", "edit_uid");

        return $hasone;
    }



    public function loginloglist()
    {
        $hasmany =  $this->hasMany('AdminLoginLogModel', 'adminid', 'id');

        return $hasmany;
    }
}
