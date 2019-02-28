<?php

namespace app\api\controller;
use think\Db;

class Task extends Base {
    public function __construct(){
        // 设置所有方法的默认请求方式
        $this->method = 'GET';

        parent::__construct();
    }

    // 没5分钟更新一次 人气代表大会数据
	public function generateCongress(){
        $users = Db::name('users')->where(array('is_lock'=>0, 'sex'=>2))->order('fansNum desc')->field('user_id, head_pic, nickname, sex, birthday, age, auth_video_status')->limit(50)->select();

        if(is_array($users) && is_array($users)){
            foreach ($users as &$item) {
                $item['age'] = getAge($item['birthday']);
                unset($item['birthday']);
            }
        }

        $filepath = RUNTIME_PATH . 'cache/congress.php';

        file_put_contents($filepath, "<?php \r\n return ".var_export($users, true).';');
	}

    public function test(){
        echo date('Y-m-d H:i:s');
        echo phpinfo();

    }
}