<?php

namespace app\mobile\controller;

use think\Db;
use think\Config;

 
class Local extends Base {
	// 登录成功后加载的隐藏页面 用于前端保存用户信息
	public function saveUserInfo(){
		$user_id = I('user_id');

		$userinfo =  M('users')->where('user_id', $user_id)->find();
		unset($userinfo['password']);
		$userinfo['age'] = getAge($userinfo['birthday']);

		$this->assign('userinfo', json_encode($userinfo));
		return $this->fetch();
	}

	public function downapp(){
		return $this->fetch();
	}
}

