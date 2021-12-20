<?php
//2021-12-08 16:56:57
namespace app\admin\service;

use think\Db;
use think\Config;
use think\Log;

class AdminService extends \app\common\service\AdminService
{
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 通过用户名集合获取未锁定用户字典（username:id）
     * @param array $names
     * @return array
     */
    public function getNameIdDicByNames($names)
    {
        $list = $this->GetList(['names' => $names, 'islock' => 0], 0);

        return array_column($list, 'id', 'username');
    }

    /**
     * 修改信息
     * @param array $data 修改数据
     * @param array $where 修改条件
     * @return array|bool|int
     */
    public function Update($data, $where = array(), $isSetAttr = null)
    {
        $model = $this->getModel();

        $model->db()->startTrans();

        try {
            $arsv = new AdminRoleService();

            $arsv->Delete(['adminid' => $data['id']]);

            $result = parent::Update($data, $where);

            if ($this->resultIsError($result) === true) {
                $model->db()->commit();
            } else {
                $model->db()->rollback();
            }

            return $result;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $model->db()->rollback();
            return ['error' => $e->getMessage()];
        }
    }



    /**
     * 登录方法
     * @param array $um 用户对象
     * @param array $cm 验证码对象
     * @return array|true
     */
    public function saveLoginInfo($um)
    {
        $this->SetTogether("loginloglist");

        $model = $this->getModel();

        $model->db()->startTrans();

        try {
            $result = parent::Update($um);

            if ($this->resultIsError($result) === true) {
                $model->db()->commit();
                return true;
            }

            $model->db()->rollback();
            return ['error' => '登录失败'];
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $model->db()->rollback();
            return ['error' => $e->getMessage()];
        }
    }
}
