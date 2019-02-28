<?php
/**
 * app 支付类
 */
namespace app\api\logic;
require_once PLUGIN_PATH . 'alipay/AopSdk.php';

use think\Db;


class AlipayLogic {
	
	private $gatewayUrl = 'https://openapi.alipay.com/gateway.do';
	private $appId = '2018081461022343';
	private $rsaPrivateKey = 'MIIEpAIBAAKCAQEAntYOKNypKZH8deQ8mSpNFSarTmJjT/XhVFEV3mSVsaQUmGYS02QiOcJr49awF49WrjPKnrSUr+nItb33fNCeiymfF8+sLuE6tCHmibLlIuX/srXELVWoAFp9gK25k6dlSVkpdyULah9od/Ya6PzPghswmCAQmrwuvUmdZ0YpSVPpTbTtFWaoSgb4WRF+PAq6jz0bI2kPCgauJ81OlW0SFs9Ec0dq1E1RDyf8Bkn2x9ZHFC8L3CRqu3lH6AHq1PG9n4MhHvE0H+U6cT9AZHN3Fu/LPRtZ4JtJAfF2mgWUUz83nrZNSn55LBEAeTQXDf2I+A5auumoWwEVoHGBdUtRFQIDAQABAoIBAQCVotlSG5fuRs8NjYidTyGxVG28eapQETdHxOASVLZF1Wtlq3v+1G399jDIQ2A/wdUKZlZzr3IIS/m/Zgj6+Fr0hbCQsR/oRl2Uh/91KCj/+Kgsh1sazoBoXNexW3jiJqigMjSDP04CmzZDFYKCjnE7hDwChOq/q5tozipQueN5ZOyg14bD7CyUefSnRBwAs0Yz3dd6TcmuEPgQjQSjcsuQc6Qv5iaaLqHjzOmW/Ty6D5JGoaoF5RkVd0rU+HYfgkxH3v3Y6slW0kyvDWVICnA1Z4BKvMP/fwd8lBUl8BROFSY77t1nJtSGO6qU/yjltEUOaF0iemrQRdDCXnIVM1QhAoGBANNU2ElStMTECAn2JnT+Eg95sVCUucdXNTPJqlBFoptv1A3x9dkxaV12IG7XdhAnvPpiIqC2+by9Yw+VXnsPW/Rw60BP36JbYK2j4Ph+nDMN0aWqOoYfz9lfOKHJ2YKTfk8aabD6MYdmxL3p1zTQ7+LuqdTPQsjkCrbOrEUEbGxdAoGBAMBosDm/tKK+A9X2pTbsWWhCzaaGYwD6mfUxZ841OTAJ8hNqna5xmxy9gGuTvx3Lp7AoSlIk/fg47AeV4fz0jJHD6i4nwrm2CkrtejFV8E8HO3zVL7zNTMo4/vSfuJemlENfEIluXWPj+MlQUY98c0sUWHi5K/6YyBWisSDpG+wZAoGANhR5QNJEZjIQKJRwJPI7pvAqWwekPwnnGHib9+zJ/uLwLh1kH4+QehVXmWXT1bCFoMFqQRxx7kW8yukbg3xbuMMIwK1v+jlOFUFhudWUnVAE/VMBpP8RDnxanrRr0Al0gkOtFlgAQrke0ca8hsyNBtKybT4YxhXtU/ixhvvpzxUCgYEAmz/dcbtNdUL8tVgeVwK94XKFnSgyGkgakc0bhTdMvYZI3YHZWTgxgC8nv6WnP9Njqq/XyBAxHGhRio1Vm1V3VuZNvpA2fsJz66FSRxygmiOrzD34Fs9QdpsmnRuDRloSp4m9PibfFAqOY2F7bdts69euyzoeVX/RciOj6HovHYECgYAbFe8OxNDaPww8Xag6vpkd7t5n6sDgega6C0fnotorV3iE7N6YYq+caQn/w5+E8P9Eji3qRQwoX7bpktSxpXttL0jieopFPabtOqvFov+cN5Y8/GS1e7VOVxyCho5cmm8V0FmkgpClmnrcrmHFyZb1fRi0dgEYZG3ytyF15enK+g==';
	private $format = 'json';
	private $charset = 'UTF-8';
	private $signType = 'RSA2';
	private $alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEArOIQAl26+RNfue1KYYWGGE1TWAC6BcnxMkygw2GbWGO3SXSz8rqO7Pj+YW8mGqH5WfYektk0ZT/HjJXjh0jbQQrFoXpfVkp9yqY8qXFBUcCBhghi587OKFD/ZyD9v6lewTVWgJjqXnsmZjPj4DhalzaRKHwtQ0R9zo1SLMpRGbEtCUhJPWTLVYOGVGSL+EkGQi5D/auOvoAEmNh9qf+aOz6z00SoqP8KpsNzIWjjqkapNzj+f5CU4iIBpnGf+uMu/ovWo1VnLHOTUvHR1KxA9+x9DO20hnwJFF+PzZuN+9lm0ORh0qai8dXc0wv7sa3xFoaYMCLlcGfCnX0FDjq+JwIDAQAB';
	private $notify_url;

	public function __construct($notify_url = true){
		$this->notify_url = $notify_url;
	}

	// 生成客户端需要的orderStr
	public function generateOrderStr($out_trade_no, $total_amount, $body, $subject){

		$aop = new \AopClient;
		$aop->gatewayUrl = $this->gatewayUrl;
		$aop->appId = $this->appId;
		$aop->rsaPrivateKey = $this->rsaPrivateKey;
		$aop->format = $this->format;
		$aop->charset = $this->charset;
		$aop->signType = $this->signType;
		// $aop->alipayrsaPublicKey = $this->alipayrsaPublicKey;
		//实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
		$request = new \AlipayTradeAppPayRequest();
		//SDK已经封装掉了公共参数，这里只需要传入业务参数
		$bizcontent = array(
			'body' => $body,
			'subject' => $subject,
			'out_trade_no' => $out_trade_no,
			'total_amount' => $total_amount,
			'product_code' => 'QUICK_MSECURITY_PAY'
		);

		$request->setNotifyUrl($this->notify_url);
		$request->setBizContent(json_encode($bizcontent));
		//这里和普通的接口调用不同，使用的是sdkExecute
		$orderStr = $aop->sdkExecute($request);
		return $orderStr;
	}

	public function checkSign(){
		$aop = new \AopClient;
		$aop->alipayrsaPublicKey = $this->alipayrsaPublicKey;
		$signType = $this->signType;
file_put_contents('runtime/log/request.log',var_export($_POST, true), FILE_APPEND);
		return $aop->rsaCheckV1($_POST, NULL, $signType);

	}
}