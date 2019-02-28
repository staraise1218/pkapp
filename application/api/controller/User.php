<?php

namespace app\api\controller;
use think\Db;
use app\api\logic\SmsLogic;
use app\api\logic\FileLogic;
use app\api\logic\GeographyLogic;
use app\api\logic\DynamicLogic;
use think\Image;
use app\api\logic\MessageLogic;
use app\api\logic\RongyunLogic;

class User extends Base {

	public function __construct(){
		// 设置所有方法的默认请求方式
		$this->method = 'POST';

		parent::__construct();
	}

    /**
     * [index 我的 页面 需要的接口]
     * @return [type] [description]
     */
    public function index(){
        $user_id = I('user_id');

        /************** 是否有未读消息 *************/
        $message_read = Db::name('message')->alias('m')
            ->join('user_message um', 'um.message_id=m.message_id', 'left')
            ->where(function ($query) use ($user_id){
                $query->where(array('user_id'=>$user_id, 'type'=>'0', 'status'=>'0'));
            })
            ->whereOr(function($query) use ($user_id){
                $query->where('type', '1')->where('status', null);
            })
            ->count();
        $result['message_read'] = $message_read ? 1 : 0;

        /************** 是否有未读评论 *************/
        $comment_read = Db::name('dynamics_comment')->where(array('reply_user_id'=>$user_id, 'is_read'=>0))->count();
        $result['comment_read'] = $comment_read ? 1 : 0;

         /************** 最近来访 5 个头像 *************/
         $visitor = Db::name('user_visitor')->alias('uv')
            ->join('users u', 'uv.user_id=u.user_id','left')
            ->where('to_user_id', $user_id)
            ->limit(5)
            ->order('id desc')
            ->field('head_pic, uv.is_read')
            ->select();
        $result['visitor'] = $visitor;

        response_success($result);
    }

    /**
     * [uploadFile 上传头像/认证视频 app 原生调用]
     * @param [type] $[type] [文件类型 head_pic 头像 auth_video 视频认证]
     * @param  $[action] [ 默认 add 添加 edit 修改]
     * @return [type] [description]
     */
    public function uploadFile(){
        $user_id = I('user_id/d');
        $type = I('type'); 
        $action = I('action', 'add');

        if(!in_array($type, array('head_pic', 'auth_video'))) response_error('', '不被支持的文件类型');
        /******************** 上传文件 **************/
        if($type == 'head_pic') $uploadPath = UPLOAD_PATH.'head_pic/';
        if($type == 'auth_video') $uploadPath = UPLOAD_PATH.'auth_video/';

        $FileLogic = new FileLogic();
        $result = $FileLogic->uploadSingleFile('file', $uploadPath);
        if($result['status'] == '1'){
            $fullPath = $result['fullPath'];

            /**************** 修改用户表 头像记录 ************/
            if($type == 'head_pic'){
                Db::name('users')->update(array('user_id'=>$user_id, 'head_pic'=>$fullPath));

                $resultdata = array('head_pic'=>$fullPath);
                // 更新上传头像动态
                $DynamicLogic = new DynamicLogic();
                $DynamicLogic->add($user_id, 2, array($fullPath));
            }
            /**************** 记录认证视频 ************/
            if($type == 'auth_video'){
                $video_thumb = $FileLogic->video2thumb($fullPath);

                $oldAuthVideo = Db::name('users_auth_video')->where('user_id', $user_id)->find();
                if($oldAuthVideo){
                    Db::name('users_auth_video')->where('id', $oldAuthVideo['id'])->delete();
                }

                Db::name('users_auth_video')->insert(array('user_id'=>$user_id, 'auth_video_url'=> $fullPath, 'video_thumb'=>$video_thumb, 'add_time' => time()));
                // 更新用户表视频认证状态
                Db::name('users')->where('user_id', $user_id)->setField('auth_video_status', 1);

                // 返回前端结果
                $resultdata = array(
                    'video' => $fullPath,
                    'video_thumb' => $video_thumb,
                );
            }

            response_success($resultdata, '上传成功');
            
        } else {
            response_error('', '提交失败');
        }
    }

