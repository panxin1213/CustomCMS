<?php
//2021-12-20 19:31:35
namespace app\common\service;

use think\Db;
use think\Config;
use think\Log;

class AdminLoginLogService extends \app\core\service\BaseService
{
    public function __construct()
    {
        parent::__construct();
    }
}