
/**
 * API 公共
 */
var GlobalHost = 'http://pkapp.staraise.com.cn'
// var GlobalHost = 'http://pkapp.localhost.com'


/**
 * 正则
 */
// 姓名
let userNameRgx = /^[a-zA-Z0-9_\u4e00-\u9fa5]+$/;
// 手机号
let phoneRgx = /^((13[0-9])|(14[0-9])|(15[0-9])|(17[0-9])|(18[0-9]))\d{8}$/;
// 身份证
let shenfenCardRgx = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;

/**
 * 区分Android ios
 */
var u = navigator.userAgent, 
app = navigator.appVersion;
var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1; //g
var isIOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
if (isAndroid) {
    console.log("安卓机！")
}
if (isIOS) {
    console.log("苹果机！")
}



/** 
 * 获取指定的URL参数值 
 * 参数：paramName URL参数 
 * 调用方法:getParam("name") 
 */
function getParam(paramName) {
    paramValue = "", isFound = !1;
    if (this.location.search.indexOf("?") == 0 && this.location.search.indexOf("=") > 1) {
        arrSource = this.location.search.substring(1, this.location.search.length).split("&"), i = 0;
        while (i < arrSource.length && !isFound) arrSource[i].indexOf("=") > 0 && arrSource[i].split("=")[0].toLowerCase() == paramName.toLowerCase() && (paramValue = arrSource[i].split("=")[1], isFound = !0), i++
    }
    return paramValue == "" && (paramValue = null), paramValue
}


function getRequest(key=null) {   
   var url = window.location.search; //获取url中"?"符后的字串   
   var theRequest = new Object();   
   if (url.indexOf("?") != -1) {   
      var str = url.substr(1);   
      strs = str.split("&");   
      for(var i = 0; i < strs.length; i ++) {   
          //就是这句的问题
         theRequest[strs[i].split("=")[0]]=decodeURI(strs[i].split("=")[1]); 
         //之前用了unescape()
         //才会出现乱码  
      }   
   }   
   return key ? theRequest[key] : theRequest;   
}




/**
 * 回退 1
 */
// $('.back').on('click', function () {
//     window.history.back(-1);
// })
if (isAndroid) {
    $('.back').on('click', function () {
        window.android.goBack();
    //     console.log(window.history.__proto__.constructor)
    //     if(window.android.goBack() != 'undefined') {
    //     } else {
    //         // window.history.back(-1);
    //     }
    })
} else {
    $('.back').on('click', function () {
        window.history.back(-1);
    })
}

/**
 * 时间戳转时间
 */
function formatDate(date) {
    if (date.length == 10) {
        date = date * 1000
    }
    date = Number(date)
    date = new Date(date);
    var y = date.getFullYear();
    var m = date.getMonth() + 1;
    m = m < 10 ? '0' + m : m;
    var d = date.getDate();
    d = d < 10 ? ('0' + d) : d;
    return y + '-' + m + '-' + d;
}
function formatDateCom(time) {
    time*= 1000
    time = Number(time)
    var date = new Date(time);
    Y = date.getFullYear() + '-';
    M = (date.getMonth()+1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1) + '-';
    D = date.getDate() + ' ';
    h = date.getHours() + ':';
    m = date.getMinutes() + ':';
    s = date.getSeconds(); 
    console.log(Y+M+D+h+m+s);
    return Y+M+D+h+m+s
}


