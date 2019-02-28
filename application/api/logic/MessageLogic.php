<?php
// 站内消息类
namespace app\api\logic;

use think\Db;

class MessageLogic {

    /**
     * [add 发送站内系统消息]
     * @param integer $uses [0 代表全体消息 可以为 1 或者数组]
     * @param string  $message [description]
     */
    public function add($users=0, $message=''){
        $message = array(
            'admin_id' => 1,
            'message' => $message,
            'category' => 0,
            'send_time' => time()
        );

        if ($users == 0) {
            //全体用户系统消息
            $message['type'] = 1;
            M('Message')->add($message);
        } else {
            //个体消息
            $message['type'] = 0;
            $message_id = M('Message')->add($message);
            if(!is_array($users)) $users = array($users);
            foreach ($users as $user_id) {
                M('user_message')->add(array('user_id' => $user_id, 'message_id' => $message_id, 'status' => 0, 'category' => 0));
            }
        }
    }
}