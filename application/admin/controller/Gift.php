<?php

namespace app\admin\controller;

use think\Page;
use think\Db;

class Gift extends Base {

    public function index(){

        $p = $this->request->param('p');
        $list = M('Gift')
            ->where('is_delete', 0)
            ->order('sort desc, id desc')
            ->page($p.',10')
            ->select();

        
        $count = M('Gift')->where('is_delete', 0)->count();
        $Page = new Page($count,10);
        $show = $Page->show();

        $this->assign('list',$list);
        $this->assign('page',$show);
        return $this->fetch();
    }

    public function add(){
        if(IS_POST) {
            $data = I('post.');
            if( ! false == M('Gift')->save($data)){
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
            if( ! false == M('Gift')->where('id', $id)->save($data)){
                $this->ajaxReturn(array('status'=>1, 'msg'=>'操作成功'));
            } else {
                $this->ajaxReturn(array('status'=>0, 'msg'=>'操作失败'));
            }
        }

        $info = M('Gift')->where('id', $id)->find();

        $this->assign('info', $info);
        return $this->fetch();
    }

    public function del(){
        $id = I('id');

        if(! FALSE ==  M('Gift')->where('id', $id)->update(array('is_delete'=>1))){
            $this->ajaxReturn(array('status'=>1, 'msg'=>'操作成功'));
        } else {
            $this->ajaxReturn(array('status'=>0, 'msg'=>'操作失败'));
        }
    }
}