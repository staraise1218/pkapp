<?php

namespace app\api\controller;
use think\Db;
use think\Config;
use app\api\logic\AlipayLogic;
use app\api\logic\WxpayLogic;

class Goldcoin extends Base {

	public function __construct(){
		// 设置所有方法的默认请求方式
		$this->method = 'POST';

		parent::__construct();
	}


	// 下单
	public function placeOrder(){

		$user_id = I('user_id');
		$goldcoin_id = I('goldcoin_id');

		$goldcoin = M('goldcoin')->where(array('id'=>$goldcoin_id, 'is_delete'=>0))->find();
		if(empty($goldcoin)) response_error('', '该金币商品不存在');

		$order_no = 'g'.$this->generateOrderno();
		$data = array(
			'order_no' => $order_no,
			'user_id' => $user_id,
			'goldcoin_id' => $goldcoin_id,
			'num' => $goldcoin['num'],
			'give_num' => $goldcoin['give_num'],
			'price' => $goldcoin['price'],
			'createtime' => time(),
		);

		if(Db::name('goldcoin_order')->insert($data)){
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
		$order = Db::name('goldcoin_order')->where('order_no', $order_no)->find();
		if(empty($order)) response_error('', '该订单不存在');
		if($order['paystatus'] == 1) response_error('', '该订单已支付');

		$total_amount = $order['price'];

		/************** 获取订单签名字符串 **************/
		if($paymentMethod == 'alipay'){
			$notify_url = 'http://app.yujianhaoshiguang.cn/index.php/api/goldcoin/alipayCallback';
			$AlipayLogic = new AlipayLogic($notify_url);
			$orderStr = $AlipayLogic->generateOrderStr($order_no, $total_amount, '购买金币', '购买金币');
			return $orderStr;
		}

		if($paymentMethod == 'wxpay'){
			$WxpayLogic = new WxpayLogic();
			$WxpayLogic->notify_url = 'http://app.yujianhaoshiguang.cn/index.php/api/goldcoin/wxpayCallback';
			$param = $WxpayLogic->getPrepayId($order_no, $total_amount, '购买金币');
			response_success($param);
		}
	}

	// 购买金币后的支付宝回调接口
	public function alipayCallback(){
		$order_no = input('post.out_trade_no');
		$trade_status = input('post.trade_status');

		$order = Db::name('goldcoin_order')->where('order_no', $order_no)->find();
		if(empty($order)) goto finish;
		if($order['paystatus'] == 1) goto finish;

		// 回调后的业务流程
		if($trade_status == 'TRADE_SUCCESS'){
			$this->operation($order_no);
		}

		finish:
		echo 'success';
	}
	// 购买金币后的微信回调接口
	public function wxpayCallback(){
		$WxpayLogic = new WxpayLogic();
		$result = $WxpayLogic->callback();

		if($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS' ){
            
            $order_no  = $result['out_trade_no'];

            $order = Db::name('goldcoin_order')->where('order_no', $order_no)->find();
			if(empty($order)) goto finish;
			if($order['paystatus'] == 1) goto finish;

			// 回调后的业务流程
			$this->operation($order_no);
            
        }

         
		finish:
		echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        exit();     
	}

	public function IOSCallback(){
		$user_id = I('user_id');
		$goldcoin_id = I('goldcoin_id');


		$goldcoin = M('goldcoin')->where(array('id'=>$goldcoin_id, 'is_delete'=>0))->find();
		// ios没走下单接口，这里支付成功记录一下
		$order_no = $this->generateOrderno();
		$data = array(
			'order_no' => $order_no,
			'user_id' => $user_id,
			'goldcoin_id' => $goldcoin_id,
			'num' => $goldcoin['num'],
			'give_num' => $goldcoin['give_num'],
			'price' => $goldcoin['price'],
			'createtime' => time(),
		);

		if(Db::name('goldcoin_order')->insert($data)){
			$this->operation($order_no);
		}

		response_success('', '操作成功');
	}

	public function operation($order_no){
		// 启动事务
		Db::startTrans();
		try{
			
			// 更改订单状态
			M('goldcoin_order')->where('order_no', $order_no)->update(array('paystatus'=>1, 'paytime'=>time()));
		    // 记录赠送
		    $goldcoin_order = M('goldcoin_order')->where('order_no', $order_no)->find();

            // 加金币
            $total_goldcoin = $goldcoin_order['num'] + $goldcoin_order['give_num'];
		    Db::name('users')->where('user_id', $goldcoin_order['user_id'])->setInc('goldcoin', $total_goldcoin);
		   
		   	// 记录金币变动日志
			goldcoin_log($user_id, "+{$total_goldcoin}", 2, '购买金币', $goldcoin_order['id']);

		    // 提交事务
		    Db::commit();

		} catch (\Exception $e) {
		    // 回滚事务
		    Db::rollback();
		}
	}

	private function generateOrderno(){
		$order_no = date('YmdHis').mt_rand(1000, 9999);

		$count = Db::name('goldcoin_order')->where('order_no', $order_no)->count();

		if($count) $this->generateOrderno();
		return $order_no;
	}
}