    // h5 修改头像
    public function editHeadPic(){
        $user_id = I('user_id/d');
        $file = I('file');

        /**************** 修改用户表 头像记录 ************/
        if(false !== Db::name('users')->update(array('user_id'=>$user_id, 'head_pic'=>$file))){
            // 更新上传头像动态
            $DynamicLogic = new DynamicLogic();
            $DynamicLogic->add($user_id, 2, array($file));

            response_success('', '修改成功');
        } else{
            response_error('', '修改失败');
        }
    }

    /**
     * [uploadPhoto 上传照片、精华照片]
     * type 照片类型 1 普通照片 2  精华照片
     * file_type 文件类型 1 图片 2 视频
     * @return [type] [description]
     */
    public function uploadPhoto(){
        $user_id = I('user_id/d');
        $type = I('type'); 
        $file_type = I('file_type', 1); 
        $files = I('file'); 

        /*$uploadPath = UPLOAD_PATH.'photo/';
        $FileLogic = new FileLogic();
        $uploadResult = $FileLogic->uploadMultiFile('file', $uploadPath);*/
        $files = json_decode(htmlspecialchars_decode(html_entity_decode($files)), true);
        foreach ($files as $imageUrl) {
            $photoData = array(
                'user_id' => $user_id,
                'thumb' => $imageUrl,
                'url' => $imageUrl,
                'type' => $type,
                'add_time' => time(),
                'file_type' => $file_type,
            );
            M('user_photo')->insert($photoData);
        }
        
        // 发表动态
        if($file_type == 1){
            $DynamicLogic = new DynamicLogic();
            $DynamicLogic->add($user_id, 4, $files);
        }

        response_success('', '上传成功');

    }

     /**
     * [uploadPhoto 上传照片、精华照片]
     * type 1 普通照片 2  精华照片
     * file_type 1 图片 2 视频
     * @return [type] [description]
     */
    // public function uploadPhoto(){
    //     $user_id = I('user_id/d');
    //     $type = I('type'); 
    //     $file_type = I('file_type', 1); 

    //     $FileLogic = new FileLogic();
        
    //     /************** 处理上传的图片 ****************/
    //     if($file_type == 1){
    //         $uploadPath = UPLOAD_PATH.'photo/';
    //         $uploadResult = $FileLogic->uploadMultiFile('file', $uploadPath);

    //         if($uploadResult['status'] != 1) response_error('', '上传失败');

    //         $images = $uploadResult['image'];
    //         foreach ($images as $imageUrl) {
    //             $photoData = array(
    //                 'user_id' => $user_id,
    //                 'thumb' => $imageUrl,
    //                 'url' => $imageUrl,
    //                 'type' => $type,
    //                 'add_time' => time(),
    //                 'file_type' => $file_type,
    //             );
    //             M('user_photo')->insert($photoData);
    //         }
            
    //         // 发表动态
    //         $description = $type == '1' ? '上传了照片到相册' : '上传了精华照片到相册';
    //         $dynamics_data = array(
    //             'user_id' => $user_id,
    //             'type' => 2,
    //             'description' => $description,
    //             'image' => $images,
    //             'origin' => 4,
    //             'add_time' => time(),
    //         );
    //         D('dynamics')->add($dynamics_data);

    //         response_success(array('files'=>$images));

    //     }
    // }

    // 常见问题
    public function questions(){

        $where = array(
            'cat_id' => '1',
            'is_open' => '1',
        );
        $questions = Db::name('article')->where($where)
            ->order('article_id desc')
            ->field('article_id, title, description, content')
            ->select();

        if(!empty($questions)){
            foreach ($questions as &$item) {
                $item['content'] = html_entity_decode($item['content']);
                $item['link'] = '/mobile/Article/questionDetail?article_id='.$item['article_id'];
            }
        }
        response_success($questions);
    }

