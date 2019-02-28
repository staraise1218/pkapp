<?php


namespace app\admin\controller;
use think\AjaxPage;
use think\Page;
use think\Db;
use app\api\logic\MessageLogic;
use app\api\logic\RongyunLogic;

class IdentityAuth extends Base {

    public function index(){
    	
        return $this->fetch();
    }

    public function ajaxindex(){
        // 搜索条件
        $condition = array();
        I('mobile') ? $condition['mobile'] = I('mobile') : false;
        I('email') ? $condition['email'] = I('email') : false;
               
        $model = M('IdentityAuth');
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
        	->field('u.user_id, u.nickname, u.uuid, uav.id, uav.image, uav.status, uav.add_time')
        	->select();

        // if(is_array($lists) && !empty($lists)){
        // 	foreach ($lists as &$item) {
        // 		$item['image'] = unserialize($item['image']);
        // 	}
        // }
                           
        $show = $Page->show();
        $this->assign('lists',$lists);
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('pager',$Page);
        return $this->fetch();
    }

    public function image(){
    	$id = I('id');

    	$info = M('IdentityAuth')->where('id', $id)->field('image')->find();

    	$this->assign('image', unserialize($info['image']));
    	return $this->fetch();
    }

    public function changeStatus(){
    	$id = I('id');
    	$status = I('status');
    	$user_id = I('user_id');

    	$result = M('IdentityAuth')->where('id', $id)->setField('status', $status);
    	M('users')->where('user_id', $user_id)->setField('auth_identity_status', $status);

        
        if($result && in_array($status, array(2, 3))){
            // 发送站内消息
            $messsage = $status == 2 ? '恭喜您，身份认证已通过' : '很抱歉，身份认证未通过';
            $MessageLogic = new MessageLogic();
            $MessageLogic->add($user_id, $messsage);

            // 融云消息
            $RongyunLogic = new RongyunLogic();
            $result = $RongyunLogic->PublishPrivateMessage('1', $user_id, $messsage);
        }
    }
 }