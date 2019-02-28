<?php

namespace app\api\controller;
use think\Db;
use app\api\logic\MessageLogic;
use app\api\logic\RongyunLogic;

class Gift extends Base {

	public function __construct(){
		// 设置所有方法的默认请求方式
		$this->method = 'POST';

		parent::__construct();
	}

	public function alllist(){
		$user_id = I('user_id');

		$user = M('users')->where('user_id', $user_id)->field('goldcoin')->find();

		$gift = M('gift')->where('is_delete', 0)
				->where('is_delete', 0)
				->order('sort desc, id desc')
				->field('id, image, price, glamour')
				->select();

		$result['user'] = $user;
		$result['gift'] = $gift;

		response_success($result);
	}

	// 赠送礼物
	public function give(){
		$user_id = I('user_id');
		$to_user_id = I('to_user_id');
		$gift_id = I('gift_id');

		$user = M('users')->where('user_id', $user_id)->field('goldcoin')->find();
		$giftinfo = M('gift')->where('id', $gift_id)->find();

		if($user['goldcoin'] < $giftinfo['price']) response_error('', '金币不足');

		// 启动事务
		Db::startTrans();
		try{
			
		    // 记录赠送
		    $data = array(
		    	'user_id' => $user_id,
		    	'to_user_id' => $to_user_id,
		    	'gift_id' => $gift_id,
		    	'image' => $giftinfo['image'],
		    	'goldcoin' => $giftinfo['price'],
		    	'glamour' => $giftinfo['glamour'],
		    	'add_time' => time(),
		    );
            $insert_id = M('gift_gived')->insertGetId($data);

            // 扣除金币
		    Db::name('users')->where('user_id', $user_id)->setDec('goldcoin', $giftinfo['price']);
		    // 给受赠者增加魅力值
		    Db::name('users')->where('user_id', $to_user_id)->setInc('glamour', $giftinfo['glamour']);
		   	// 记录金币变动日志
			goldcoin_log($user_id, "-{$giftinfo['price']}", 3, '赠送礼物', $insert_id);

			// 站内消息
			$MessageLogic = new MessageLogic();
			$MessageLogic->add($to_user_id, '您收到了一个礼物');
			// 融云消息
			$RongyunLogic = new RongyunLogic();
			$result = $RongyunLogic->PublishPrivateMessage('1', $to_user_id, '您收到了一个礼物');

		    // 提交事务
		    Db::commit();

		    response_success('', '赠送成功');
		} catch (\Exception $e) {
		    // 回滚事务
		    Db::rollback();

		    response_error('', '赠送失败');
		}
	}
}