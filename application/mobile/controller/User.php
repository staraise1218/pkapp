<?php

namespace app\mobile\controller;

use think\Db;

class User extends Base {


    public function comment(){
        return $this->fetch();
    }
   
    /**
     * [visitors 来访者]
     * @param type 1 来访者 2 我看过的人
     * @return [type] [description]
     */
    public function visitor(){
        return $this->fetch();
    }

    /**
     * [friend 我的好友]
     * @return [type] [description]
     */
    public function friend(){

        return $this->fetch();
    }

    /**
     * [friend 关注和粉丝]
     * @param type 1 关注 2 粉丝
     * @return [type] [description]
     */
    public function attention(){
       
        return $this->fetch();
    }

    public function fans(){
        return $this->fetch();
    }

    /**
     * [addFriend 添加好友]
     */
    public function addFriend(){

        return $this->fetch();
    }

    // 查看别人主页
    public function homePage(){
        $user_id = I('user_id');
        $toUserId = I('toUserId');
        if($user_id == $toUserId){
            $this->redirect('user/myHomePage');
        }

        return $this->fetch();
    }

    // 我的个人主页
    public function myHomePage(){

        return $this->fetch();
    }

    public function editHomePage(){

        return $this->fetch();
    }

    public function myDynamicList(){

        return $this->fetch();
    }

    public function signIn(){
        $user_id = I('user_id');
        // 取出签到页面的数据
        $user = Db::name('users')->where('user_id', $user_id)->field('signInDays, flower_num')->find();

        $count = Db::name('user_sign_log')->where(array('user_id'=>$user_id, 'date'=>date('Y-m-d')))->count();

        $isSign = $count ? 1 : 0;

        $this->assign('isSign', $isSign);
        return $this->fetch();
    }

    // 全国人气代表大会
    public function congress(){
        return $this->fetch();
    }

    // 礼物页面 toUserId
    public function gift(){
        return $this->fetch();
    }
    
    // 礼物页面 toUserId
    public function mygiftlist(){
        return $this->fetch();
    }

    // 申请提现页面
    public function withdraw(){
        return $this->fetch();
    }
}
