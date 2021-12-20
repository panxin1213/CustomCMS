<?php
//2021-12-20 19:31:35
namespace app\common\searchmodel;

class AdminLoginLogSearchModel extends \app\core\pager\SearchModel
{
    /**
     * where语句绑定方法
     * @return array
     */
    protected function BindWhere($param){
        $where = array();

        if(isset($param['islock']) && $param['islock'] !== ''){
            $where['islock'] = $param['islock'];
        }
        
        return $where;
    }
    
    /**
     * 获取当前SeachModel的数据模型
     * @return \think\Model
     */
    protected function getModel(){
        return null;
    }
    
    
    /**
     * 获取排序规则
     * @return string
     */
    protected function getOrderString(){
        return "";
    }
}