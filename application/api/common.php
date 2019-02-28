
<?php


function generateOrderSn(){
	return date('YmdHis').mt_rand(1000,9999);
}


// 创建uuid
function generateUuid(){
    $uuid = mt_rand(10000000, 99999999);

    if(M('users')->where('uuid', $uuid)->count()){
        $this->generateUuid();
    }
    return $uuid;
}