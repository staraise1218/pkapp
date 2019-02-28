<?php

namespace app\api\controller;
use think\Db;
use app\api\logic\SmsLogic;

class Region extends Base {

	public function __construct(){
		// 设置所有方法的默认请求方式
		$this->method = 'POST';

		parent::__construct();
	}


    public function getJson(){
    	$regions = M('region')->field('name, code, parentCode')->select();

    	$data = array();
  		foreach ($regions as $region) {
  			$data[$region['code']] = $region;
  		}

  		$data = $this->_tree($data);
  		response_success($data);
    }
    


   	/**
   	 * 生成目录树结构
   	 */
	  private function _tree($data){

   		$tree = array();
   		foreach ($data as $item) {
               if(isset($data[$item['parentCode']])){
                  $data[$item['parentCode']]['sub'][] = &$data[$item['code']];
               } else {
                  $tree[] = &$data[$item['code']];
               }
   		}

   		return $tree;
   	}
}