<?php
/**
 * 文件上传类
 */

namespace app\api\logic;
use think\Db;
use think\Controller;

class DynamicLogic extends Controller {
	/**
	 * 间接发布的动态
	 * @param $origin 动态来源 1 发布动态 2 上传头像3 认证视频 4 上传了照片到相册 5 上传了精华照片到相册
	 * image 数组
	 * videodata 包含video video_thumb
	 */
	public function add($user_id, $origin, $images=[], $videodata=[]){
		switch ($origin) {
			case '2':
				$description = '更新了形象照';
				break;
			case '3':
				$description = '更新了形象视频';
				break;
			case '4':
				$description = '更新了照片到相册';
				break;
			case '5':
				$description = '更新了精华照片到相册';
				break;
			
			default:
				$description = '';
				break;
		}

		$data = array(
			'user_id' => $user_id,
			'description' => $description,
			'origin' => $origin,
			'add_time' => time(),
		);
		/********************* 根据后台设置的是否审核动态来定动态状态 ***************/
        $shopinfo_config = tpCache('shop_info');
        $data['status'] = ($shopinfo_config['examine_invite'] == '1' ? 1 : 2);

        if($images){
        	$data['image'] = $images;
        	$data['type'] = 2;
        }
        if($videodata){
        	$data['video'] = $videodata['video'];
        	$data['video_thumb'] = $videodata['video_thumb'];
        	$data['type'] = 3;
        }

        return D('Dynamics')->add($data);
	}
}