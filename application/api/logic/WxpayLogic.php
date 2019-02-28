<?php
namespace app\api\logic;

use think\Db;

class WxpayLogic{

    
    public $notify_url = ''; //完成付款后，微信的异步通知，不能带参数
    public $submit_charset = 'UTF-8';
    public $signtype = 'sha1';

    public $appid = 'wx14916e24be20671a';                 //appid
    public $key   = '7YKbxIFif6opt2XyggRCCETfIkLELVsc';   //私钥  
    public $mch_id = '1512749221';                        //微信支付商户号

    public function __construct(){
 
    
    }

    /**
     * 获取预支付交易单号
     */
    public function getPrepayId($order_sn, $total_fee, $body){

        $params = array(
            'appid' => $this->appid,
            'mch_id' => $this->mch_id,
            'nonce_str' => $this->createNonceStr(),
            'sign_type' => 'MD5',
            'body' => $body,
            'out_trade_no' => $order_sn,
            'total_fee' => $total_fee*100,
            'spbill_create_ip' => $this->getclientip(),
            'notify_url' => $this->notify_url,
            'trade_type' => 'APP',
        );
        ksort($params);
        // 签名
        $sign = $this->getSign($params, $this->key);
        $params['sign'] = $sign;

        // array -> xml
        $xml = $this->arrayToXml($params);


        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $result = $this->postXmlCurl($xml, $url);
        $result = $this->xmlToArray($result);

        if($result['return_code'] = 'SUCCESS' && $result['result_code'] == 'SUCCESS'){
            $return_params = array(
                'appid' => $this->appid,
                'partnerid' => $this->mch_id,
                'noncestr' => $this->createNonceStr(),
                'prepayid' => $result['prepay_id'],
                'package' => 'Sign=WXPay',
                'timestamp' => time(),
            );
            ksort($return_params);
            // 签名
            $return_sign = $this->getSign($return_params, $this->key);
            $return_params['sign'] = $return_sign;
            $return_params['_package'] = $return_params['package']; // 客户端关键字字段转换
            unset($return_params['package']);
            return $return_params;
        }else {
            return false;
        }
    }

    /**
     * 支付后返回后处理的事件的动作
     * @params array - 所有返回的参数，包括POST和GET
     * @return null
     */
    function callback(){
        $postData = array();
        $postStr = file_get_contents("php://input");

        file_put_contents('runtime/log/request.log', $postStr, FILE_APPEND);
        $postArray = $this->xmlToArray($postStr);
        $nodify_data = array_merge($_GET,$postArray);

        $result = $nodify_data;

        return $result;
    }

    /**
     * 支付成功回打支付成功信息给支付网关
     */
    function ret_result($paymentId,$ret){
        $ret = array('return_code'=>'SUCCESS','return_msg'=>'');
        $ret = $this->arrayToXml($ret);
        echo $ret;exit;
    }

//↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓公共函数部分↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓

    /**
     * 生成随机数
     * */
    function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
          $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }    


    /**
     *  作用：将xml转为array
     */
    public function xmlToArray($xml)
    {
        //将XML转为array
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

        return $array_data;
    }

    /**
     *  作用：array转xml
     */
    function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
             // if (is_numeric($val)){
             //    $xml.="<".$key.">".$val."</".$key.">";
             // } else {
             //    $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
             // }
             $xml.="<".$key.">".$val."</".$key.">";
        }
        $xml.="</xml>";
        return $xml;
    }

    /**
     *  作用：以post方式提交xml到对应的接口url
     */
    public function postXmlCurl($xmlData, $url)
    {

        $ch = curl_init();  // 初始一个curl会话
        curl_setopt($ch, CURLOPT_URL, $url);    // 设置url
        curl_setopt($ch, CURLOPT_POST, 1);  // post 请求
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type:text/xml; charset=utf-8"));    // 一定要定义content-type为xml，要不然默认是text/html！
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);//post提交的数据包
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3); // PHP脚本在成功连接服务器前等待多久，单位秒
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $result = curl_exec($ch);   // 抓取URL并把它传递给浏览器
        curl_close($ch);
        return $result;
    }

    /**
     *  作用：设置标配的请求参数，生成签名，生成接口参数xml
     */
    function createXml($parameters)
    {
        $this->parameters["appid"] = WxPayConf_pub::APPID;//公众账号ID
        $this->parameters["mch_id"] = WxPayConf_pub::MCHID;//商户号
        $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
        $this->parameters["sign"] = $this->getSign($this->parameters);//签名
        return  $this->arrayToXml($this->parameters);
    }

    /**
     *  作用：post请求xml
     */
    function postXml()
    {
        $xml = $this->createXml();
        $this->response = $this->postXmlCurl($xml,$this->url,$this->curl_timeout);
        return $this->response;
    }

    /**
     *  作用：格式化参数，签名过程需要使用
     */
    function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v)
        {
            if($urlencode)
            {
               $v = urlencode($v);
            }
            //$buff .= strtolower($k) . "=" . $v . "&";
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar;
        if (strlen($buff) > 0)
        {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }

    /**
     *  作用：生成签名
     */
    public function getSign($params, $key)
    {
        //签名步骤一：按字典序排序参数，拼接参数
        ksort($params);
        $string = $this->formatBizQueryParaMap($params, false);

        //签名步骤二：在string后加入KEY
        $string = $string."&key=".$key;

        //签名步骤三：MD5加密
        $string = md5($string);

        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);

        return $result;
    }

//↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑公共函数部分↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

    /**
    * 获取客户端IP地址
    * @param integer $type
    * @return mixed
    */
    public function getclientip(){
        static $realip = NULL;
          
        if($realip !== NULL){
            return $realip;
        }
        if(isset($_SERVER)){
            if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){ //但如果客户端是使用代理服务器来访问，那取到的就是代理服务器的 IP 地址，而不是真正的客户端 IP 地址。要想透过代理服务器取得客户端的真实 IP 地址，就要使用 $_SERVER["HTTP_X_FORWARDED_FOR"] 来读取。
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
                foreach ($arr AS $ip){
                    $ip = trim($ip);
                    if ($ip != 'unknown'){
                        $realip = $ip;
                        break;
                    }
                }
            }elseif(isset($_SERVER['HTTP_CLIENT_IP'])){//HTTP_CLIENT_IP 是代理服务器发送的HTTP头。如果是"超级匿名代理"，则返回none值。同样，REMOTE_ADDR也会被替换为这个代理服务器的IP。
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            }else{
                if (isset($_SERVER['REMOTE_ADDR'])){ //正在浏览当前页面用户的 IP 地址
                    $realip = $_SERVER['REMOTE_ADDR'];
                }else{
                    $realip = '0.0.0.0';
                }
            }
        }else{
            //getenv环境变量的值
            if (getenv('HTTP_X_FORWARDED_FOR')){//但如果客户端是使用代理服务器来访问，那取到的就是代理服务器的 IP 地址，而不是真正的客户端 IP 地址。要想透过代理服务器取得客户端的真实 IP 地址
                $realip = getenv('HTTP_X_FORWARDED_FOR');
            }elseif (getenv('HTTP_CLIENT_IP')){ //获取客户端IP
                $realip = getenv('HTTP_CLIENT_IP');
            }else{
                $realip = getenv('REMOTE_ADDR');  //正在浏览当前页面用户的 IP 地址
            }
        }
        preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
        $realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
        return $realip;
   }
}


?>