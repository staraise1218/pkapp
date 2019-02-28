<?php

namespace app\api\controller;
use think\Db;
use app\api\logic\SmsLogic;
use app\api\logic\RongyunLogic;

class Auth extends Base {

	public function __construct(){
		// 设置所有方法的默认请求方式
		$this->method = 'POST';

		parent::__construct();
	}


    /**
     * 登录
     */
    public function login()
    {
        $mobile = trim(I('mobile'));
        $password = trim(I('password'));

        if (!$mobile || !$password) {
        	response_error('', '请填写账号或密码');
        }
        $user = Db::name('users')->where("account_mobile", $mobile)->find();
        if (!$user) {
            response_error('', '用户名或密码填写错误，请核对后再次提交');
        } elseif (encrypt($password) != $user['password']) {
            response_error('', '用户名或密码填写错误，请核对后再次提交');
        } elseif ($user['is_lock'] == 1) {
            response_error('', '账号异常已被锁定！');
        }
        
        // 更新活跃时间、在线状态
        $updateData = array(
            'active_time' => time(),
            'is_line' => '1',
        );
        M('users')->where('user_id', $user['user_id'])->update($updateData);

        
        $userInfo = M('users')->where('user_id', $user['user_id'])->find();
        unset($userInfo['password']);
       	response_success($userInfo);
    }

    // 检测是否注册
    public function isRegister(){
    	$mobile = I('mobile');

    	$where['account_mobile'] = $mobile;
    	$count = M('users')->where($where)->count();
    	if($count){
    		response_error('', '该手机号已注册');
    	}
    	response_success('', '未注册');
    }
    // 检测手机验证码
    public function checkMobileCode(){
        $mobile = I('mobile');
        $code = I('code');
        // 验证码检测
        $SmsLogic = new SmsLogic();
        if($SmsLogic->checkCode($mobile, $code, '1', $error) == false) response_error('', $error);
        response_success('', '验证码正确');
    }

    /**
     *  手机号注册
     */
    public function register() {
    	$mobile = I('mobile');
    	$code = I('code');
    	$password = trim(I('password'));
    	$nickname = I('nickname');
    	$sex = I('sex');
    	$birthday = I('birthday');
    	$local_country = I('local_country');
        $local_province = I('local_province');
        $city = I('city');
        $permanent_province = I('province');
        $permanent_city = I('city');
        $qq = I('qq');
        $longitude = I('longitude');
        $latitude = I('latitude');

    	if(check_mobile($mobile) == false){
    		response_error('', '手机号格式错误');
    	}

    	$userInfo = Db::name('users')->where("account_mobile={$mobile}")->find();
    	if($userInfo){
    		response_error('', '该手机号已注册');
    	}

    	// 验证码检测
    	$SmsLogic = new SmsLogic();
        if($SmsLogic->checkCode($mobile, $code, '1', $error) == false) response_error('', $error);


    	if(empty($password)){
    		response_error('', '密码不能为空');
    	}

    	$uuid = generateUuid();
    	$map = array(
    		'account_mobile' => $mobile,
    		'nickname' => $nickname,
    		'password' => encrypt($password),
    		'uuid' => $uuid,
    		'reg_time' => time(),
    		'last_login' => time(),
    		'token' => md5(time().mt_rand(1,999999999)),
   			'sex' => $sex,
			'birthday' => $birthday,
            'country' => $country,
            'province' => $local_province,
            'country' => $local_country,
            'permanent_province' => $permanent_province,
			'permanent_city' => $permanent_city,
            'qq' => $qq,
            'longitude' => $longitude,
            'latitude' => $latitude,
            'active_time' => time(),
            'is_line' => '1',
            'phoneOwechat' => $mobile,
    	);

    	$user_id = M('users')->insertGetId($map);
        if($user_id === false){
           response_error('', '注册失败');
        }

        // 注册融云获取token
        /*$RongyunLogic = new RongyunLogic();
        $RongyunLogic->getToken($user_id, $nickname);*/

        
        $userInfo = M('users')->where('user_id', $user_id)->find();
        unset($userInfo['password']);
        response_success($userInfo, '注册成功');
    }

