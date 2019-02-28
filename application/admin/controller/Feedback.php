<?php

namespace app\admin\controller;
use think\Db;
use think\Page;
use think\AjaxPage;

class Feedback extends Base{
	public function index(){
    	
        return $this->fetch();
    }

    public function ajaxindex(){
        // 搜索条件
        $condition = array();

        $model = M('Feedback');
        $count = $model->where($condition)->count();
        $Page  = new AjaxPage($count,10);
        //  搜索条件下 分页赋值
        foreach($condition as $key=>$val) {
            $Page->parameter[$key]   =   urlencode($val);
        }
        
        $lists = $model->alias('f')->where($condition)
        	->join('users u', 'f.user_id=u.user_id', 'left')
        	->order('id desc')
        	->limit($Page->firstRow.','.$Page->listRows)
        	->field('u.user_id, u.nickname, u.uuid, f.id, f.content, f.add_time')
        	->select();

                           
        $show = $Page->show();
        $this->assign('lists',$lists);
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('pager',$Page);
        return $this->fetch();
    }

    public function detail(){
    	$id = I('id');

    	$info = M('Feedback')->where('id', $id)->field('content, image')->find();

    	$this->assign('info', $info);
    	return $this->fetch();
    }
}