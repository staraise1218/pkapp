<?php

namespace app\api\controller;
use think\Db;
use app\api\logic\UserLogic;
use app\api\logic\GeographyLogic;

class Index extends Base {

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
        // 获取banner
        $bannerList = Db::name('ad')
            ->where('pid', 1)
            ->where('enabled', 1)
            ->field('ad_name, ad_link, ad_code')
            ->order('orderby desc, ad_id desc')
            ->select();

        // 获取动态
        $where['d.status'] = '2';
        $dynamiclist = Db::name('dynamics')->alias('d')
            ->join('users u', 'd.user_id=u.user_id', 'left')
            ->where($where)
            ->field('u.user_id, head_pic, nickname, u.sex, u.birthday, u.age, d.id dynamic_id, d.type, d.description, d.content, d.location, d.add_time, d.flower_num')
            ->order('d.id desc')
            ->limit($limit_start, 2)
            ->select();

        if(is_array($dynamiclist) && !empty($dynamiclist)){
            
            foreach ($dynamiclist as $k => &$item) {
                $item['age'] = getAge($item['birthday']);
                // 图片类型，取出图片
                if($item['type'] == '2'){
                    $dynamics_image = M('dynamics_image')->where('dynamic_id', $item['dynamic_id'])->field('image')->select();
                    $image = array();
                    if(is_array($dynamiclist) && !empty($dynamiclist)){
                        foreach ($dynamics_image as $v) {
                            $image[] = $v['image'];
                        }
                    }
                    $item['image'] = $image;
                }
                // 视频
                if($item['type'] == '3'){
                    $dynamics_image = M('dynamics_image')->where('dynamic_id', $item['dynamic_id'])->field('image, video video_url')->find();
                    
                    $item['video_thumb'] = $dynamics_image['image'];
                    $item['video_url'] = $dynamics_image['video_url'];
                }

                // 查看人数
                $item['viewer_count'] = M('dynamics_viewer')->where('dynamic_id', $item['dynamic_id'])->count();
                // 评论数 
                $item['comment_count'] = M('dynamics_comment')->where('dynamic_id', $item['dynamic_id'])->where('type', 1)->count();
            }
        }
        
        $result['bannerList'] = $bannerList;
        $result['dynamiclist'] = $dynamiclist;
        response_success($result);
    }
}