<?php

namespace app\mobile\controller;

use think\Controller;

class Pk extends Base{

    public function index(){

    	return $this->fetch('index');
    }
}