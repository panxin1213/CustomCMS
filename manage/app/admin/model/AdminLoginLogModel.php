<?php
//2021-12-20 19:31:35
namespace app\admin\model;

use think\Model;
use think\Cache;
use app\admin\traits\ModuleModelTraits;
use app\core\traits\ModelTraits;

class AdminLoginLogModel extends \app\common\model\AdminLoginLogModel
{
    use ModuleModelTraits;
    use ModelTraits;
}
