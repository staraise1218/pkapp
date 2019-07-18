<?php
return	array(	
	'system'=>array('name'=>'系统','child'=>array(
				array('name' => '设置','child' => array(
						array('name'=>'网站设置','act'=>'index','op'=>'System'),
						array('name'=>'意见反馈','act'=>'index','op'=>'Feedback'),
						// array('name'=>'友情链接','act'=>'linkList','op'=>'Article'),
						array('name'=>'清除缓存','act'=>'cleanCache','op'=>'System'),
				)),
				array('name' => '广告','child' => array(
						array('name'=>'广告列表','act'=>'adList','op'=>'Ad'),
						array('name'=>'广告位置','act'=>'positionList','op'=>'Ad'),
				)),
				array('name' => '文章','child'=>array(
						array('name' => '文章列表', 'act'=>'articleList', 'op'=>'Article'),
						array('name' => '文章分类', 'act'=>'categoryList', 'op'=>'Article'),
				)),
				array('name' => '权限','child'=>array(
					array('name' => '管理员列表', 'act'=>'index', 'op'=>'Admin'),
					array('name' => '角色管理', 'act'=>'role', 'op'=>'Admin'),
					array('name'=>'权限资源列表','act'=>'right_list','op'=>'System'),
					array('name' => '管理员日志', 'act'=>'log', 'op'=>'Admin'),
				)),
			
				/*array('name' => '模板','child'=>array(
						array('name' => '模板设置', 'act'=>'templateList', 'op'=>'Template'),
						//array('name' => '自定义手机模板', 'act'=>'index', 'op'=>'Block'),
						array('name' => '自定义页面', 'act'=>'pageList', 'op'=>'Block'),
						//array('name' => '手机首页', 'act'=>'mobile_index', 'op'=>'Template'),
				)),
				array('name' => '数据','child'=>array(
						array('name' => '数据备份', 'act'=>'index', 'op'=>'Tools'),
						array('name' => '数据还原', 'act'=>'restore', 'op'=>'Tools'),
						//array('name' => 'ecshop数据导入', 'act'=>'ecshop', 'op'=>'Tools'),
						//array('name' => '淘宝csv导入', 'act'=>'taobao', 'op'=>'Tools'),
						//array('name' => 'SQL查询', 'act'=>'log', 'op'=>'Admin'),
				))*/
	)),
		
	/*'shop'=>array('name'=>'商城','child'=>array(
				array('name' => '商品','child' => array(
				    array('name' => '商品列表', 'act'=>'goodsList', 'op'=>'Goods'),
				    array('name' => '淘宝导入', 'act'=>'index', 'op'=>'Import'),
					array('name' => '商品分类', 'act'=>'categoryList', 'op'=>'Goods'),
					array('name' => '库存日志', 'act'=>'stock_list', 'op'=>'Goods'),
					array('name' => '商品模型', 'act'=>'goodsTypeList', 'op'=>'Goods'),
					array('name' => '商品规格', 'act' =>'specList', 'op' => 'Goods'),
					array('name' => '品牌列表', 'act'=>'brandList', 'op'=>'Goods'),
					array('name' => '商品属性', 'act'=>'goodsAttributeList', 'op'=>'Goods'),
					array('name' => '评论列表', 'act'=>'index', 'op'=>'Comment'),
					array('name' => '商品咨询', 'act'=>'ask_list', 'op'=>'Comment'),
			)),
	     
    	    array('name' => '微信','child' => array(
    	        array('name' => '公众号配置', 'act'=>'index', 'op'=>'Wechat'),
    	        array('name' => '微信菜单管理', 'act'=>'menu', 'op'=>'Wechat'),
    	        array('name' => '自动回复', 'act'=>'auto_reply', 'op'=>'Wechat'),
                array('name' => '粉丝列表', 'act'=>'fans_list', 'op'=>'Wechat'),
                array('name' => '模板消息', 'act'=>'template_msg', 'op'=>'Wechat'),
                array('name' => '素材管理', 'act'=>'materials', 'op'=>'Wechat'),
    	    )),

			
			array('name' => '统计','child' => array(
					array('name' => '销售概况', 'act'=>'index', 'op'=>'Report'),
					array('name' => '销售排行', 'act'=>'saleTop', 'op'=>'Report'),
					array('name' => '会员排行', 'act'=>'userTop', 'op'=>'Report'),
					array('name' => '销售明细', 'act'=>'saleList', 'op'=>'Report'),
					array('name' => '会员统计', 'act'=>'user', 'op'=>'Report'),
					array('name' => '运营概览', 'act'=>'finance', 'op'=>'Report'),
					array('name' => '平台支出记录','act'=>'expense_log','op'=>'Report'),
			)),
	)),*/
	'module' => array('name'=>'模块', 'child'=>array(
		array('name' => '会员','child'=>array(
			array('name'=>'会员列表','act'=>'index','op'=>'User'),
			// array('name'=>'视频认证','act'=>'index','op'=>'UserAuthVideo'),
			// array('name'=>'身份认证','act'=>'index','op'=>'IdentityAuth'),
			// array('name'=>'车辆认证','act'=>'index','op'=>'CarAuth'),
			// array('name'=>'VIP购买记录','act'=>'index','op'=>'Vip'),
			// array('name'=>'提现申请','act'=>'index','op'=>'Withdraw'),
			// array('name'=>'会员等级','act'=>'levelList','op'=>'User'),
			// array('name'=>'充值记录','act'=>'recharge','op'=>'User'),
			// array('name'=>'提现申请','act'=>'withdrawals','op'=>'User'),
			// array('name'=>'汇款记录','act'=>'remittance','op'=>'User'),
			//array('name'=>'会员整合','act'=>'integrate','op'=>'User'),
			// array('name'=>'会员签到','act'=>'signList','op'=>'User'),
		)),
		array('name' => '知识库','child'=>array(
			array('name' => '题库列表', 'act'=>'index', 'op'=>'knowledge	'),
			array('name' => '年级分类', 'act'=>'index', 'op'=>'grade'),
			array('name' => '课程分类', 'act'=>'index', 'op'=>'lesson'),
		)),
		/*array('name'=>'金币', 'child'=>array(
			array('name'=>'金币商品','act'=>'index','op'=>'goldcoin'),
			array('name'=>'购买记录','act'=>'order','op'=>'goldcoin'),
			
		)),*/
		/*array('name'=>'礼物', 'child'=>array(
			array('name'=>'礼物列表','act'=>'index','op'=>'gift'),
			
		)),
		array('name'=>'邀约', 'child'=>array(
			array('name'=>'邀约列表','act'=>'index','op'=>'Invite'),
			
		)),*/
		array('name'=>'动态', 'child'=>array(
			array('name'=>'动态列表','act'=>'index','op'=>'Dynamics'),
			
		)),
	)),
);