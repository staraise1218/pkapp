<?php

namespace app\admin\controller;

use think\Page;
use think\Db;

class Goldcoin extends Base {

    public function index(){

        $p = $this->request->param('p');
        $list = M('Goldcoin')
            ->where('is_delete', 0)
            ->order('sort desc, id asc')
            ->page($p.',10')
            ->select();

        
        $count = M('Goldcoin')->where('is_delete', 0)->count();
        $Page = new Page($count,10);
        $show = $Page->show();

        $this->assign('list',$list);
        $this->assign('page',$show);
        return $this->fetch();
    }

    public function add(){
        if(IS_POST) {
            $data = I('post.');
            if( ! false == M('Goldcoin')->save($data)){
                $this->ajaxReturn(array('status'=>1, 'msg'=>'操作成功'));
            } else {
                $this->ajaxReturn(array('status'=>0, 'msg'=>'操作失败'));
            }
        }
        return $this->fetch();
    }

    public function edit(){
        $id = I('id');

        if(IS_POST) {
            $data = I('post.');
            if( ! false == M('Goldcoin')->where('id', $id)->save($data)){
                $this->ajaxReturn(array('status'=>1, 'msg'=>'操作成功'));
            } else {
                $this->ajaxReturn(array('status'=>0, 'msg'=>'操作失败'));
            }
        }

        $info = M('Goldcoin')->where('id', $id)->find();

        $this->assign('info', $info);
        return $this->fetch();
    }

    public function del(){
        $id = I('id');

        if(! FALSE ==  M('Goldcoin')->where('id', $id)->update(array('is_delete'=>1))){
            $this->ajaxReturn(array('status'=>1, 'msg'=>'操作成功'));
        } else {
            $this->ajaxReturn(array('status'=>0, 'msg'=>'操作失败'));
        }
    }

    // 购买记录
    public function order(){
        $p = I('p', 1);
        $searchtype = I('searchtype');
        $keyword = I('keyword', '');
        $start_time = I('start_time');
        $end_time = I('end_time');

        $where = array(
            'paystatus'=>1,
        );

        if($keyword != ''){
            if($searchtype == 'nickname') $where['u.nickname'] = array('like', "%$keyword%");
            if($searchtype == 'uuid') $where['u.uuid'] = $keyword;
        }
        if($start_time && $end_time){
            $start_time = strtotime($start_time);
            $end_time = strtotime($end_time);

            $where['go.paytime'] = array('BETWEEN', array($start_time, $end_time));

            $start_time = date('Y-m-d H:i:s', $start_time);
            $end_time = date('Y-m-d H:i:s', $end_time);
        }
        $list = Db::name('goldcoin_order')->alias('go')
            ->join('users u', 'go.user_id=u.user_id', 'left')
            ->where($where)
            ->order('id desc')
            ->page($p.',10')
            ->field('u.nickname, u.uuid, go.num, go.give_num, go.price, go.paytime')
            ->select();
        
        $count = Db::name('goldcoin_order')->alias('go')
            ->join('users u', 'go.user_id=u.user_id', 'left')
            ->where($where)
            ->count();

        $Page = new Page($count,10);
        $show = $Page->show();

        // 计算总金额
        $total_money = Db::name('goldcoin_order')->alias('go')
            ->join('users u', 'go.user_id=u.user_id', 'left')
            ->where($where)
            ->sum('price');

        $this->assign('list', $list);
        $this->assign('show', $show);
        $this->assign('total_money', $total_money);
        $this->assign('searchtype', $searchtype);
        $this->assign('keyword', $keyword);
        $this->assign('start_time', $start_time);
        $this->assign('end_time', $end_time);
        return $this->fetch();
    }
}