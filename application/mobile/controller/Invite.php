<?php

namespace app\mobile\controller;

use think\Db;
use think\Config;

 
class Invite extends Base {
	public function index(){

	    return $this->fetch();
    }

    public function add(){
        
        return $this->fetch();
    }

    public function detail(){

    	return $this->fetch();
    }
}