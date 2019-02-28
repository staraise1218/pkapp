<?php

namespace app\admin\controller;
use think\AjaxPage;
use think\Page;
use think\Db;
use app\api\logic\MessageLogic;

class Withdraw extends Base {

    public function index(){
        $searchtype = I('searchtype');
        $keyword = I('keyword', '');
        $start_time = I('start_time');
        $end_time = I('end_time');
        $status = I('status');

        // 搜索条件
        $where = array();
        if($keyword){
            if($searchtype == 'nickname') $where['nickname'] = array('like', "%$keyword%");
            if($searchtype == 'uuid') $where['uuid'] = $keyword;
        }

        if($start_time && $end_time){
            $start_time = strtotime($start_time);
            $end_time = strtotime($end_time);

            $where['createtime'] = array('BETWEEN', array($start_time, $end_time));

            $start_time = date('Y-m-d H:i:s', $start_time);
            $end_time = date('Y-m-d H:i:s', $end_time);
        }

        if($status || $status != '') $where['status'] = $status;
        
        $count = M('Withdraw')->alias('w')
            ->join('users u', 'w.user_id=u.user_id', 'left')
            ->where($where)
            ->count();

        $Page = new Page($count,10);
        $show = $Page->show();

        $lists = M('Withdraw')->alias('w')
            ->join('users u', 'w.user_id=u.user_id', 'left')
            ->where($where)
            ->order('w.id desc')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->field('u.user_id, u.nickname, u.uuid, w.id, w.money, w.account, w.name, w.createtime, w.status, w.mark')
            ->select();
                           
        $this->assign('lists',$lists);
        $this->assign('show', $show);
        $this->assign('searchtype', $searchtype);
        $this->assign('keyword', $keyword);
        $this->assign('start_time', $start_time);
        $this->assign('end_time', $end_time);
        $this->assign('status', $status);
        return $this->fetch();
    }
    
    public function export(){
        $strTable ='<table width="500" border="1">';
        $strTable .= '<tr>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="100">会员昵称</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;width:80px;">uuid</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="*">提现金额</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="*">支付宝账号</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="*">姓名</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="*">申请日期</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="*">状态</td>';
        $strTable .= '</tr>';
        $count = M('withdraw')->count();
        $p = ceil($count/5000);
        for($i=0;$i<$p;$i++){
            $start = $i*5000;
            $end = ($i+1)*5000;
            $list = M('withdraw')->alias('w')
                ->join('users u', 'w.user_id=u.user_id', 'left')
                ->order('w.id desc')
                ->limit($start.','.$end)
                ->select();
            if(is_array($list)){
                foreach($list as $k=>$val){
                    $status = $val['status'] == 1 ? '已处理' : '待处理';
                    $strTable .= '<tr>';
                    $strTable .= '<td style="text-align:center;font-size:12px;">'.$val['nickname'].'</td>';
                    $strTable .= '<td style="text-align:left;font-size:12px;">'.$val['uuid'].' </td>';
                    $strTable .= '<td style="text-align:left;font-size:12px;">'.$val['money'].'</td>';
                    $strTable .= '<td style="text-align:left;font-size:12px;">'.$val['account'].'</td>';
                    $strTable .= '<td style="text-align:left;font-size:12px;">'.$val['name'].'</td>';
                    $strTable .= '<td style="text-align:left;font-size:12px;">'.date('Y-m-d H:i',$val['createtime']).'</td>';
                    $strTable .= '<td style="text-align:left;font-size:12px;">'.$status.'</td>';
                    $strTable .= '</tr>';
                }
                unset($list);
            }
        }
        $strTable .='</table>';
        downloadExcel($strTable,'提现申请'.$i);
        exit();
    }

    public function detail(){
    	$id = I('id');
        if(IS_POST){
            $data = array(
                'status' => I('status'),
                'mark' => I('mark'),
            );

            if(false !== Db::name('withdraw')->where('id', $id)->update($data)){
                exit($this->success('修改成功', U('withdraw/index')));
            }
            exit($this->error('未作内容修改或修改失败'));
        }
    	$info = M('Withdraw')->alias('w')
            ->join('users u', 'w.user_id=u.user_id', 'left')
            ->where('w.id', $id)
            ->find();

    	$this->assign('info', $info);
    	return $this->fetch();
    }

    public function changeStatus(){
    	$id = I('id');
    	$status = I('status');
    	$user_id = I('user_id');

    	$result = M('Withdraw')->where('id', $id)->setField('status', $status);

        // 发送站内消息
        // if($result && in_array($status, array(2, 3))){
        //     $messsage = $status == 2 ? '恭喜您，身份认证已通过' : '很抱歉，身份认证未通过';
        //     $MessageLogic = new MessageLogic();
        //     $MessageLogic->add($user_id, $messsage);
        // }
    }
 }