    /**
     * [getUserInfo 获取用户基本资料]
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function getUserInfo($user_id){
    	$userInfo = M('users')->where("user_id", $user_id)->find();
        unset($userInfo['password']);
       
       // 计算年龄
        $userInfo['age'] = getAge($userInfo['birthday']);
        response_success($userInfo);
    }

    public function message(){
        $user_id = I('user_id/d');
        $page = I('page/d', 1);

        $limit_start = ($page-1)*20;

        $message = Db::name('message')->alias('m')
            ->join('user_message um', 'um.message_id=m.message_id', 'left')
            ->where('user_id', $user_id)
            ->whereOr('m.type', 1)
            ->field('m.message_id, message, m.category, data, send_time, status')
            ->order('message_id desc')
            ->limit($limit_start, 20)
            ->select();

        if(!empty($message)){
            $now_date = strtotime(date('Y-m-d')); // 今日凌晨
            $mid_date = strtotime(date('Y-m-d 12:00:00')) ;// 今日中午

            foreach ($message as &$item) {
                if($item['send_time'] < $now_date) $item['send_time'] = date('Y-m-d', $item['send_time']);
                if($item['send_time'] > $now_date && $item['send_time'] < $mid_date) $item['send_time'] = '上午'.date('H:i', $item['send_time']);
                if($item['send_time'] > $mid_date) $item['send_time'] = '下午'.date('H:i', $item['send_time']);

                if($item['data']) $item['data'] = unserialize($item['data']);
            }
        }

        response_success($message);
    }

    public function commentList(){
        $user_id = I('user_id');
        $page = I('page', 1);
        $type = I('type', 3);

        $start_limit = ($page-1)*20;
        $lists =  M('dynamics_comment')->alias('dc')
            ->join('dynamics d', 'd.id=dc.dynamic_id', 'left')
            ->join('users u', 'u.user_id=dc.commentator_id', 'left')
            ->where('dc.reply_user_id', $user_id)
            ->field('d.type, d.description, d.content dynamic_content,u.user_id, u.head_pic, u.nickname, u.auth_video_status, u.sex, u.birthday, u.age, dc.dynamic_id, dc.content, dc.parent_id, dc.add_time, dc.type comment_type')
            ->limit($start_limit, 20)
            ->order('dc.id desc')
            ->select();

        if(is_array($lists) && !empty($lists)){
            foreach ($lists as &$item) {
                if(in_array($item['type'], array('2', '3'))){
                    $image = M('dynamics_image')->where('dynamic_id', $item['dynamic_id'])->find();
                    $item['image'] = $image['image'] ?  $image['image'] : '';
                    $item['video'] = $image['video'] ?  $image['video'] : '';
                }
                $item['age'] = getAge($item['birthday']);
            }
        }

         /***************  标记已读 ****************/
        Db::name('dynamics_comment')
            ->where('reply_user_id', $user_id)
            ->where('is_read', 0)
            ->setField('is_read', 1);

