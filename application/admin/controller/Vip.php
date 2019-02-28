<?php


namespace app\admin\controller;
use think\AjaxPage;
use think\Page;
use think\Db;
use app\api\logic\MessageLogic;

class Vip extends Base {

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

            $where['paytime'] = array('BETWEEN', array($start_time, $end_time));

            $start_time = date('Y-m-d H:i:s', $start_time);
            $end_time = date('Y-m-d H:i:s', $end_time);
        }


        $count = M('vip_order')->alias('vo')
            ->join('users u', 'vo.user_id=u.user_id', 'left')
            ->where($where)
            ->where('paystatus', 1)
            ->count();

        $Page = new Page($count,10);
        $show = $Page->show();

        $lists = M('vip_order')->alias('vo')
            ->where($where)
            ->join('users u', 'vo.user_id=u.user_id', 'left')
            ->where('paystatus', 1)
            ->order('id desc')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->field('u.user_id, u.nickname, u.vip_expire_date, u.uuid, vo.id, vo.amount, vo.level, vo.paystatus, vo.paytime')
            ->select();

        // 统计vip购买数量
        $total_amount = M('vip_order')->alias('vo')
            ->join('users u', 'vo.user_id=u.user_id', 'left')
            ->where($where)
            ->where('paystatus', 1)
            ->sum('amount');

                           
        $show = $Page->show();
        $this->assign('lists',$lists);
        $this->assign('show', $show);
        $this->assign('searchtype', $searchtype);
        $this->assign('keyword', $keyword);
        $this->assign('start_time', $start_time);
        $this->assign('end_time', $end_time);
        $this->assign('total_amount', $total_amount);
        return $this->fetch();
    }

    public function export(){
        $strTable ='<table width="500" border="1">';
        $strTable .= '<tr>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="100">会员昵称</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;width:80px;">uuid</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="*">会员级别</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="*">支付金额</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="*">支付时间</td>';
        $strTable .= '</tr>';
        $count = M('vip_order')->where('paystatus', 1)->count();
        $p = ceil($count/5000);
        for($i=0;$i<$p;$i++){
            $start = $i*5000;
            $end = ($i+1)*5000;
            $list = M('vip_order')->alias('vo')
                ->join('users u', 'vo.user_id=u.user_id', 'left')
                ->where('vo.paystatus', 1)
                ->order('vo.id desc')
                ->limit($start.','.$end)
                ->select();
            if(is_array($list)){
                foreach($list as $k=>$val){
                    
                    switch ($val['level']) {
                        case 1:
                            $level_name = '白银VIP';
                            break;
                        case 2:
                            $level_name = '黄金VIP';
                            break;
                        case 3:
                            $level_name = '铂金VIP';
                            break;
                        case 4:
                            $level_name = '钻石VIP';
                            break;
                    }
                    $strTable .= '<tr>';
                    $strTable .= '<td style="text-align:center;font-size:12px;">'.$val['nickname'].'</td>';
                    $strTable .= '<td style="text-align:left;font-size:12px;">'.$val['uuid'].' </td>';
                    $strTable .= '<td style="text-align:left;font-size:12px;">'.$level_name.'</td>';
                    $strTable .= '<td style="text-align:left;font-size:12px;">'.$val['amount'].' </td>';
                    $strTable .= '<td style="text-align:left;font-size:12px;">'.date('Y-m-d H:i',$val['paytime']).'</td>';
                    $strTable .= '</tr>';
                }
                unset($list);
            }
        }
        $strTable .='</table>';
        downloadExcel($strTable,'vip购买记录');
        exit();
    }
 }