    // 忘记密码
    public function resetPwd(){
        $mobile = I('mobile');
        $code = I('code');
        $password = I('password');

        if(check_mobile($mobile) == false){
            response_error('', '手机号码有误');
        }
        // 检测验证码
        $SmsLogic = new SmsLogic();
        if($SmsLogic->checkCode($mobile, $code, '1', $error) == false) response_error('', $error);

        $user = Db::name('users')->where("account_mobile = $mobile")->find();
        if(empty($user)){
            response_error('', '手机号不存在');
        }

        $password = encrypt($password);
        Db::name('users')->where("account_mobile=$mobile")->update(array('password'=>$password));

        response_success('', '密码修改成功');
    }

    /*
     * 第三方注册接口
     * account_id 微信 openid 微博 id
     * sex 1 男 2 女
     */
    public function third_register(){
        $type = I('type');
        $account_id = I('account_id');
        $nickname = I('nickname');
        $head_pic = I('head_pic');
        $sex = I('sex/d');
        $birthday = I('birthday');
        $local_country = I('local_country');
        $local_province = I('local_province');
        $permanent_province = I('province');
        $permanent_city = I('city');
        $city = I('city');
        $qq = I('qq');
        $longitude = I('longitude');
        $latitude = I('latitude');

        // 检测用户是否已注册
        $user_third = Db::name('user_third')->where("account_id='$account_id'")->field('user_id')->find();
        if($user_third){
            $user_id = $user_third['user_id'];
        } else {
            $third_data = array(
                'account_id' => $account_id,
                'nickname' => $nickname,
                'head_pic' => $head_pic,
                'sex' => $sex,
                'type' => $type,
            );
            // 将信息写入第三方登录表
            $id = Db::name('user_third')->insertGetId($third_data);

            // 将信息写入用户表
            $uuid = generateUuid();
            $userData = array(
                'uuid' => $uuid,
                'nickname' => $nickname,
                'reg_time' => time(),
                'last_login' => time(),
                'token' => md5(time().mt_rand(1,999999999)),
                'sex' => $sex,
                'head_pic' => $head_pic,
                'birthday' => $birthday,
                'country' => $country,
                'province' => $local_province,
                'city' => $local_city,
                'permanent_province' => $permanent_province,
                'permanent_city' => $permanent_city,
                'qq' => $qq,
                'longitude' => $longitude,
                'latitude' => $latitude,
                'active_time' => time(),
                'is_line' => '1',
            );

            $user_id = Db::name('users')->insertGetId($userData);
            // 更新三方登录表记录的user_id
            Db::name('user_third')->where('id', $id)->update(array('user_id'=>$user_id));
        }

        $userInfo = M('users')->where('user_id', $user_id)->find();
        unset($userInfo['password']);

        response_success($userInfo);
    }

    public function third_login(){
        $account_id = I('account_id');
        $type = I('type');

        $user_third = Db::name('user_third')->where("account_id='$account_id' and type='$type'")->field('user_id')->find();
        if (!$user_third) {
            response_error('', '账号不存在！');
        }

        $userInfo = Db::name('users')->where('user_id', $user_third['user_id'])->find();

        if ($userInfo['is_lock'] == 1) {
            response_error('', '账号异常已被锁定！');
        }
        
        // 更新活跃时间、在线状态
        $updateData = array(
            'active_time' => time(),
            'is_line' => '1',
        );
        M('users')->where('user_id', $userInfo['user_id'])->update($updateData);


        unset($userInfo['password']);
        response_success($userInfo);
    }

    /**
     * [thirdIsRegister 检测第三方登录是否已经授权]
     * @return [type] [description]
     */
    public function thirdIsRegister(){
        $account_id = I('account_id');
        $type = I('type');
        if(!in_array($type, array('weixin', 'qq', 'weibo'))) response_error('', '类型不正确');

        $is_register = Db::name('user_third')->where(array('account_id'=>$account_id, 'type'=>$type))->count();

        response_success(array('is_register'=>$is_register));
    }

    /**
     * [sendMobleCode 发送手机验证码]
     * @param [scene 1 注册 2 找回密码]
     * @return [type] [description]
     */
    public function sendMobileCode(){
        $mobile = I('mobile');
        $scene = I('scene', 1);

        $SmsLogic = new SmsLogic();
        $code = $SmsLogic->send($mobile, $scene, $error);
        if($code != false){
            response_success(array('code'=>$code), '发送成功');
        } else {
            response_error('', $error);
        }
    }
}