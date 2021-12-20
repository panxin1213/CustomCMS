<?php

namespace app\admin\controller;

use app\admin\service\AdminLoginLogService;
use app\admin\service\AdminService;
use app\admin\service\MenusService;
use app\admin\validate\LoginValidate;
use think\Config;
use think\Controller;
use app\common\traits\ControllerTraits;
use cmf\lib\Upload;

class LoginController extends Controller
{
    use ControllerTraits;


    public function index($param = [])
    {
        $validate = new LoginValidate();

        $param = $this->request->param();

        $scene = array_saft_value($param, 'type') ?: 'account';

        $result = $validate->check($param, [], $scene);

        $loginlog = [
            'jsonString'    => \json_encode($param, JSON_UNESCAPED_UNICODE),
            'ip'            => get_client_ip(0, true),
            'explorer'      => $_SERVER['HTTP_USER_AGENT']
        ];

        $allsv = new AdminLoginLogService();


        if (!$result) {
            $errs = $validate->getError();

            if (is_array($errs)) {
                $errs = join(",", $errs);
            }
            if (!empty($validate->um)) {
                $loginlog['adminid'] = $validate->um['id'];
                $loginlog['status'] = 'fail';
                $loginlog['errmsg'] = $errs;

                $allsv->Insert($loginlog);
            }

            $this->error($validate->getError());
        } else {
            $um = $validate->um;
            //$cm = $validate->cm;

            $um['last_login_time'] = date('Y-m-d H:i:s');
            $um['last_login_ip'] = get_client_ip(0, true);
            $loginlog['adminid'] = $um['id'];
            $loginlog['status'] = 'SUCCESS';

            $um['loginloglist'] = [];
            $um['loginloglist'][] = $loginlog;

            $asv = new AdminService();

            $result = $asv->saveLoginInfo($um, null);

            $msv = new MenusService();

            $ml = $msv->GetList(['islock' => 0], 0);

            if ($result === true) {
                session('admin', $um);
                session('ADMIN_ID', $um['id']);
                unset($um['password']);
                if ($um['id'] === 1) {
                    $um['menuids'] = array_column($ml, 'id');
                }
                $this->success('SUCCESS', null, ['usermodel' => $um, "menulist" => $ml]);
            } else {
                $this->error($result['error']);
            }
        }
    }



    public function uploadimg()
    {
        $header = $this->GetResponseAccessHeader();

        if (!empty($header)) {
            unset($k);
            unset($v);
            foreach ($header as $k => $v) {
                header($k . ': ' . $v);
            }
        }

        if ($this->request->isPost()) {

            $uploader = new Upload();

            $uploader->setFormName('file');
            $uploader->setFileType('image');

            $result = $uploader->upload();

            if ($result === false) {
                $this->error($uploader->getError());
            } else {
                $this->success("上传成功!", '', $result);
            }
        }
    }


    protected function getResponseType()
    {
        return Config::get('default_ajax_return');
    }
}
