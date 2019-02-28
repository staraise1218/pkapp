<?php

namespace app\api\controller;
use think\Db;
use think\Config;
use app\api\logic\AlipayLogic;
use app\api\logic\WxpayLogic;

class Vip extends Base {

	public function __construct(){
		// 设置所有方法的默认请求方式
		$this->method = 'POST';

		parent::__construct();
	}

	// 修改vip级别 0 普通会员 1 白金 2 黄金 3 vip
	// public function change(){
	// 	$user_id = I('user_id');
	// 	$level = I('level');

	// 	if(M('users')->where('user_id', $user_id)->setField('level', $level) !== false){
	// 		response_success('', '修改成功');
	// 	} else {
	// 		response_error('', '修改失败');
	// 	}
	// }


	// 下单
	public function placeOrder(){

		$user_id = I('user_id');
		$level = I('level');
		switch ($level) {
			case '1':
				$amount = '0.01';
				break;
			
			case '2':
				$amount = '0.01';
				break;
			
			case '3':
				$amount = '0.01';
				break;
			
			case '4':
				$amount = '0.01';
				break;
		}

		$order_no = $this->generateOrderno();
		$data = array(
			'order_no' => $order_no,
			'user_id' => $user_id,
			'level' => $level,
			'createtime' => time(),
			'amount' => $amount,
		);

		if(Db::name('vip_order')->insert($data)){
			response_success(array('order_no'=>$order_no));
		} else {
			response_error('', '下单失败');
		}
	}

	// 选择支付方式去支付
	public function topay(){
		$order_no = I('order_no');
		$paymentMethod = I('paymentMethod');

		/********* 判断订单信息 **************/
		$order = Db::name('vip_order')->where('order_no', $order_no)->find();
		if(empty($order)) response_error('', '该订单不存在');
		if($order['paystatus'] == 1) response_error('', '该订单已支付');

		switch ($order['level']) {
			case '1':
				$total_amount = '0.01';
				break;
			
			case '2':
				$total_amount = '0.01';
				break;
			
			case '3':
				$total_amount = '0.01';
				break;
			
			case '4':
				$total_amount = '0.01';
				break;
		}

		/************** 获取订单签名字符串 **************/
		if($paymentMethod == 'alipay'){
			$notify_url = 'http://app.yujianhaoshiguang.cn/index.php/api/vip/alipayCallback';
			$AlipayLogic = new AlipayLogic($notify_url);
			$orderStr = $AlipayLogic->generateOrderStr($order_no, $total_amount, '购买VIP', '购买VIP');
			return $orderStr;
		}

		if($paymentMethod == 'wxpay'){
			$WxpayLogic = new WxpayLogic();
			$WxpayLogic->notify_url = 'http://app.yujianhaoshiguang.cn/index.php/api/vip/wxpayCallback';
			$param = $WxpayLogic->getPrepayId($order_no, $total_amount, '购买VIP');
			response_success($param);
		}
	}

	// 购买vip后的支付回调接口
	public function alipayCallback(){
		$order_no = input('post.out_trade_no');
		$trade_status = input('post.trade_status');

		//验签
		// $AlipayLogic = new AlipayLogic();
		/*$param = $_POST;
		$param['fund_bill_list'] = html_entity_decode($param['fund_bill_list']);
		$_POST = $param;
		if( ! $AlipayLogic->checkSign()) die('error');*/
		
		
		
		$order = Db::name('vip_order')->where('order_no', $order_no)->find();
		if(empty($order)) goto finish;
		if($order['paystatus'] == 1) goto finish;
		// 回调后的业务流程
		if($trade_status == 'TRADE_SUCCESS'){
			$this->changeVip($order_no, $order['user_id'], $order['level']);
		}

		finish:
		echo 'success';
	}

	// 购买vip后的支付回调接口
	public function wxpayCallback(){
		$WxpayLogic = new WxpayLogic();
		$result = $WxpayLogic->callback();

		if($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS' ){
            
            $order_no  = $result['out_trade_no'];

            $order = Db::name('vip_order')->where('order_no', $order_no)->find();
            if(empty($order)) goto finish;
            if($order['paystatus'] == 1) goto finish;
            
            // 回调后的业务流程
            $this->changeVip($order_no, $order['user_id'], $order['level']);
            
        }

         
		finish:
		echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        exit();     
	}

	public function IOSCallback(){
		$user_id = I('user_id');
		$level = I('level');

		// 计算到期日期
		$user = Db::name('users')->where('user_id', $user_id)->field('vip_expire_date')->find();
		$old_date = $user['vip_expire_date'] ? $user['vip_expire_date'] : date('Y-m-d');
		$enum = Config::load(APP_PATH.'enum.php', ture);
		$vip_config = $enum['vip'];
		$num = $vip_config[$level]['num'];
		$unit = $vip_config[$level]['unit'];
		$expire_date = date('Y-m-d', strtotime('+'.$num.$unit, strtotime($old_date)));

		Db::name('users')->where('user_id', $user_id)->update(array('level'=>$level, 'vip_expire_date'=>$expire_date));

		switch ($level) {
			case '1':
				$amount = '0.01';
				break;
			
			case '2':
				$amount = '0.01';
				break;
			
			case '3':
				$amount = '0.01';
				break;
			
			case '4':
				$amount = '0.01';
				break;
		}
		// ios没走下单接口，这里支付成功记录一下
		$order_no = $this->generateOrderno();
		$data = array(
			'order_no' => $order_no,
			'user_id' => $user_id,
			'level' => $level,
			'createtime' => time(),
			'amount' => $amount,
		);
		Db::name('vip_order')->insert($data);

		response_success();
	}

	private function changeVip($order_no, $user_id, $level){

		Db::name('vip_order')->where('order_no', $order_no)->update(array('paystatus'=>'1', 'paytime'=>time()));
		// 计算到期日期
		$user = Db::name('users')->where('user_id', $user_id)->field('vip_expire_date')->find();
		$old_date = $user['vip_expire_date'] ? $user['vip_expire_date'] : date('Y-m-d');
		$enum = Config::load(APP_PATH.'enum.php', ture);
		$vip_config = $enum['vip'];
		$num = $vip_config[$level]['num'];
		$unit = $vip_config[$level]['unit'];
		$expire_date = date('Y-m-d', strtotime('+'.$num.$unit, strtotime($old_date)));

		Db::name('users')->where('user_id', $user_id)->update(array('level'=>$level, 'vip_expire_date'=>$expire_date));
	}

	private function generateOrderno(){
		$order_no = date('YmdHis').mt_rand(1000, 9999);

		$count = Db::name('vip_order')->where('order_no', $order_no)->count();

		if($count) $this->generateOrderno();
		return $order_no;
	}
}