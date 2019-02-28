<?php

namespace app\admin\controller;
use think\Db;
use think\Page;

class Dynamics extends Base{
	public function index(){
		$p = I('p', 1);

		$where = " 1 = 1 ";
        $keywords = trim(I('keywords'));
        $keywords && $where.=" and title like '%$keywords%' ";
       
       	$pagesize = 20;
        $lists = M('dynamics')->alias('d')
        	->join('users u', 'd.user_id=u.user_id', 'left')
        	->where($where)
        	->order('id desc')
        	->page("$p, $pagesize")
        	->field('nickname, uuid, description, content, d.id, d.status, d.add_time')
        	->select();
        $count = M('dynamics')->where($where)->count();// 查询满足要求的总记录数
        $Page = new Page($count, $pagesize);// 实例化分页类 传入总记录数和每页显示的记录数

        $this->assign('lists', $lists);
        $this->assign('Page', $Page);
		return $this->fetch();
	}

	public function detail(){
		$id = I('id');

		$info = M('dynamics')->alias('d')
			->join('users u', 'd.user_id=u.user_id', 'left')
			->where('id', $id)
			->field('u.uuid, d.*')
			->find();

		$this->assign('info', $info);
		return $this->fetch();
	}

	public function changeStatus(){
		$id = I('id');
		$status = I('status');

		M('dynamics')->where('id', $id)->setField('status', $status);

		response_success();
	}
}