        response_success($lists);
    }

    // 身份认证
    public function identityAuth(){
        $user_id = I('user_id');

        $uploadPath =  UPLOAD_PATH.'identityAuth/';
        $FileLogic = new FileLogic();
        $uploadResult = $FileLogic->uploadMultiFile('file', $uploadPath);
        if($uploadResult['status'] == '1'){
            $image = $uploadResult['image'];
        }

        $data = array(
            'user_id' => $user_id,
            'image' => serialize($image),
            'add_time' => time(),
            'status' => '1',
        );

        $count = M('identity_auth')->where('user_id', $user_id)->count();
        if($count){
            M('identity_auth')->where('user_id', $user_id)->update($data);
            
        } else {
            M('identity_auth')->insert($data);
        }
        M('users')->where('user_id', $user_id)->update(array('auth_identity_status'=>1));
        response_success('', '操作成功');
    }

    // 车辆认证
    public function carAuth(){
        $user_id = I('user_id');

        $uploadPath =  UPLOAD_PATH.'carAuth/';
        $FileLogic = new FileLogic();
        $uploadResult = $FileLogic->uploadSingleFile('file', $uploadPath);
        if($uploadResult['status'] == '1'){
            $image = $uploadResult['fullPath'];
        }

        $data = array(
            'user_id' => $user_id,
            'image' => $image,
            'add_time' => time(),
            'status' => '1',
        );

        $count = M('car_auth')->where('user_id', $user_id)->count();
        if($count){
            M('car_auth')->where('user_id', $user_id)->update($data);
            
        } else {
            M('car_auth')->insert($data);
        }
        M('users')->where('user_id', $user_id)->update(array('auth_car_status'=>1));
        response_success('', '操作成功');
    }

    // 关注
    public function attention(){
        $user_id = I('user_id');
        $friend_id = I('friend_id');

        if($user_id == $friend_id) response_error('', '自己不能关注自己');

        // 防止重复关注
        if(M('friend')->where(array('user_id'=>$user_id, 'friend_id'=>$friend_id))->count()){
            response_error('', '已关注');
        }

        $data = array(
            'user_id' => $user_id,
            'friend_id' => $friend_id,
            'add_time' => time(),
        );

        $insert_id = M('friend')->insertGetId($data);
        if($insert_id){
             // 查看是否被关注
            $friend = M('friend')->where(array('user_id'=>$friend_id, 'friend_id'=>$user_id))->find();
            if($friend){
                M('friend')->where('id', $insert_id)->whereOr('id', $friend['id'])->setField('twoway', 1);
            }

            // 发消息
            $userinfo = Db::name('users')->where('user_id', $user_id)->field('nickname')->find();
            $MessageLogic = new MessageLogic();
            $message = $userinfo['nickname'].'关注了你';
            $MessageLogic->add($friend_id, $message);
            // 融云消息
            $RongyunLogic = new RongyunLogic();
            $result = $RongyunLogic->PublishPrivateMessage('1', $friend_id, $message);

            response_success('', '关注成功');
        } else {
            response_error('', '关注失败');
        }
    }

    public function cancelAttention(){
        $user_id = I('user_id');
        $friend_id = I('friend_id');


        M('friend')->where(array('user_id'=>$user_id, 'friend_id'=>$friend_id))->delete();
        M('friend')->where(array('user_id'=>$friend_id, 'friend_id'=>$user_id))->setField('twoway', 0);
        response_success('', '操作成功');
    }

    public function homePage(){
        $user_id = I('user_id');
        $toUserId = I('toUserId');

        /************ 获得自己的信息 **************/
        $user = M('users')->where('user_id', $user_id)->find();

        /*********** 获得对方信息 ************/
        $toUserInfo = M('users')->where('user_id', $toUserId)->find();
        unset($toUserInfo['password']);

        $toUserInfo['age'] = getAge($toUserInfo['birthday']);
        $data['baseinfo'] = $toUserInfo;
        $data['baseinfo']['province'] = $toUserInfo['province'] ? getRegionNameByCode($toUserInfo['province']) : '';
        $data['baseinfo']['city'] = $toUserInfo['city'] ? getRegionNameByCode($toUserInfo['city']) : '';
        //计算距离
        $GeographyLogic = new GeographyLogic();
        $data['baseinfo']['dinstince'] = $GeographyLogic->getDistance($user['longitude'], $user['latitude'], $toUserInfo['longitude'], $toUserInfo['latitude']);
        
        /************* 是否关注 *************/
        $data['baseinfo']['attention'] = 0; // 未关注
        $friend = M('friend')->where(array('user_id'=>$user['user_id'], 'friend_id'=>$toUserId))->find();
        if($friend){
            $data['baseinfo']['attention'] = 1; // 已关注
            if($friend['twoway']) $data['baseinfo']['attention'] = 3; // 好友
        } else {
            $friend = M('friend')->where(array('user_id'=>$toUserId, 'friend_id'=>$user['user_id']))->find();
            if($friend) $data['baseinfo']['attention'] = 2; // 被关注
        }
        
        /************** 他的邀约 *********/
        $data['invite'] = M('invite')->where('user_id', $toUserId)
            ->field('id, title')
            ->order('id desc')
            ->limit(5)
            ->select();
        /************** 他的动态 *********/
        $data['dynamics'] = M('dynamics')->where('user_id', $toUserId)
            ->field('id, description, content')
            ->order('id desc')
            ->find();

        /************** 他的照片 *********/
        $data['photos'] = M('user_photo')->where('user_id', $toUserId)
            ->field('id, thumb, url, type, file_type')
            ->order('id desc')
            ->select();

        /************** 记录来访者 *****************/
        $visitordata = array(
            'user_id'=>$user_id,
            'to_user_id' => $toUserId,
            'add_time' => time(),
        );
        Db::name('user_visitor')->where(array('user_id'=>$user_id, 'to_user_id'=>$toUserId))->delete();
        Db::name('user_visitor')->insert($visitordata);

        /************** 他收到的礼物 *********************/
        $subQuery = M('gift_gived')->where('to_user_id', $toUserId)->order('id desc')->buildSql();
        $data['gift'] = Db::table($subQuery.' sub')
            ->field('image, count(*) count')
            ->group('gift_id')
            ->select();
        // $data['gift'] = M('gift_gived')
        //     ->where('to_user_id', $toUserId)
        //     ->field('image, count(*) count')
        //     ->group('gift_id')
        //     ->order('id desc')
        //     ->select();

        /************** 统计数量 *************/
        $data['count']['normalPhotoCount'] = Db::name('user_photo')->where(array('user_id'=>$toUserId, 'type'=>1))->count();
        $data['count']['jinghuaPhotoCount'] = Db::name('user_photo')->where(array('user_id'=>$toUserId, 'type'=>2))->count();
        $data['count']['inviteCount'] = Db::name('invite')->where(array('status'=>2, 'user_id'=>$toUserId))->count();
        $data['count']['dynamicsCount'] = Db::name('dynamics')->where(array('status'=>2, 'user_id'=>$toUserId))->count();
        $data['count']['giftCount'] = Db::name('gift_gived')->where(array('to_user_id'=>$toUserId))->count();

        response_success($data);
    }

    public function myHomePage(){
        $user_id = I('user_id');
        /************ 获得自己的信息 **************/
        $user = M('users')->where('user_id', $user_id)->find();
        unset($user['password']);

        $user['age'] = getAge($user['birthday']);
        $data['baseinfo'] = $user;

        /************** 我的的邀约 *********/
        $data['invite'] = M('invite')->where('user_id', $user_id)
            ->field('id, title')
            ->order('id desc')
            ->limit(5)
            ->select();
        /************** 我的的动态 *********/
        $data['dynamics'] = M('dynamics')->where('user_id', $user_id)
            ->field('id, description, content')
            ->order('id desc')
            ->find();

        /************** 我的照片 *********/
        $data['photos'] = M('user_photo')
            ->where('user_id', $user_id)
            ->where('type', 1)
            ->field('id, thumb, url, type, file_type')
            ->order('id desc')
            ->select();

        /************** 我的精华照片 *********/
        $data['quintessence_photos'] = M('user_photo')
            ->where('user_id', $user_id)
            ->where('type', 2)
            ->field('id, thumb, url, type, file_type')
            ->order('id desc')
            ->select();
        /************** 我收到的礼物 *********************/
        $subQuery = M('gift_gived')->where('to_user_id', $user_id)->order('id desc')->buildSql();
        $data['gift'] = Db::table($subQuery.' sub')
            ->field('image, count(*) count')
            ->group('gift_id')
            ->select();

        /************** 统计数量 *************/
        $data['count']['normalPhotoCount'] = Db::name('user_photo')->where(array('user_id'=>$user_id, 'type'=>1))->count();
        $data['count']['jinghuaPhotoCount'] = Db::name('user_photo')->where(array('user_id'=>$user_id, 'type'=>2))->count();
        $data['count']['inviteCount'] = Db::name('invite')->where(array('status'=>2, 'user_id'=>$user_id))->count();
        $data['count']['dynamicsCount'] = Db::name('dynamics')->where(array('status'=>2, 'user_id'=>$user_id))->count();
        $data['count']['giftCount'] = Db::name('gift_gived')->where(array('to_user_id'=>$user_id))->count();

        response_success($data);
    }

    /**
     * [changeLocation 更新位置]
     * @return [type] [description]
     */
    public function changeLocation(){
        $user_id = I('user_id', 1);
        $data['province'] = I('province');
        $data['city'] = I('city');
        $data['longitude'] = I('longitude');
        $data['latitude'] = I('latitude');

        if($user_id == 2) response_success('', '操作成功'); // 上线后取消

        if(M('users')->where('user_id', $user_id)->update($data) !== false){
            response_success('', '操作成功');
        } else {
            response_error('', '操作失败');
        }
    }

    /**
     * [changeInfo 编辑个人资料]
     * @return [type] [description]
     */
    public function changeInfo(){
        $user_id = I('user_id');
        $field = I('field');
        $fieldValue = I('fieldValue');

        if(M('users')->where('user_id', $user_id)->setField($field, $fieldValue) !== false){
            response_success('', '操作成功');
        } else {
            response_error('', '操作失败');
        }
    }

    /**
     * [visitors 来访者]
     * @param type 1 来访者 2 我看过的人
     * @return [type] [description]
     */
    public function visitor(){
        $user_id = I('user_id');
        $page = I('page', 1);
        $type = I('type', 1);

        if($type == '1'){
            $join_on = 'uv.user_id = u.user_id';
            $where['uv.to_user_id'] = $user_id;
            $field = 'uv.user_id, head_pic, nickname, birthday, age, sex, uv.add_time, signature';
        } else {
            $join_on = 'uv.to_user_id = u.user_id';
            $where['uv.user_id'] = $user_id;
            $field = 'to_user_id user_id, head_pic, nickname, birthday, age, sex, uv.add_time, signature';
        }

        $limit_start = ($page-1)*10;
        $lists = M('user_visitor')->alias('uv')
            ->join('users u', $join_on, 'left')
            ->where($where)
            ->field($field)
            ->order('uv.id desc')
            ->limit($limit_start, 10)
            ->select();

        if(is_array($lists) && !empty($lists)){
            foreach ($lists as &$item) {
                $item['age'] = getAge($item['birthday']);
            }
        }

        /*************** 进入来访者 标记已读 ****************/
        Db::name('user_visitor')
            ->where('to_user_id', $user_id)
            ->where('is_read', 0)
            ->setField('is_read', 1);

        response_success($lists);
    }

    /**
     * [friend 我的好友]
     * @return [type] [description]
     */
    public function friend(){
        $user_id = I('user_id', 1);

        $lists = M('friend')->alias('f')
            ->join('users u', 'f.friend_id = u.user_id', 'left')
            ->where('f.user_id', $user_id)
            ->where('twoway', '1')
            ->order('f.id desc')
            ->field('friend_id user_id, head_pic, nickname, auth_video_status, rongyun_token')
            ->select();

        response_success($lists);
    }

    // 通过uuid查找人，用来加好友
    public function searchFriend(){
        $user_id = I('user_id'); // 我的id
        $uuid = I('uuid'); // 要搜索的id

        $friendInfo = M('users')->where('uuid', $uuid)
            ->field('user_id, head_pic, nickname, sex, birthday, age, signature, auth_video_status')
            ->find();

        if(empty($friendInfo)) response_error('', '用户不存在');
        if($friendInfo['user_id'] == $user_id) response_error('', '不能添加自己为好友');

        /***************** 查看好友关系是怎么样的 *****************/
        $friend = M('friend')->where(array('user_id'=>$user_id, 'friend_id'=>$friendInfo['user_id']))->find();
        $friendInfo['attention'] = 0; // 未关注
        if($friend){
            $friendInfo['attention'] = 1; // 已关注
            if($friend['twoway']) $friendInfo['attention'] = 3; // 好友
        } else {
            $friend = M('friend')->where(array('user_id'=>$toUserId, 'friend_id'=>$user['user_id']))->find();
            if($friend) $friendInfo['attention'] = 2; // 被关注
        }

        $friendInfo['age'] = getAge($friendInfo['birthday']);

        response_success($friendInfo);
    }

    /**
     * [friend 关注和粉丝]
     * @param type 1 关注 2 粉丝
     * @return [type] [description]
     */
    public function attentionFans(){
        $user_id = I('user_id', 1);
        $type = I('type', 1);
        $page = I('page', 1);

        if($type == '1'){
            $join_on = 'f.friend_id = u.user_id';
            $where['f.user_id'] = $user_id;
            $field = 'friend_id user_id, head_pic, nickname, auth_video_status, twoway';
        } else {
            $join_on = 'f.user_id = u.user_id';
            $where['f.friend_id'] = $user_id;
            $field = 'u.user_id, head_pic, nickname, auth_video_status, twoway';
        }

        $limit_start = ($page-1)*20;
        $lists = M('friend')->alias('f')
            ->join('users u', $join_on, 'left')
            ->where($where)
            ->order('f.id desc')
            ->field($field)
            ->limit($limit_start, 20)
            ->select();

        response_success($lists);
    }

    // 意见反馈
    public function feedback(){
        $data['user_id'] = I('user_id');
        $data['content'] = I('content');

        $data['add_time'] = time();

        if($_FILES['image']){
            $FileLogic = new FileLogic();
            $uploadPath = UPLOAD_PATH.'feedback';
            $result = $FileLogic->uploadSingleFile('image', $uploadPath);
            if($result['status'] == '1'){
                $data['image'] = $result['fullPath'];
            } else {
                response_error('', '文件上传失败');
            }
        }

        if(M('feedback')->insert($data)){
            response_success('', '操作成功');
        } else {
            response_error('', '操作失败');
        }
    }

    // 获取用户认证视频连接
    public function getAuthVideoUrl(){
        $user_id = I('user_id');

        $video = M('users_auth_video')->where('user_id', $user_id)->field('auth_video_url')->find();
        response_success(array('video_url'=>$video['auth_video_url']));
    }

        // 标记读消息
    public function readMessage(){
        $user_id = I('user_id');
        $message_id = I('message_id');

        $count = M('user_message')->where(array('user_id'=>$user_id, 'message_id'=>$message_id))->count();
        if($count){
            M('user_message')->where(array('user_id'=>$user_id, 'message_id'=>$message_id))->setField('status', 1);
        } else {
            $data = array(
                'user_id' => $user_id,
                'message_id' => $message_id,
                'status' => '1',
            );
            M('user_message')->insert($data);
        }

        response_success();
    }

    // 签到  
    public function signIn(){
        $user_id = I('user_id');

        $count = Db::name('user_sign_log')->where(array('user_id'=>$user_id, 'date'=>date('Y-m-d')))->count();
        if($count) response_error('', '您今天已签到');

        $setInc = array(
            'flower_num' => array('exp', 'flower_num+10'),
            'signInDays' => array('exp', 'signInDays+1'),
        );
        Db::name('users')->where('user_id', $user_id)->update($setInc);
        $data = array(
            'user_id' => $user_id,
            'date' => date('Y-m-d'),
        );
        Db::name('user_sign_log')->insert($data);

        response_success('', '签到成功');
    }

    // 代表大会
    public function  congress(){
        $filepath = RUNTIME_PATH.'cache/congress.php';
        if(file_exists($filepath)){
            $users = include $filepath;
        } else {
            $users = Db::name('users')->where(array('is_lock'=>0, 'sex'=>2))->order('fansNum desc')->field('user_id, head_pic, nickname, sex, birthday, age, auth_video_status')->limit(50)->select();

            if(is_array($users) && is_array($users)){
                foreach ($users as &$item) {
                    $item['age'] = getAge($item['birthday']);
                    unset($item['birthday']);
                }
            }

            file_put_contents($filepath, "<?php \r\n return ".var_export($users, true).';');
        }

        response_success($users);
    }

    // 删除相册里的图片
    public function delPhoto(){
        $user_id = I('user_id');
        $id = I('id');

        if(Db::name('user_photo')->where(array('user_id'=>$user_id, 'id'=>$id))->delete()){
            response_success();
        } else {
            response_error();
        }
    }

    // 我的账户页面
    public function myaccount(){
        $user_id = I('user_id');

        $user = M('users')
                        ->where('user_id', $user_id)
                        ->field('goldcoin, glamour, flower_num, level')
                        ->find();

        $goldcoin = M('goldcoin')
                        ->where('is_delete', 0)
                        ->order('sort desc, id asc')
                        ->field('id, num, give_num, price, thumb')
                        ->select();

        $user['withdraw'] = $user['glamour']/100;
        
        $result['user'] = $user;
        $result['goldcoin'] = $goldcoin;
        response_success($result);
    }

    // 申请提现
    public function withdraw(){
        $user_id = I('user_id');

        $user = M('users')->where('user_id', $user_id)
                            ->field('glamour')
                            ->find();

        // 获取系统设置
        $shopinfo_config = tpCache('shop_info');
        $min_money = $shopinfo_config['min_money']; // 最低提现金额
        $poundage = $shopinfo_config['poundage'];  // 提现手续费率（%）

        $data = array(
            'glamour' => $user['glamour'],
            'money' => $user['glamour']/100, // 可提现金额
            'money_min' => $min_money, // 最低提现金额
            'poundage' => $poundage, // 提现手续费率（%）
        );
        
        response_success($data);
    }

    public function doWithdraw(){
        $user_id = I('user_id');
        $money = I('money');
        $account = I('account');
        $name = I('name');

        // 获取系统设置
        $shopinfo_config = tpCache('shop_info');
        $min_money = $shopinfo_config['min_money']; // 最低提现金额
        $poundage = $shopinfo_config['poundage'];  // 提现手续费率（%）

        if($money < $money_min) response_error('', '对不起，提现金额不能少于'.$min_money.'元');
        if(empty($account)) response_error('', '请填写支付宝信息');

        $user = M('users')->where('user_id', $user_id)
                            ->field('glamour')
                            ->find();

        $xiaofei_glamour = $money*100;
        if($xiaofei_glamour > $user['glamour']) response_error('', '超出可提现金额');

        // 提现手续费
        $poundage_money = round($money*$poundage/100, 2);
        $money = round($money-$poundage_money, 2);
        $data = array(
            'user_id' => $user_id,
            'money' => $money,
            'account' => $account,
            'name' => $name,
            'createtime' => time(),
            'poundage' => $poundage,
            'poundage_money' => $poundage_money,
        );

        if(Db::name('withdraw')->insert($data)){
            Db::name('users')->where('user_id', $user_id)
                ->inc('frozen_glamour', $xiaofei_glamour)
                ->dec('glamour', $xiaofei_glamour)
                ->update();
            response_success('', '申请提现成功，请注意查收');
        } else {
            response_error('', '操作失败');
        }
    }

    // 我的礼物页面
    public function mygiftlist(){
        $user_id = I('user_id');
        $page = I('page', 1);

        $limit_start = ($page-1)*10;
        $list = M('gift_gived')->alias('gg')
            ->join('users u', 'gg.user_id=u.user_id', 'left')
            ->where('gg.to_user_id', $user_id)
            ->limit($limit_start, 10)
            ->field('head_pic, nickname, birthday, gg.goldcoin, gg.image')
            ->order('id desc')
            ->select();

        if(is_array($list) && !empty($list)){
            foreach ($list as &$item) {
                $item['age'] = getAge($image['birthday']);
            }
        }

        response_success($list);
    }

    // 统计消息数量
    public function messageCount(){
        $user_id = I('user_id');

        $count = Db::name('message')->alias('m')
            ->join('user_message um', 'um.message_id=m.message_id', 'left')
            ->where(function ($query) use ($user_id){
                $query->where(array('user_id'=>$user_id, 'type'=>'0', 'status'=>'0'));
            })
            ->whereOr(function($query) use ($user_id){
                $query->where('type', '1')->where('status', null);
            })
            ->count();

        response_success(array('count'=>$count));
    }
}