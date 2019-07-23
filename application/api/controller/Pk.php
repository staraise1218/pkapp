<?php

namespace app\api\controller;

use think\Db;
use GatewayClient\Gateway;

require_once './GatewayClient/Gateway.php';

class Pk extends Base {

	public function __construct(){
		// 设置所有方法的默认请求方式
		$this->method = 'POST';

		parent::__construct();
	}

	public function index(){
		$user_id = I('user_id');

		$result['total_num'] = 0;
		$result['win_num'] = 0;
		$result['fail_num'] = 0;

		response_success($result);
	}

	public function pklist(){
		$user_id = I('user_id');
		$page = I('page', 1);
		$searchNickname = I('searchNickname');

		$where = '1=1';
		if($searchNickname) $where .= " and nickname like '%{$searchNickname}%'";
		
		$offset = 15;
		$limit_start = ($page-1)*$offset;
		$sql = "SELECT t.*, @ranking := @ranking + 1 AS ranking FROM (SELECT @ranking := $limit_start) r, (SELECT user_id, nickname, head_pic, province, city, school FROM tp_users where $where ORDER BY goldcoin DESC limit $limit_start,$offset) AS t";
		$list = Db::query($sql);

		if(is_array($list) && !empty($list)){
			foreach ($list as &$item) {
				$item['province_city'] = '北京市朝阳区';
				unset($item['province']);
				unset($item['city']);
			}
		}

		// 获取自己的排名
		$sql = "select * from (SELECT user_id, @ranking := @ranking + 1 AS ranking FROM (SELECT @ranking := 0) r, (SELECT user_id FROM tp_users ORDER BY goldcoin DESC) AS t) as b where user_id = $user_id";
		$own = Db::query($sql);

		$total_num = Db::name('users')->count();
		
		if($own) $my_ranking = $own[0]['ranking'];
		$result = array(
			'list' => $list, 
			'my_ranking'=>$my_ranking,
			'total_num'=>$total_num
		);
		response_success($result);
	}

	public function invite(){
		$user_id = I('user_id');
		$to_user_id = I('to_user_id');

		if($user_id == $to_user_id) response_error('', '不能邀请自己');
		if(!Gateway::isUidOnline($user_id)) response_error('', '您不在线');
		if(!Gateway::isUidOnline($to_user_id)) response_error('', '对方不在线');

		// 获取用户信息
		$user = Db::name('users')->where('user_id', $user_id)->find();
		$user['head_pic'] = headPic($user['head_pic']);

		$room_id  = Db::name('room')->insertGetId(array(
			'user_id' => $user_id,
			'to_user_id' => $to_user_id,
			'createtime' => time(),
		));

		if($room_id) {
			$message = json_encode(array(
				'action'=>'invite',
				'room_id' => $room_id,
				'user_id' => $user_id,
				'nickname' => $user['nickname'],
				'head_pic' => $user['head_pic'],
			));
			Gateway::sendToUid($to_user_id, $message);

			// 获取问题列表
			/*$knowledgeList = Db::name('knowledge')
				->where('is_open', 1)
				->where('is_delete', 0)
				->order('id desc')
				->limit(5)
				->field('title, a, b, c, d, answer')
				->select();*/
			$sql = "SELECT id room_knowledge_id, title, a, b, c, d, answer FROM `tp_knowledge`WHERE id >= (SELECT floor(RAND() * (SELECT MAX(id) FROM `tp_knowledge`))) ORDER BY id LIMIT 5";
       		$knowledgeList = Db::query($sql);
			foreach ($knowledgeList as $k => $item) {
				$item['room_id'] = $room_id;
				$knowledgeList[$k]['room_knowledge_id'] = Db::name('room_knowledge')->insertGetId($item);
			}

			

			$result['knowledgeList'] = $knowledgeList;
			response_success($result);
			
		} else {
			response_error();
		}
	}

	// 进入房间
	public function intoRoom(){
		$room_id = I('room_id');
		$to_user_id = I('to_user_id');

		$room = Db::name('room')->where('id', $room_id)->find();
		if($room['user_id_status'] == 2) response_error(array('status'=>1), '对方已退出');
		// if($room['createtime']+20 > time()) response_error(array('status'=>2), '等待超时');

		$userinfo = Db::name('users')
			->where('user_id', $room['user_id'])
			->field('user_id, head_pic, nickname')
			->find();
		$userinfo['head_pic'] = headPic($userinfo['head_pic']);
		
		$touserinfo = Db::name('users')
			->where('user_id', $to_user_id)
			->field('user_id, head_pic, nickname')
			->find();
		$touserinfo['head_pic'] = headPic($touserinfo['head_pic']);

		$result['userinfo'] = $userinfo;
		$result['touserinfo'] = $touserinfo;

		// 获取题目
		$knowledgeList = Db::name('room_knowledge')
			->where('room_id', $room_id)
			->field('id room_knowledge_id, title, a, b, c, d, answer')
			->select();
		$result['knowledgeList'] = $knowledgeList;

		$message = json_encode(array(
			'action' => 'intoRoom',
		));
		Gateway::sendToUid($room['user_id'], "$message");

		response_success($result);
	}

	// 开始答题
	public function start(){
		$to_user_id = I('to_user_id');

		$message = json_encode(array(
			'action' => 'start',
		));
		Gateway::sendToUid($to_user_id, "$message");
	}

	// 选择答案
	public function choose(){
		$room_knowledge_id = I('room_knowledge_id');
		$user_id = I('user_id');
		$to_user_id = I('to_user_id');
		$answer = I('answer');
		$is_right = I('is_right');

		$message = json_encode(array(
			'action' => 'choose',
			'answer' => $answer,
			'is_right' => $is_right,
		));
		Gateway::sendToUid($to_user_id, "$message");

		// $updatedata = array(
		// 	''
		// );
		// Db::name('room_knowledge')->where('id', $room_knowledge_id)->update($updatedata);
	}

	// pk结果 
	public function sendResult(){
		$room_id = I('room_id');
		$user_id = I('user_id');
		$score = I('score');
		$res = I('res');

		$data = array(
			'room_id'=>$room_id,
			'user_id' => $user_id,
			'score'=>$score,
			'res' => $res,
		);
		$insert_id = Db::name('room_result')->insert($data);
		if($insert_id){
			if($res == '1'){
				Db::name('users')->where('user_id', $user_id)->setInc('goldcoin', $score); 
			}

			response_success();
		} else {
			response_error();
		}

		
	}

}