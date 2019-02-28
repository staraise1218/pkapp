<?php

namespace app\api\controller;

use think\Db;
use app\api\logic\RongyunLogic;

// include PLUGIN_PATH.'rongyun/rongcloud.php';

class Rongyun extends Base {

	public function __construct(){
		// 设置所有方法的默认请求方式
		$this->method = 'POST';

		parent::__construct();
	}

	public function getToken(){
		$user_id = I('user_id');
		$user = M('users')->where('user_id', $user_id)->field('nickname, head_pic, rongyun_token')->find();
		if(empty($user)) response_error('', '该用户不存在');

		// if($user['rongyun_token']) response_success(array('token'=>$user['rongyun_token']));

		ini_set('safe_mode','Off');
		// 获取 Token 方法
		$RongyunLogic = new RongyunLogic();
		$host_url = config('host_url');
		$rongyun_token = $RongyunLogic->getToken($user_id, $user['nickname'], $user['head_pic']);
		if($rongyun_token){
			response_success(array('token'=>$rongyun_token));
		} else {
			response_error('', '获取失败');
		}
	}

	// 发送系统消息
	public function PublishSystemMessage(){
		$RongyunLogic = new RongyunLogic();
		$content = json_encode(array('content' => '收到请回复', 'extra' => '你说什么'));
		$result = $RongyunLogic->PublishSystemMessage('5', ["1", "9" ,"19", "20", "2"], $content);
	}

	// 发送系统消息
	public function PublishPrivateMessage(){
		

		$RongyunLogic = new RongyunLogic();
		$result = $RongyunLogic->PublishPrivateMessage('5', $users, $content);
	}
}