<?php
namespace app\api\model;

use think\Model;


class Dynamics extends Model {

	public function add($data){
		  $dynamicsModel = new Dynamics;
		  $dynamicsModel->save($data);
		  $insert_id = $dynamicsModel->id;
      if($insert_id){
          // 插入图片表
          if(is_array($data['image']) && !empty($data['image'])){
              foreach ($data['image'] as $item) {
                  M('dynamics_image')->insert(array('dynamic_id'=>$insert_id, 'image'=>$item));
              }
          }
          // 保存视频路径
          if($data['video'] && $data['video_thumb']){
              M('dynamics_image')->insert(array('dynamic_id'=>$insert_id, 'image'=>$data['video_thumb'], 'video'=>$data['video']));
          }
          return true;
      } else {
    	   return false;
      }
	}
}