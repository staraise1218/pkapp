<?php


namespace app\admin\controller;
use think\AjaxPage;
use think\Page;
use think\Db;
use app\api\logic\MessageLogic;
use app\api\logic\DynamicLogic;
use app\api\logic\RongyunLogic;

class UserAuthVideo extends Base {

    public function index(){
    	
        return $this->fetch();
    }

    public function ajaxindex(){
        // 搜索条件
        $condition = array();
        I('mobile') ? $condition['mobile'] = I('mobile') : false;
        I('email') ? $condition['email'] = I('email') : false;
               
        $model = M('UsersAuthVideo');
        $count = $model->where($condition)->count();
        $Page  = new AjaxPage($count,10);
        //  搜索条件下 分页赋值
        foreach($condition as $key=>$val) {
            $Page->parameter[$key]   =   urlencode($val);
        }
        
        $lists = $model->alias('uav')->where($condition)
        	->join('users u', 'uav.user_id=u.user_id', 'left')
        	->order('id desc')
        	->limit($Page->firstRow.','.$Page->listRows)
        	->field('u.user_id, u.nickname, u.uuid, uav.id, uav.auth_video_url, uav.status, uav.add_time')
        	->select();
                           
        $show = $Page->show();
        $this->assign('lists',$lists);
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('pager',$Page);
        return $this->fetch();
    }

    public function video(){
    	$id = I('id');

    	$video = M('UsersAuthVideo')->where('id', $id)->field('auth_video_url')->find();

    	$this->assign('auth_video_url', $video['auth_video_url']);
    	return $this->fetch();
    }

    public function changeStatus(){
    	$id = I('id');
    	$status = I('status');
    	$user_id = I('user_id');

    	$result = M('UsersAuthVideo')->where('id', $id)->setField('status', $status);
    	M('users')->where('user_id', $user_id)->setField('auth_video_status', $status);

        
        if($result && in_array($status, array(2, 3))){
            // 发送站内消息
            $messsage = $status == 2 ? '恭喜您，视频认证已通过' : '很抱歉，视频认证未通过';
            $MessageLogic = new MessageLogic();
            $MessageLogic->add($user_id, $messsage);

            // 融云消息
            $RongyunLogic = new RongyunLogic();
            $result = $RongyunLogic->PublishPrivateMessage('1', $user_id, $messsage);
        }

        // 发布动态
        if($result && $status == 2){
            $video = M('UsersAuthVideo')->where('id', $id)->find();
            $videodata = array(
                'video' => $video['auth_video_url'],
                'video_thumb' => $video['video_thumb'],
            );

            $DynamicLogic = new DynamicLogic();
            $DynamicLogic->add($user_id, 3, [], $videodata);
        }
    }
 }