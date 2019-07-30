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
				->field('id, name, image, price')
				->select();

		$result['user'] = $user;
		$result['gift'] = $gift;

		response_success($result);
	}


	// 金币兑换礼物
	public function buyGift(){
		$user_id = I('user_id');
		$gift_id = I('gift_id');
		$num = I('num', 1);

		// 获取用户信息
		$user = Db::name('users')->where('user_id', $user_id)->field('goldcoin')->find();
		if(empty($user)) response_error('', '用户信息异常');

		// 获取礼物信息
		$gift = Db::name('gift')
			->where('id', $gift_id)
			->where('is_delete', 0)
			->find();
		if(empty($gift)) response_error('', '该礼物不存在');

		$total_goldcoin = $gift['price']*$num;
		if($user['goldcoin'] < $total_goldcoin) response_error('', '您的金币不足');

		// 启动事务
		Db::startTrans();
		try{
			// 扣除金币
			Db::name('users')->where('user_id', $user_id)->setDec('goldcoin', $total_goldcoin);
			$count = Db::name('gift_my')->where('user_id', $user_id)->where('gift_id', $gift_id)->count();
			if($count){
				Db::name('gift_my')->where('user_id', $user_id)->where('gift_id', $gift_id)->setInc('num', $num);
			} else {
				// 记录我的礼物
				$data = array(
					'user_id' => $user_id,
					'gift_id' => $gift_id,
					'num' => $num,
				);

				Db::name('gift_my')->insert($data);
			}

			// 提交事务
		    Db::commit();
			response_success('', '兑换成功');
		} catch (\Exception $e) {
		    // 回滚事务
		    Db::rollback();

		    response_error('', '兑换失败');
		}
		
	}

	// 赠送礼物
	public function give(){
		$user_id = I('user_id');
		$to_user_id = I('to_user_id');
		$gift_id = I('gift_id');
		$num = I('num');

		// 我拥有的礼物
		$myGift = Db::name('gift_my')->where('user_id', $user_id)->where('gift_id', $gift_id)->find();
		if(empty($myGift)) response_error('', '您未拥有此礼物');
		if($num > $myGift['num']) response_error('', '您的礼物余量不足');

		// 启动事务
		Db::startTrans();
		try{
			
		    // 记录赠送
		    $data = array(
		    	'user_id' => $user_id,
		    	'to_user_id' => $to_user_id,
		    	'gift_id' => $gift_id,
		    	'add_time' => time(),
		    );
            $insert_id = M('gift_gived')->insertGetId($data);

            // 减去自己的礼物
		    Db::name('gift_my')->where('user_id', $user_id)->where('gift_id', $gift_id)->setDec('num', $num);

		    // 提交事务
		    Db::commit();

		    response_success('', '赠送成功');
		} catch (\Exception $e) {
		    // 回滚事务
		    Db::rollback();

		    response_error('', '赠送失败');
		}
	}

	// 我的礼物
	public function myGift(){
		$user_id = I('user_id');
		$page = I('page', 1);

		$list = Db::name('gift_my')->alias('gm')
			->join('gift g', 'gm.gift_id=g.id', 'left')
			->where('user_id', $user_id)
			->where('num', '>', 0)
			->field('gm.gift_id, gm.num, g.name, g.image, g.price')
			->limit(16)
			->page($page)
			->select();

		response_success($list);
	}

	// 我送出的礼物
	public function gived(){
		$user_id = I('user_id');
		$page = I('page', 1);

		$list = Db::name('gift_gived')->alias('gg')
			->join('gift g', 'gg.gift_id=g.id', 'left')
			->join('users u', 'gg.to_user_id=u.user_id')
			->field('gg.gift_id, gg.num, g.name, g.image, g.price, u.head_pic, u.nickname')
			->where('gg.user_id', $user_id)
			->limit(15)
			->page($page)
			->select();

		response_success($list);
	}

	// 我收到的礼物
	public function giveMe(){
		$user_id = I('user_id');
		$page = I('page', 1);

		$list = Db::name('gift_gived')->alias('gg')
			->join('gift g', 'gg.gift_id=g.id', 'left')
			->join('users u', 'gg.user_id=u.user_id')
			->field('gg.gift_id, gg.num, g.name, g.image, g.price, u.head_pic, u.nickname')
			->where('to_user_id', $user_id)
			->limit(15)
			->page($page)
			->select();

		response_success($list);
	}
}