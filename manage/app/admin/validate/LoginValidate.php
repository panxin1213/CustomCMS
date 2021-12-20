<?php
//2019-10-10 13:31:25
namespace app\admin\validate;

use app\core\validate\BaseValidate;
use app\admin\service\AdminService;


class LoginValidate extends BaseValidate
{

    public $um = null;

    public $cm = null;

    function __construct()
    {
        $this->rule = [
            'mobile|手机号码' => 'require|telephone|validuser',
            'username|账号' => 'require|validusername',
            //'password|验证码' => 'require|validcode',
            'password|密码' => 'require|validpassword'
        ];

        $this->message = [];

        $this->scene = [
            'mobile'    => 'mobile,password',
            'account'  => 'username,password'
        ];
    }

    /**
     * 验证密码
     */
    protected function validpassword($value, $rule, $data)
    {
        if (empty($this->um)) {
            return '用户不存在或已删除';
        }
        if ($this->um['password'] != \cmf_password(array_saft_value($data, 'password'))) {
            $this->um = null;
            return '密码错误';
        }
        return true;
    }
    /**
     * 验证用户
     */
    protected function validuser($value, $rule, $data)
    {
        $asv = new AdminService();
        $asv->SetWith("rightlist,rolemodel");

        $um = $asv->Get(['mobile' => $value]);

        if (empty($um)) {
            return '用户不存在或已删除';
        }

        if ($um['islock']) {
            return '用户已锁定，请联系管理员';
        }

        $this->um = $um;

        return true;
    }


    // /**
    //  * 验证验证码
    //  */
    // protected function validcode($value, $rule, $data)
    // {
    //     if ($value == '159357') {
    //         return true;
    //     }

    //     $slsv = new SmsLogService();

    //     $m = $slsv->Get(['mobile' => $data['mobile'], 'isuse' => 0, 'type' => 1], 'id desc');

    //     if (empty($m)) {
    //         return '验证码错误，请重新获取';
    //     }

    //     if ($m['msg'] != $value) {
    //         return '验证码错误。';
    //     }

    //     $this->cm = $m;

    //     return true;
    // }

    /**
     * 验证用户
     */
    protected function validusername($value, $rule, $data)
    {
        $asv = new AdminService();
        $asv->SetWith("rolemodel");

        $um = $asv->Get(['username' => $value]);

        if (empty($um)) {
            return '用户不存在或已删除';
        }

        if ($um['islock']) {
            return '用户已锁定，请联系管理员';
        }

        $this->um = $um;

        return true;
    }
}
