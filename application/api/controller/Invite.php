<?php

namespace app\api\controller;
use think\Db;
use app\api\logic\FileLogic;
use app\api\logic\GeographyLogic;
use app\api\logic\MessageLogic;
use app\api\logic\RongyunLogic;

class Invite extends Base {

	public function __construct(){
		// 设置所有方法的默认请求方式
		$this->method = 'POST';

		parent::__construct();
	}

    /**
     * [index 邀约列表]
     * @param [type] $[order_type] [排序类型 1 最新发布 2 近期约会 3 离我最近]
     * @return [type] [description]
     */
    public function index(){
        $user_longitude = I('longitude');
        $user_latitude = I('latitude');
        $type = I('type');
        $sex = I('sex', 0);
        $province = I('province');
        $city = I('city');
        $auth_video = I('auth_video');
        $page = I('page', 1);
        /************************ 要查询的字段 ***************************/
        $field = 'u.user_id, head_pic, auth_video_status, nickname, u.sex, u.birthday, u.age, i.id, i.title, i.description, i.time, i.place, image';
        /************************ 排序 ***************************/
        $order_type = I('order_type', 1);
        if($order_type == 1) $order = 'add_time desc';
        if($order_type == 2) {
            $where['time'] = array('>=', date('Y-m-d'));
            $order = 'time asc';
        }

        $GeographyLogic = new GeographyLogic();
        // 计算1000km 范围内的经纬度
        /*$around = $GeographyLogic->getAround($user_longitude, $user_latitude, 1000000);
        $where['u.longitude'] = array('BETWEEN', array($around['minLongitude'], $around['maxLongitude']));
        $where['u.latitude'] = array('BETWEEN', array($around['minLatitude'], $around['maxLatitude']));*/
        // sql 计算距离 并按距离排序
        $field .= ", ROUND(6378.138*2*ASIN(SQRT(POW(SIN(($user_latitude*PI()/180-u.latitude*PI()/180)/2),2)+COS($user_latitude*PI()/180)*COS(u.latitude*PI()/180)*POW(SIN(($user_longitude*PI()/180-u.longitude*PI()/180)/2),2))), 2) AS distance";
        if($order_type == 3) {
            $order = 'distance asc';
        }

        /************************ 筛选条件 ***************************/
        if($type) $where['type'] = $type;
        if($sex) $where['u.sex'] = ($sex == 1) ? 1 : 2;
        if($province) $where['u.province'] = $province;
        if($city) $where['u.city'] = $city;
        if($auth_video) $where['u.auth_video_status'] = 2;

        $where['i.status'] = '2';
        $limit_start = ($page-1)*10;
        $lists = Db::name('invite')->alias('i')
            ->join('users u', 'i.user_id=u.user_id', 'left')
            ->where($where)
            ->field($field)
            ->order($order)
            ->limit($limit_start, 10)
            ->select();

        if(!empty($lists)){
            
            foreach ($lists as $k => &$item) {
                // 反序列化图片
                $image = $item['image'] ? unserialize($item['image']) : '';
                $item['image'] = $image ? $image[0] : '';
                $item['image_num'] = count($image);

                // 计算用户和发布者之间的距离，sql计算出来的是米 这里转换成 km
                // $item['distance'] = round($item['distance']/1000, 2); 

                // 计算年龄
                $item['age'] = getAge($item['birthday']);
            }
        }
        response_success($lists);   
    }

    /**
     * [add description]
     */
    public function add(){
        $data['user_id'] = I('user_id');
        $data['user_sex'] = I('user_sex');
        $data['type'] = I('type');
        $data['title'] = I('title');
        $data['description'] = I('description');
        $data['time'] = I('time');
        $data['province'] = I('province');
        $data['city'] = I('city');
        $data['place'] = I('place');
        $data['longitude'] = I('longitude');
        $data['latitude'] = I('latitude');
        $data['object'] = I('object');
        $data['pay'] = I('pay');
        $data['is_jiesong'] = I('is_jiesong');
        $data['with_confidante'] = I('with_confidante');
        $data['add_time'] = time();
        // 后台设置的是否审核
        $shopinfo_config = tpCache('shop_info');
        $data['status'] = ($shopinfo_config['examine_invite'] == '1' ? 1 : 2);

        if(I('file')){
            $data['image'] = serialize(json_decode(html_entity_decode(I('file')), true));
        }

        // 判断邀约数量
        $num = Db::name('invite')->where('user_id', $data['user_id'])->count();
        if($num >= 5) response_error('', '您已发满5条邀约，请删除不需要的邀约后再发布');

        M('invite')->insert($data);
        response_success('', '操作成功');
    }

    public function detail(){
        $id = I('id');

        $where = array(
            'id' => $id,
            'status' => '2',
        );
        $info = M('invite')->alias('i')
            ->join('users u', 'i.user_id=u.user_id', 'left')
            ->where($where)
            ->field('u.user_id, head_pic, auth_video_status, nickname, u.sex, u.birthday, u.age, i.id invite_id, i.title, i.description, i.time, i.place, image, i.object, i.pay, i.is_jiesong, i.with_confidante')
            ->find();

        if(empty($info)) response_error('', '该内容不存在或已删除');
        if($info['image']) $info['image'] = unserialize($info['image']);
        $info['age'] = getAge($info['birthday']);

        response_success($info);
    }

    // 邀约感兴趣
    public function enroll(){
        $user_id = I('user_id');
        $invite_id = I('invite_id');

        $count = Db::name('invite_enroll')->where(array('user_id'=>$user_id, 'invite_id'=>$invite_id))->count();
        if($count) response_success('', '您已感兴趣此邀约');

        $data = array(
            'user_id' => $user_id, 
            'invite_id'=> $invite_id,
            'add_time' => time(),
        );
        if(Db::name('invite_enroll')->insert($data)){
            // 发消息
            $user = Db::name('users')->where('user_id', $user_id)->field('nickname')->find();
            $invite = Db::name('invite')->where('id', $invite_id)->field('user_id, title')->find();
            // 站内消息
            $message = $user['nickname'].'对您的邀约“'.$invite['title'].'”感兴趣';
            $MessageLogic = new MessageLogic();
            $MessageLogic->add($invite['user_id'], $message);
            // 融云消息
            $RongyunLogic = new RongyunLogic();
            $result = $RongyunLogic->PublishPrivateMessage('1', $invite['user_id'], $message);

            response_success('', '操作成功');
        } else {
            response_error('', '操作失败');
        }
    }

    public function getEnroll(){
        $invite_id = I('invite_id');

        $list = Db::name('invite_enroll')->alias('ie')
            ->join('users u', 'u.user_id=ie.user_id', 'left')
            ->where('ie.invite_id',  $invite_id)
            ->field('u.user_id, u.nickname, u.head_pic, u.sex, birthday, u.age, u.auth_video_status')
            ->select();

        if( !empty($list) && is_array($list)) {
            foreach ($list as &$item) {
                $item['age'] = getAge($item['birthday']);
            }
        }

        response_success($list);
    }

    // 判断用户已发布的邀约数量，最多5条
    public function totalNum(){
        $user_id = I('user_id');

        $num = Db::name('invite')->where('user_id', $user_id)->count();

        response_success(array('num'=>$num));
    }

    public function del(){
        $user_id = I('user_id');
        $invite_id = I('invite_id');

        if(Db::name('invite')->where(array('user_id'=>$user_id,'id'=>$invite_id))->delete()){
            response_success('', '删除成功');
        } else {
            response_error('', '删除失败');
        }
    }
}