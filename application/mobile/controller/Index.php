<?php

namespace app\mobile\controller;

use Think\Db;
use app\common\logic\wechat\WechatUtil;

class Index extends Base {

    public function index(){
        
        $this->assign('flash_sale_list',$flash_sale_list);
        $this->assign('start_time',$start_time);
        $this->assign('end_time',$end_time);
        $this->assign('favourite_goods',$favourite_goods);
        return $this->fetch();
    }

    public function index2(){
        $id=I('post.id');
        if($id){
            $arr=M('mobile_template')->where('id='.$id)->field('template_html,block_info')->find();
        }else{
            $arr=M('mobile_template')->order('id DESC')->limit(1)->field('template_html,block_info')->find();
        }

        $html=htmlspecialchars_decode($arr['template_html']);
        $this->assign('html',$html);
        $this->assign('info',$arr['block_info']);
        return $this->fetch();
    }

    //商品列表板块参数设置
    public function goods_list_block(){
        $data=I('post.');
        $count=I('post.num');
        //dump($data);exit();

        if($data['ids']){
            $ids = substr($data['ids'],0,strlen($data['ids'])-1);   //ids是前台传递过来的商品2级分类
        }
        
        if($ids){
            $ids="(".$ids.")";
            //此处前台传递的是2级分类id 需要获取它的3级分类
            $cat_ids=Db::name('goods_category')->where("parent_id in".$ids." and is_show=1")->getField('id',true);  
        }
        if($cat_ids){
            $str="(".implode(",",$cat_ids).")";
        }
        
        $where='is_on_sale=1';
        if($cat_ids){
            $where.=" and cat_id in".$str;
        }
        if($data['label']){
            $where.=" and ".$data['label']."=1";
        }
        if($data['min_price']){
            $where.=" and shop_price>".$data['min_price'];
        }
        if($data['max_price']){
            $where.=" and shop_price<".$data['max_price'];
        }
        if($data['goods']){
            $goods_id = substr($data['goods'],0,strlen($data['goods'])-1);
            $goods_id = "(".$goods_id.")";
            $where.=" and goods_id in".$goods_id;
        }


        switch ($data['order']) {
            case '0':
                $order_str="sales_sum DESC";
                break;
            
            case '1':
                $order_str="sales_sum ASC";
                break;

            case '2':
                $order_str="shop_price DESC";
                break;

            case '3':
                $order_str="shop_price ASC";
                break;

            case '4':
                $order_str="last_update DESC";
                break;

            case '5':
                $order_str="last_update ASC";
                break;
        }

        $goodsList = M('Goods')->where($where)->order($order_str)->limit(0,$count)->select();

        $html='';
        foreach ($goodsList as $k => $v) {
            $html.='<li>';
            $html.='<a class="tpdm-goods-pic" href="javascript:;"><img src="'.$v[original_img].'" alt="" /></a>';
            $html.='<a href="/Mobile/Goods/goodsInfo/id/'.$vo[goods_id].'".htm" class="tpdm-goods-name">'.$v[goods_name].'</a>';
            $html.='<div class="tpdm-goods-des">';
            $html.='<div class="tpdm-goods-price">￥'.$v[shop_price].'</div>'; 
            $html.='<a class="tpdm-goods-like" href="javascript:;">看相似</a>'; 
            $html.='</div>';
            $html.='</li>';
        }
        $this->ajaxReturn(['status' => 1, 'msg' => '成功', 'result' =>$html]);
    }


    //自定义页面获取秒杀商品数据
    public function get_flash(){
        $now_time = time();  //当前时间
        if(is_int($now_time/7200)){      //双整点时间，如：10:00, 12:00
            $start_time = $now_time;
        }else{
            $start_time = floor($now_time/7200)*7200; //取得前一个双整点时间
        }
        $end_time = $start_time+7200;   //结束时间
        $flash_sale_list = M('goods')->alias('g')
            ->field('g.goods_id,g.original_img,g.shop_price,f.price,s.item_id')
            ->join('flash_sale f','g.goods_id = f.goods_id','LEFT')
            ->join('__SPEC_GOODS_PRICE__ s','s.prom_id = f.id AND g.goods_id = s.goods_id','LEFT')
            ->where("start_time = $start_time and end_time = $end_time")
            ->limit(4)->select();
        $str='';
        if($flash_sale_list){
            foreach ($flash_sale_list as $k => $v) {
                $str.='<a href="'.U('Mobile/Activity/flash_sale_list').'">';
                $str.='<img src="'.$v['original_img'].'" alt="" />';
                $str.='<span>￥'.$v['price'].'</span>';
                $str.='<i>￥'.$v['shop_price'].'</i></a>';
            }
        }
        $time=date('H',$start_time);
        $this->ajaxReturn(['status' => 1, 'msg' => '成功','html' => $str, 'start_time'=>$time, 'end_time'=>$end_time]);
    }

    /**
     * 分类列表显示
     */
    public function categoryList(){
        return $this->fetch();
    }

    /**
     * 模板列表
     */
    public function mobanlist(){
        $arr = glob("D:/wamp/www/svn_tpshop/mobile--html/*.html");
        foreach($arr as $key => $val)
        {
            $html = end(explode('/', $val));
            echo "<a href='http://www.php.com/svn_tpshop/mobile--html/{$html}' target='_blank'>{$html}</a> <br/>";            
        }        
    }
    
    /**
     * 商品列表页
     */
    public function goodsList(){
        $id = I('get.id/d',0); // 当前分类id
        $lists = getCatGrandson($id);
        $this->assign('lists',$lists);
        return $this->fetch();
    }
    
    public function ajaxGetMore(){
    	$p = I('p/d',1);
        $where = [
            'is_recommend' => 1,
            'exchange_integral'=>0,  //积分商品不显示
            'is_on_sale' => 1,
            'virtual_indate' => ['exp', ' = 0 OR virtual_indate > ' . time()]
        ];
    	$favourite_goods = Db::name('goods')->where($where)->order('goods_id DESC')->page($p,C('PAGESIZE'))->cache(true,TPSHOP_CACHE_TIME)->select();//首页推荐商品
    	$this->assign('favourite_goods',$favourite_goods);
    	return $this->fetch();
    }
    
    //微信Jssdk 操作类 用分享朋友圈 JS
    public function ajaxGetWxConfig()
    {
        $askUrl = input('askUrl');//分享URL
        $askUrl = urldecode($askUrl);

        $wechat = new WechatUtil;
        $signPackage = $wechat->getSignPackage($askUrl);
        if (!$signPackage) {
            exit($wechat->getError());
        }

        $this->ajaxReturn($signPackage);
    }
       
}