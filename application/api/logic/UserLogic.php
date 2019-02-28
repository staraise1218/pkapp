<?php
/**
 * 短信验证码类
 */

namespace app\api\logic;
use think\Db;

class UserLogic {

    public function getRocketNum($user_id){
        $num = 0;
        // 查看用户是否是vip
        $user = M('users')->where('user_id', $user_id)->field('level, rockets')->find();
        if($user['level'] > 0){
            $num = 2; // 如果是vip 免费2个
        } else {
            $num = 1; // 否则 1 个
        }
        $num += $user['rockets']; // 加上购买的火箭数量

        // 查找当日已使用的火箭次数
        $where = array(
            'user_id' => $user_id,
            'used_date' => date('Y-m-d'),
        );
        $used_num = M('rocket_log')->where($where)->count();
        $num -= $used_num;

        return $num;
    }
    
}