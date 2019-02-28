let Global = (function () {
    let GlobalHost="http://app.yujianhaoshiguang.cn"; //2018.12.29切换域名
    // let GlobalHost="http://app.yujianhaoshiguang.cn";


    function openLoading(msg = "处理中") {
        if ($(".mLoadingMask").length == 0) {
            $("body").append($('<div class="mLoadingMask"></div>'));
        }
        if ($(".mLoading").length == 0) {
            $("body").append($(`<div class="mLoading">${msg}</div>`));
        }
    }

    function closeLoading(resultMsg="处理中", callback = function () {}, delay = 500) {
        $(".mLoading").html(resultMsg);
        setTimeout(function () {
            $(".mLoadingMask")
                .add($(".mLoading")).remove();
            callback();
        }, delay);
    }
    
    function closeLoadingNow() {
        $(".mLoadingMask").add($(".mLoading")).remove();
    }

    function messageWin(msg,callback) {
        if ($(".msgWrap").length == 0) {
            let $msgDiv = $(
                `
                <div class="msgWrap">
                    <div class="msg">
                        <p class="msgText">${msg}</p>
                        <p class="msgCtrl">
                            <span class="closeMsg">确定</span>
                        </p>
                    </div>
                </div>
            `
            );
            $msgDiv.find(".closeMsg").click(function () {
                $msgDiv.remove();

                if(callback){
                    callback()
                }
            });
            $("body").append($msgDiv);
        }
    }

    function messageConfirWin(msg, callback) {
        if ($(".msgWrap").length == 0) {
            let $msgDiv = $(
                `
                <div class="msgWrap">
                    <div class="msg">
                        <p class="msgText">${msg}</p>
                        <p class="msgCtrl">
                            <span class="closeMsg">取消</span>
                            <span class="gotoCallback">确定</span>
                        </p>
                    </div>
                </div>
            `
            );
            $msgDiv.find(".closeMsg").click(function () {
                $msgDiv.remove();
            });
            $msgDiv.find(".gotoCallback").click(function () {
                callback();
                $msgDiv.remove();
            });
            $("body").append($msgDiv);
        }
    }

    function bindLimitCount(element, textCount, showElement) {
        element.onchange = function () {
            limitCount(this)
        }
        element.onkeydown = function () {
            limitCount(this)
        }
        element.onkeyup = function () {
            limitCount(this)
        }

        function limitCount(el) {
            // el.value = el.value.substring(0, textCount)
            // el.value = el.value.substr(0, textCount);
            let count = el.value.length
            if (showElement) {
                showElement.innerHTML = count
            }
            if (count >= textCount) {
                messageWin(`最多输入${textCount}个字符`);

                el.value = el.value.substr(0, textCount);
                return
            }
        }
    }

    function stampToDate(timestamp) {
        var date = new Date(timestamp.toString().length == 10 ? (timestamp * 1000) : (timestamp * 1)); //时间戳为10位需*1000，时间戳为13位的话不需乘1000
        Y = date.getFullYear() + '-';
        M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
        D = (date.getDate() < 10 ? "0" + date.getDate() : date.getDate()) + ' ';
        h = (date.getHours() < 10 ? "0" + date.getHours() : date.getHours()) + ':';
        m = (date.getMinutes() < 10 ? "0" + date.getMinutes() : date.getMinutes());
        // s = date.getSeconds();
        return Y + M + D + h + m;
    }

    function stampToStr(stamp) { //传来的stamp十位数
        if (!stamp) return '';

        let nowStamp = Math.round((new Date().getTime()) / 1000);
        let howLong = nowStamp - stamp;

        if (howLong < 60) { //1分钟内
            return "1分钟内";
        } else if (howLong < 3600) { //1小时内，显示分钟数
            let minutes = Math.floor(howLong / 60);
            return minutes + "分钟前";
        } else if (howLong < 43200) { //12小时内，显示小时数
            let hours = Math.floor(howLong / 3600);
            return hours + "小时前";
        } else if (howLong >= 43200 && howLong < 86400) { //大于12小时，小于24小时
            let dateStr = stampToDate(stamp);
            console.log(dateStr)
            let minStr = dateStr.substr(dateStr.length - 5);
            return "昨天 " + minStr;
        } else if (howLong > 86400) { //大于24小时，直接显示时间
            return stampToDate(stamp);
        }
    }

    function isIOS() {
        let u = navigator.userAgent;
        let isIOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
        return isIOS;
    }

    function gotoApp(funcStr) { //调原生方法
        // let u = navigator.userAgent;
        // let isAndroid = u.indexOf('Android') > -1 || u.indexOf('Adr') > -1; //android终端


        let isIOS = isIOS();
        if (isIOS) {
            //
        } else { //安卓
            window.Android[funcStr]();
        }
    }

    function eleCantClick(ele) {
        $(ele).addClass("eventsDisabled");
    }

    function eleCanClick(ele) {
        $(ele).removeClass("eventsDisabled");
    }

    //普通的全屏视频预览 动态add
    function fullScreen(src,posterSrc) {
        posterSrc=posterSrc?posterSrc:"";
        let $fullScreen = $(
            `
            <div class="fullScreen">
                <div class="videoHeader">
                    <div class="closeFullScreen"></div>
                </div>

                <div class="fullScreenScroll">
                    <div class="fullScreenWrap">
                        <video id="video1" width="100%" height="100%" src="${src}" poster=${posterSrc} autoplay="autoplay" loop></video>
                    </div>
                </div>
            </div>
            `
        );

        let video1=$fullScreen.find("#video1")[0];

        // 绑定事件
        //点击关闭事件
        $fullScreen.find(".closeFullScreen").click(function () {
            $fullScreen.remove();
        });
        //点击 控制video
        $(video1).click(function (event) {
            event.stopPropagation()
            if (this.paused) {
                this.play()
            } else {
                this.pause()
            }
        });
        // 绑定事件 end

        //append div
        video1.play()
        $("body").append($fullScreen);
    }

    //全屏叽喳小视频
    function fullScreenVideo({
        user_id,
        head_pic,
        src,
        poster,
        dynamic_id
    }){
        if(head_pic){
            head_pic=filterHttpImg(head_pic);
        }else{
            head_pic="/application/mobile/view/static/images/icon/tx.png";
        }

        let divHtml=`
            <div class="fullScreen">
                <div class="videoHeader">
                    <div class="closeFullScreen"></div>
                    
                    <div class="img-box">
                        <img src="${head_pic}">
                    </div>
                </div>

                <div class="fullScreenScroll">
                    <div class="fullScreenWrap">
                        <span class="progressBar"></span>
                        <video id="video1" width="100%" height="100%" src="${src}" poster="${poster}" autoplay="autoplay" loop></video>
                        <div class="videoFooter" style="justify-content: flex-end;">
                            <div class="dynamicDetailBtn" style="font-size:0.16rem;color:#fff;margin-right:30px;">查看动态详情</div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        //<div class="videoOperate"></div>
        //<span class="littleTri"></span>
        
        let $div=$(divHtml);
        let video=$div.find("#video1")[0];

        // 绑定事件
        //关闭事件
        $div.find(".closeFullScreen").click(function(event){
            event.stopPropagation();
            let $fullScreen=$(this).closest(".fullScreen");
            $fullScreen.remove();
        });
        //点击 控制video
        $(video).click(function (event) {
            event.stopPropagation()
            console.log(this.paused)
            if (this.paused) {
                this.play()
            } else {
                this.pause()
            }
        });
        //去动态详情页
        $div.find(".dynamicDetailBtn").click(function(event){
            event.stopPropagation();

            console.log(dongtaiVm)
            dongtaiVm.savePageToSession();
            window.location.href = GlobalHost + '/index.php/mobile/dynamics/detail/id/' + dynamic_id + '.html';
        });
        // 绑定事件 end

        //总是从头开始播放
        video.currentTime = 0; 
        //进度条
        setInterval(function () {
            if (video.currentTime >= video.duration) {
                return false
            }
            // console.log(video.currentTime, video.duration)
            $div.find(".progressBar")[0].style.width = (video.currentTime / video.duration) * 100 + "%"
        }, 50);

        //append div
        video.play()
        $("body").append($div);
    }

    function fullScreenImg(src) {
        //获取 宽高
        let img = new Image();
        img.src = src;
        let width, height;
        if (img.complete) {
            width = img.width;
            height = img.height;
        } else {
            img.onload = function () {
                width = img.width;
                height = img.height;
            }
        }
        console.log(width, height)
        // //根据宽高设定background-size
        // let bgSize;
        // if(width>height){
        //     bgSize="100% auto";
        // }else{
        //     // bgSize="auto 100%";
        //     bgSize="100% auto";
        // }
        //生成div
        let $div=$(`
            <div style="position:fixed;top:0;bottom:0;left:0;right:0;z-index:6;background:#000 url(${src}) center center no-repeat;background-size:100% auto;">
            </div>
        `);
        //关闭事件
        $div.click(function(){
            $(this).remove();
        });

        $("body").append($div);
    }

    //过滤http头像img
    function filterHttpImg(src){
        let srcTemp;
        if(src.indexOf("http:")>-1||src.indexOf("https:")>-1){
            srcTemp=src;
        }else{
            srcTemp=GlobalHost+src;
        }
        return srcTemp;
    }

    //获取img的宽高？？？？？？？？？？？？？？
    function getImgWidth(src,callback){
        let img = new Image();
        img.onload=function(){
            let obj={};
            obj.width=this.width;
            obj.height=this.height;
            // console.log(obj.width,obj.height)
            callback(obj);
        }
        img.src=src;
    }

    //更新本地的userinfo
    function updateLocalUserinfo(key,value){
        if (localStorage.getItem("mUserInfo") && localStorage.getItem("mUserInfo") !== null &&localStorage.getItem("mUserInfo") !== "null") {
            let mUserInfo = JSON.parse(localStorage.getItem("mUserInfo"))
            console.log(mUserInfo)
            
            //更新key value
            mUserInfo[key]=value;

            localStorage["mUserInfo"] = JSON.stringify(mUserInfo)
            localStorage.setItem("mUserInfo", JSON.stringify(mUserInfo))
        }
    }

    //获取头像的认证视频url
    function getAuthVideoUrl(user_id,callback){
        let postData = {
            user_id: user_id
        }
        let url = GlobalHost + "/index.php/Api/user/getAuthVideoUrl";
        console.log(postData)
        $.ajax({
            type: "POST",
            url: url,
            data: postData,
            dataType: "json",
            success: function (res) {
                console.log(res)
                if(res.code==200&&res.data){
                    callback(res.data.video_url);
                }else{
                    console.log("请求失败")
                }
            }
        });
    }

    //检查能否发邀约
    function checkInvite(user_id,callback){
        let postData = {
            user_id: user_id
        };
        console.log(postData)
        let url = GlobalHost + "/index.php/Api/invite/totalNum";
        $.ajax({
            type: "POST",
            url: url,
            data: postData,
            dataType: "json",
            success: function (result) {
                console.log(result)
                callback(result.data.num);
            }
        }); 
    }

    function resizeHeadpic(imgEle){
        // console.log(imgEle.width,imgEle.height)
        if(imgEle.width>imgEle.height){
            imgEle.style.width="auto"
            imgEle.style.height="100%"
        }else{
            imgEle.style.width="100%"
            imgEle.style.height="auto"
        }
    }
    //获取自己的用户信息
    function getMyInfo(){
        if(localStorage.getItem("mUserInfo")&&localStorage.getItem("mUserInfo")!==null&&localStorage.getItem("mUserInfo")!=="null"){
            let mUserInfo=JSON.parse(localStorage.getItem("mUserInfo"))
            return mUserInfo
        }else{
            alert("请求失败")
            return false
        }
    }
    //Global暴露的接口------------------------
    return {
        //值
        host: GlobalHost,
        //方法
        openLoading, //msg
        closeLoading, //msg:完成瞬间显示的文字;callback;delay:人工设定延迟
        closeLoadingNow, //单纯的关闭loding，没有参数
        messageWin, //msg,callback可选参数回调
        messageConfirWin, //msg;callback：点击确定的回调
        bindLimitCount, //element:textarea元素;textCount:限制的字数;showElement:实时显示字数的元素  注:没有表情插件的
        stampToDate, //stamp  注:10位或13位都可以
        stampToStr, //stamp:10位时间戳
        isIOS, //判断是不是ios系统
        gotoApp,
        eleCantClick, //让元素无法点击 ele:元素
        eleCanClick, //ele:元素
        fullScreen, // 简单的预览全屏视频 ele:video元素
        fullScreenImg, //全屏显示图片 src
        filterHttpImg, //过滤http头像img  src
        getImgWidth, //获取图片原始宽高
        updateLocalUserinfo, //更新本地的userinfo key,value
        getAuthVideoUrl, //获取认证视频url; user_id callback(url)
        resizeHeadpic, //设置头像style
        getMyInfo, //获取自己mUserInfo

        //单独功能
        fullScreenVideo, //全屏叽喳视频（有去动态详情的按钮）
        checkInvite
    }
})();

// ------------------------------------------------------------------------------------------------------------------------------------

//上传文件的回调 args 文件路径
function uploadOne(args,thumb) { // 路径/plulic/../..
    // alert("getJavaFiles")
    // alert(args)
    // alert(thumb)
    // $("body").append($('<div>'+args+'</div>')) //测试用

    // args="/public/upload/files/20180820/a706d74e6e9e4bc8c1d5e52b984047ab.jpg"; //测试用

    let src = Global.host + args; //video或img src
    // $("body").append($('<div>'+src+'</div>')) //测试用

    // $("body").append($('<img src="'+src+'">')) //测试用
    // $("body").append($('<div>'+aaa+'</div>')) //测试用

    let srcThumb=""; 
    if(thumb&&thumb!==""){
        srcThumb=Global.host + thumb; //thumb的src
    }
    let url = args.toLowerCase();

    //是图片
    if (url.indexOf(".jpg") > -1 || url.indexOf(".jpeg") > -1 || url.indexOf(".gif") > -1 || url.indexOf(".png") > -1 || url.indexOf(".bmp") > -1 || url.indexOf(".tga") > -1 || url.indexOf(".svg") > -1) {
        let $liTemp = $(`
            <li class="edit-pic-item">
                <img class="showPic" data-index=${$(".edit-pic-item").length} src="${src}">
                <a href="javascript:void(0)" class="edit-closePic"></a>
            </li>
        `);

        //取消图片
        $liTemp.find("a.edit-closePic").click(function (event) {
            event.stopPropagation();
            let self = this;
            Global.messageConfirWin("尚未发布，确认删除？", function () {
                $(self).closest('.edit-pic-item').remove();
            });
        })
        // alert("增加图片")
        $(".showPicUl").prepend($liTemp);
    }
    //是视频
    else if (url.indexOf(".mp4") > -1 || url.indexOf(".rm") > -1 || url.indexOf(".rmvb") > -1 || url.indexOf(".avi") > -1 || url.indexOf(".wmv") > -1 || url.indexOf(".mpg") > -1 || url.indexOf(".mpeg") > -1 || url.indexOf(".flv") > -1 || url.indexOf(".3gp") > -1 || url.indexOf(".mov") > -1) {
        let $liTemp = $(`
            <li class="edit-pic-item">
                <img class="showPicVideo" src="${srcThumb}" data-src=${src} width="100%" height="100%"></img>
                <a href="javascript:void(0)" class="edit-closePic"></a>
            </li>
        `);

        //取消视频
        $liTemp.find("a.edit-closePic").click(function (event) {
            event.stopPropagation();
            let self = this;
            Global.messageConfirWin("尚未发布，确认删除？", function () {
                $(self).closest('.edit-pic-item').remove();
            });
        })
        // alert("增加视频")
        $(".showPicUl").prepend($liTemp);
    }
}



//打开新的webview
//gotoNewWebView

//原生上传多张图片
// window.Android.androidMulUpload(1,"uploadPhotos") //参数1：1图片 2视频  参数2：调用的js方法的名称

//原生上传单张图片
// window.Android.androidOneUpload(1,"uploadPhotos") //参数1：1图片 2视频  参数2：调用的js方法的名称