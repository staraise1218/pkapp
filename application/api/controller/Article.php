<?php

namespace app\api\controller;
use think\Db;

class Article extends Base {

	public function __construct(){
		// 设置所有方法的默认请求方式
		$this->method = 'POST';

		parent::__construct();
	}
    
    /**
     * [index description]
     * @return [type] [description]
     */
    public function index(){
       $page = I('page', 1);

        // 获取知识点
        $list = Db::name('article')
            ->where('cat_id', 2)
            ->where('is_open', 1)
            ->order('article_id desc')
            ->page($page)
            ->limit(15)
            ->field('article_id, title, thumb, description')
            ->select();
        
        $result['list'] = $list;
        response_success($result);
    }

    public function getContent(){
        $article_id = I('article_id');

        $info = Db::name('article')
            ->where('article_id', $article_id)
            ->field('article_id, title, thumb, content')
            ->find();
        $info['content'] = htmlspecialchars_decode($info['content']);

        response_success($info);
    }

    /**
     * [collect description]
     * @return [type] [description]
     */
    public function collect(){
        $user_id = I('user_id');
        $article_id = I('article_id');

        $count = Db::name('article_collect')
            ->where('article_id', $article_id)
            ->where('user_id', $user_id)
            ->count();

        if($count) response_success('', '已收藏');

        $data = array(
            'article_id' => $article_id,
            'user_id' => $user_id,
            'add_time' => time(),
        );
        Db::name('article_collect')->insert($data);
        response_success('', '收藏成功');
    }
}