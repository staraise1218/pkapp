<?php

namespace app\admin\controller;

use think\Page;
use think\Paginator;

class Knowledge extends Base {

    public function index(){
        $page = I('page', 1);
        
        $where = "is_delete = 0";
        $keywords = trim(I('keywords'));
        $keywords && $where.=" and title like '%$keywords%' ";
       
        $list = M('knowledge')
            ->where($where)
            ->order('id desc')
            ->paginate(20, false, ['page'=>$page, 'path'=>U('admin/knowledge/index')]);


        $this->assign('list',$list);// 赋值数据集

        // 获取年级分类
        $gradeList = M('grade')->where('is_open', 1)->where('is_delete', 0)->order('id desc')->select();
        $this->assign('gradeList', $gradeList);        
        return $this->fetch();
    }

    public function add(){
        if($this->request->isPost()){
            $data = I('post.');
            if(trim($data['title']) == '') $this->ajaxReturn(['status' => 0, 'msg' => '参数错误', 'result' => ['title' =>'标题不能为空']]);

            $data['createtime'] = time();
            if(M('knowledge')->insert($data)){
                $this->ajaxReturn(['status' => 1, 'msg' => '操作成功']);
            } else {
                $this->ajaxReturn(['status' => -1, 'msg' => '操作失败']);
            }
        }

         // 获取年级分类
        $gradeList = M('grade')->where('is_open', 1)->where('is_delete', 0)->order('id desc')->select();
        $lessonList = M('lesson')->where('is_open', 1)->where('is_delete', 0)->order('id desc')->select();
        

        $this->assign('gradeList', $gradeList);    
        $this->assign('lessonList', $lessonList);    

        return $this->fetch();
    }

    public function edit(){
        if($this->request->isPost()){
            $data = I('post.');
            if(trim($data['title']) == '') $this->ajaxReturn(['status' => 0, 'msg' => '参数错误', 'result' => ['title' =>'标题不能为空']]);

            $id = $data['id'];

            if(M('knowledge')->where('id', $id)->save($data)){
                $this->ajaxReturn(['status' => 1, 'msg' => '操作成功']);
            } else {
                $this->ajaxReturn(['status' => -1, 'msg' => '操作失败']);
            }
        }
        $id = I('id');
        $info = M('knowledge')->where('id', $id)->find();

         // 获取年级分类
        $gradeList = M('grade')->where('is_open', 1)->where('is_delete', 0)->order('id desc')->select();
        $lessonList = M('lesson')->where('is_open', 1)->where('is_delete', 0)->order('id desc')->select();
        

        $this->assign('gradeList', $gradeList);    
        $this->assign('lessonList', $lessonList);    

        $this->assign('info', $info);
        return $this->fetch();
    }

    public function del(){
        $id = I('id');

        if(false !== M('knowledge')->where('id', $id)->update(array('is_delete'=>1))){
            $this->ajaxReturn(['status' => 1, 'msg' => '操作成功']);
        } else {
            $this->ajaxReturn(['status' => 0, 'msg' => '操作失败']);
        }
    }
}