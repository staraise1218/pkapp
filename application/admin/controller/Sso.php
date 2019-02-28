<?php
/**
 * 功能未开发
 * ============================================================================
 * @Author: lhb
 */

namespace app\admin\controller;

use think\Controller;
use app\common\logic\Saas;

class Sso extends Controller
{
    public function logout()
    {
        $ssoToken = input('sso_token', '');

        $return = Saas::instance()->ssoLogout($ssoToken);

        ajaxReturn($return);
    }
}