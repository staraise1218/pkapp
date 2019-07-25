// 保存全局变量
let $action = '',
    $room_id = '',
    $user_id = '',
    $to_user_id = '',
    $data = {},
    $client_id = '',
    $knowledgeList = [],
    $user2_answer = '',
    $user2_isright = '',
    $touserinfo = {}
$comeMe = false //告诉我进来了

let patentHeight = $(".jindu").height(),
    userHeight_1 = 0,
    userHeight_2 = 0,
    $quset_index = 0,
    timeText = 0,
    $_index = 0, // 渲
    $is_choose_2 = false,
    $can_choose = false,
    $score_1 = 0,
    $score_2 = 0,
    $winer_id = '',
    $sendResult_data = {},
    $result = '',
    $time_number = 10,
    $time_text = 0,
    $answer_end = false,
    $questionsWrapper = '',
    $is_online = "对方不在线",
    $searchNickname = ''; // 搜索内容

var endAction = ''; // [1 倒计时结束]

console.log($userinfo)
let user1OBJ = {
    pic: 'http://pkapp.staraise.com.cn/' +  $userinfo.head_pic,
    name: $userinfo.nickname,
    user_id: $userinfo.user_id
}
let user2OBJ = {
    pic: '',
    name: '',
    user_id: ''
}

let selfRoomID = '';

// 保存用户登陆信息
$user_id = $userinfo.user_id;

// 建立websocket链接
var ws = new WebSocket("ws://120.92.10.2:2345");
ws.onopen = function () {
    ws.send("youaremybaby")
    console.log("socket open  链接建立")
}

// 初始化页面
$(".list-wrapper").get(0).style.display = 'block'
$("#load-wrapper").get(0).style.display = 'none';
$("#pk-display").get(0).style.display = 'none';

// 请求绑定 uid 接口
ws.onmessage = function (event) {
    setInterval(function () {
        ws.send("heart")
    }, 3000)
    console.log("socket onmessage 接受信息")
    $data = JSON.parse(event.data);
    // $data.head_pic = $data.head_pic.replace('http://tounao.staraise.com.cn','http://pkapp.staraise.com.cn')
    console.log(event)
    console.log($data)
    if($data.room_id) {
        selfRoomID = $data.room_id;
    }
    // var reg = new RegExp("(http://pkapp.staraise.com.cn)");
    // $data.head_pic = $data.head_pic.replace(reg,'http://pkapp.staraise.com.cn')
    $client_id = $data.client_id;
    var postData = {
        user_id: $user_id,
        client_id: $client_id
    }

    // 绑定Uid
    if ($data.action == 'client_id') {
        console.log("action **************** client_id")
        $.ajax({
            type: 'POST',
            url: "http://pkapp.staraise.com.cn/Api/common/bindUid",
            data: postData,
            dataType: "json",
            success: function (res) {
                console.log(res, "socket ajax 绑定成功")
            },
            error: function (e) {
                console.log("socket ajax 绑定失败");
            }
        })
    }

    // 接受者接到通知
    if ($data.action == 'invite') {
        console.log("action **************** invite")
        console.log($data)
        user2OBJ.pic = $data.head_pic;
        user2OBJ.name = $data.nickname;
        user2OBJ.user_id = $data.user_id;
        user2OBJ.room_id = $data.room_id;
        $room_id = $data.room_id;
        $(".tanchutn-wrapper").css("display", "block")
        var $fheihgt = $(".list-wrapper").height();
        // $(".tanchutn-wrapper").css("height", $fheihgt)
        $('.tanchuang .poster img').prop('src', $data.head_pic)
        $('.tanchuang .user2-name').text($data.nickname || '神秘人')
        document.addEventListener("touchmove", function (e) {
            if ($(".tanchutn-wrapper").css("display") == 'block') {
                $("html,body").addClass("overHiden")
            } else {
                $("html,body").removeClass("overHiden")
            }
        }, false)
    }

    if ($data.action == 'intoRoom') {
        console.log("action **************** intoroom")
        $comeMe = true
    }

    // 接受者开始游戏
    if ($data.action == 'start') {
        console.log("action **************** start");
        $("#load-wrapper").css("display", "none");
        $(".list-wrapper").css("display", "none");
        $("#pk-display").css("display", "block");
        $(".pk-end-wrapper").css("display", "none");
        gameTimerStart();
    }

    // 收到对方选择
    if ($data.action == 'choose') {
        console.log("action **************** choose")
        $is_choose_2 = true;
        $user2_answer = $data.answer;
        $user2_isright = $data.is_right;

        console.log("接收选择答案")
        console.log("$is_choose_2", $is_choose_2, "$user2_answer", $user2_answer, "$user2_isright", $user2_isright);

        if ($user2_isright == 1) {
            $.each($(".choose-wrapper .choose-btn"), function (index, item) {
                if ($(item).attr("data") == $user2_answer) {
                    console.log("user2正确")
                    // $(item).addClass("user2-dui");
                    setTimeout(function () {
                        $(".user2_jindu-con").animate({}, function () {
                            userHeight_2 += patentHeight / 5;
                            $(".user2_jindu-con").animate({
                                height: userHeight_2
                            }, "fast")
                            $score_2 = Number($("#user2-number").get(0).innerText) + 100;
                            $("#user2-number").text($score_2);
                        })
                    }, 500)
                }
            })
        } else if ($user2_isright == 2) {
            $.each($(".choose-wrapper .choose-btn"), function (index, item) {
                if ($(item).attr("data") == $user2_answer) {
                    console.log("user2错误")
                    // $(item).addClass("user2-cuo");
                }
            })
        }
    }

    // 结束 --- 胜利者
    if ($data.action == 'sendResult') {
        console.log("action **************** sendResult")
        console.log($sendResult_data)
    }

    // 接受邀请 
    $(".agreen").click(function () {
        $("body").removeClass("pkb-bg");
        $("body").addClass("pk-bg");
        console.log($room_id, $user_id)
        console.log("接受者 agreen")
        $("#load-wrapper").css("display", "block");
        $(".list-wrapper").css("display", "none");
        $("#pk-display").css("display", "none");
        $(".pk-end-wrapper").css("display", "none");
        $.ajax({
            type: 'POST',
            url: "http://pkapp.staraise.com.cn/Api/pk/intoroom",
            data: {
                room_id: $room_id,
                to_user_id: $user_id
            },
            dataType: "json",
            success: function (data) {
                console.log(data)
                
                console.log("socket ajax 绑定成功")
                $data = data;
                $knowledgeList = data.data.knowledgeList
                $to_user_id = data.data.userinfo.user_id

                $userinfo = data.data.touserinfo;
                $touserinfo = data.data.userinfo;
                console.log("接受者 agreen*******************************************")
                createUser();
            },
            error: function (e) {
                console.log("接受者 agreen error");
            }
        })
    })
}

ws.onerror = function () {
    console.log("socket---error")
}

ws.onclose = function (data) {
    console.log("断开连接啦啦啦啦啦啦")
    console.log(data)
}

// 点击开始--进入PK
$(".begin").click(function () {
    // createUser();
    if (!$comeMe) {
        alert("对方还没准备好")
        return
    }
    $("#load-wrapper").css("display", "none");
    $(".list-wrapper").css("display", "none");
    $("#pk-display").css("display", "block");
    $(".pk-end-wrapper").css("display", "none");

    console.log("发起者---开始游戏", $to_user_id)
    $.ajax({
        type: 'POST',
        url: "http://pkapp.staraise.com.cn/Api/pk/start",
        data: {
            to_user_id: $to_user_id
        }, // 发起者id
        dataType: "json",
        success: function (data) {
            console.log("通知发起者开始答题 ---- success");
            console.log(data);
        },
        error: function () {
            console.log("通知发起者开始答题 ---- error")
        }
    })
    gameTimerStart();
})

$('.cearchName').change(function () {
    $searchNickname = $(this).val()
})

function search(searchNickname) {
    $.ajax({
        type: 'POST',
        url: "http://pkapp.staraise.com.cn/Api/pk/pklist",
        data: {
            page: 1,
            user_id: $user_id,
            searchNickname: searchNickname
        },
        success: function (res) {
            console.log(res)
            var str = "";
            res.data.list.forEach(function (item, index) {
                if (item.user_id == $user_id) {
                    // $(".item").eq(index).css("background", "linear-gradient( #FDEB71, #FDD819)")
                    $(".item").eq(index).find($(".pk")).css("display", "none");
                    str += `<li class="item" style="background: linear-gradient( #FDEB71, #FDD819)">
                                <div class="left">
                                    <div class="number">${index + 1}</div>
                                    <div class="poster">
                                        <img src="http://pkapp.staraise.com.cn${item.head_pic}" alt="">
                                    </div>
                                </div>
                                <div class="right">
                                    <div>
                                        <div class="user-name">${item.nickname}</div>
                                    </div>
                                    <div>${item.school || "未知学校"}</div>
                                </div>
                                <div class="pk" data-id="${item.user_id}" style="display:none">PK</div>
                            </li>`
                } else {
                    str += `<li class="item">
                        <div class="left">
                            <div class="number">${index + 1}</div>
                            <div class="poster">
                                <img src="http://pkapp.staraise.com.cn${item.head_pic}" alt="">
                            </div>
                        </div>
                        <div class="right">
                            <div>
                                <div class="user-name">${item.nickname}</div>
                            </div>
                            <div>${item.school || "未知学校"}</div>
                        </div>
                        <div class="pk" data-id="${item.user_id}">PK</div>
                    </li>`
                }
            })
            $("#userList").html(str)
        }
    })
}
$('.search-btn').on('click', function () {
    search($searchNickname)
})
// 渲染列表
$(document).ready(function () {
    $(".dangqian img").attr('src', "http://pkapp.staraise.com.cn" + $userinfo.head_pic)
    $.ajax({
        type: 'POST',
        url: "http://pkapp.staraise.com.cn/Api/pk/pklist",
        data: {
            page: 1,
            user_id: $user_id
        },
        dataType: "json",
        success: function (res) {
            console.log(res)
            $('.dangqian p').text(res.data.my_ranking + "/" + res.data.total_num)
            res.data.list.forEach(function (item, index) {
                $("#userList").append(`
                  <li class="item">
                      <div class="left">
                          <div class="number">${index + 1}</div>
                          <div class="poster">
                              <img src="http://pkapp.staraise.com.cn${item.head_pic}" alt="">
                          </div>
                      </div>
                      <div class="right">
                          <div>
                              <div class="user-name">${item.nickname}</div>
                          </div>
                          <div>${item.school || "未知学校"}</div>
                      </div>
                      <div class="pk" data-id="${item.user_id}">PK</div>
                  </li>
              `)
                //   <div class="pk" data-id="${item.user_id}">PK</div>
                console.log($user_id, '$user_id')
                if (item.user_id == $user_id) {
                    // linear-gradient(#fddb92,#fee140)
                    $(".item").eq(index).css("background", "linear-gradient( #FDEB71, #FDD819)")
                    $(".item").eq(index).find($(".pk")).css("display", "none");
                    // var $paiming = index + 1 + '/' + $(".item").length;
                    // $("#paiming").text($paiming)
                }
            })

            //   拒绝邀请
            $("._back").click(function () {
                $(".tanchutn-wrapper").hide()
            })
        },
        error: function (e) {
            console.log("被邀请错误 ---- error");
        }
    })
})


// 邀请PK
$("#userList").delegate('.pk','click',function () {
    console.log(this)
    $touserinfo.nickname = $(this).parent().find(".user-name").text();
    $touserinfo.head_pic = $(this).parent().find("img").attr("src");
    
    console.log($touserinfo)
    createUser();
    $to_user_id = $(this).data("id");
    var postData = {
        user_id: $user_id,
        to_user_id: $to_user_id
    }
    console.log('邀请PK', postData)
    $.ajax({
        type: 'POST',
        url: "http://pkapp.staraise.com.cn/Api/pk/invite",
        data: postData,
        dataType: "json",
        success: function (data) {
            console.log("邀请PK成功")
            console.log(data)
            if (data.msg != "对方不在线") {
                // 显示加载页面
                $("body").removeClass("pkb-bg");
                $("body").addClass("pk-bg");
                $("#load-wrapper").css("display", "block");
                $(".list-wrapper").css("display", "none");
                $("#pk-display").css("display", "none");
                $(".pk-end-wrapper").css("display", "none");
            } else {
                alert("对方不在线！")
            }
            $room_id = data.room_id;
            $knowledgeList = data.data.knowledgeList;
            console.log($knowledgeList)
            $('.user2-poster-wrapper .poster img').attr('src', "http://pkapp.staraise.com.cn" + $userinfo.head_pic);
            $('.user1-title-wrapper .user1_poster').attr('src', "http://pkapp.staraise.com.cn" + $userinfo.head_pic);
        },
        error: function () {
            console.log("邀请PK失败")
        }
    })
})
// 加载更多 列表
var page = 2;
function getMore(page) {
    $.ajax({
        type: 'POST',
        url: "http://pkapp.staraise.com.cn/Api/pk/pklist",
        data: {
            page: page,
            user_id: $user_id
        },
        dataType: "json",
        success: function (res) {
            console.log(res)
            console.log(res.data.list.length)
            if(res.data.list.length == 0) {
                window.page = '-1'
            } else {
                window.page++
            }
        }
    })
}

$(window).scroll(function() {
    var scrollTop = $(this).scrollTop();    //滚动条距离顶部的高度
    var scrollHeight = $(document).height();   //当前页面的总高度
    var clientHeight = $(this).height();    //当前可视的页面高度
    if(scrollTop + clientHeight >= scrollHeight){   //距离顶部+当前高度 >=文档总高度 即代表滑动到底部 count++;         //每次滑动count加1
        console.log('getMore')
        if(page == '-1') {
            console.log('没有更多了')
        } else {
            getMore(page)
        }
    }else if(scrollTop<=0){
        console.log('down')
    }
});

// 渲染对战用户信息
function createUser() {
    console.log($userinfo)
    console.log($touserinfo)
    // $('.user1-poster-wrapper .poster img').attr('src', $touserinfo)
    $(".user1-wrapper .poster img").get(0).src = $touserinfo.head_pic;
    $(".user1-wrapper .user1_name").text($touserinfo.nickname)
    $(".user2-wrapper .poster img").get(0).src = $userinfo.head_pic;
    $(".user2-wrapper .user2_name").text($userinfo.nickname)

    // user1
    $(".user2-title-wrapper img").get(0).src = $touserinfo.head_pic;
    $(".user2-title-wrapper .user2-name").text($touserinfo.nickname);
    // user2
    $(".user1-title-wrapper img").get(0).src = $userinfo.head_pic;
    $(".user1-title-wrapper .user1-name").text($userinfo.nickname);
    // $('user1_poster').attr('src', 'http://pkapp.staraise.com.cn' + $userinfo.head_pic)
    // END
    // $(".pk-end-wrapper .user1 img").attr('src',  $userinfo.head_pic);
    $(".pk-end-wrapper .user1 .user-name").text($userinfo.nickname);
    $(".pk-end-wrapper .user2 img").get(0).src = $touserinfo.head_pic;
    $(".pk-end-wrapper .user2 .user-name").text($touserinfo.nickname);
    // END
    if ($winer_id == $to_user_id) {
        // $(".pk-end-wrapper .user1-info img").attr('src','http://pkapp.staraise.com.cn' + $userinfo.head_pic);
    } else {
        $(".pk-end-wrapper .user1-info img").get(0).src = $touserinfo.head_pic;
    }
}

// 答题定时器
function gameTimerStart() {
    var $timerstart = setInterval(function () {
        // 渲染页面时间
        if ($time_number > 0) {
            $time_number--;
        }
        var $time_str = "" + $time_number;
        $(".daojishi-wrapper .daojishi-content").text($time_str);
        if ($_index == 0) {
            createQuestion($_index);
            $_index++;
        }
        if ($time_number == 0) {
            console.log($_index)
            createQuestion($_index);
            if ($_index == 5) {
                clearInterval($timerstart);
                $answer_end = true;

                //newAdd 20190202
                if ($answer_end) {
                    // 判断胜负
                    if ($score_1 > $score_2) {
                        $winer_id = $user_id
                        $result = 1
                        console.log("胜利")
                        $(".pk-end-wrapper .info").text("胜利");
                        $('.jinbi em').text('+10');
                    } else if ($score_1 < $score_2) {
                        $winer_id = $to_user_id
                        console.log("失败")
                        $result = 2
                        $(".pk-end-wrapper .info").text("失败");
                        $('.jinbi em').text('+0');
                        $('.jinbi em').text('+0');
                    } else {
                        console.log("平局")
                        $result = 3
                        $(".pk-end-wrapper .info").text("平局");
                        $('.jinbi em').text('+0');
                    }
                    // 答题分数
                    $("#score1").text($score_1);
                    $("#score2").text($score_2);

                    // 胜负页面显示
                    $questionsWrapper = '';
                    $(".choose-wrapper").html($questionsWrapper);
                    $("#load-wrapper").css("display", "none");
                    $(".list-wrapper").css("display", "none");
                    $("#pk-display").css("display", "none");
                    $(".pk-end-wrapper").css("display", "block");

                    console.log("postData", postData, "***************************************************************************")

                    var postData = {
                        room_id: selfRoomID,
                        user_id: $user_id,
                        score: $score_1,
                        res: $result
                    }
                    console.log(postData)
                    $.ajax({
                        type: 'POST',
                        url: "http://pkapp.staraise.com.cn/Api/pk/sendResult",
                        data: postData,
                        dataType: "json",
                        success: function (data) {
                            console.log(data)
                            console.log("结束 **************** success")
                            $sendResult_data = data;
                            endAction = '1';
                            return;
                        },
                        error: function () {
                            console.log("结束 ************* error")
                        }
                    })
                }
            }
            $_index++;
        }
    }, 1000)
}

// 渲染题目
function createQuestion(index) {
    $is_choose_2 = false;
    $can_choose = false;
    $time_number = 10;
    // let questionsWrapper = '';
    if ($_index < 5) {
        remove();
    }
    if (index < 5) {
        $questionsWrapper = `<form class="questions-wrapper" action="" data-know_id = ${$knowledgeList[index].room_knowledge_id} data-answer=${$knowledgeList[index].answer}>
                                <h5> ${$knowledgeList[index].title} </h5>
                                <label class="choose-btn" for="a" data="a">
                                    <input type="checkbox" name="" style="display:none"> ${$knowledgeList[index].a}
                                </label>
                                <label class="choose-btn" for="a" data="b">
                                    <input type="checkbox" name="" style="display:none"> ${$knowledgeList[index].b}
                                </label>
                                <label class="choose-btn" for="a" data="c">
                                    <input type="checkbox" name="" style="display:none"> ${$knowledgeList[index].c}
                                </label>
                                <label class="choose-btn" for="a" data="d">
                                    <input type="checkbox" name="" style="display:none"> ${$knowledgeList[index].d}
                                </label>
                            </form>`
        $(".choose-wrapper").html($questionsWrapper);
    }
}

// 选择答案部分
$(".choose-wrapper").delegate(".choose-btn", "click", function () {
    var $answer = $(".questions-wrapper").attr("data-answer")
    var _this = $(this);

    if ($(".user1-active").length > 0 || $_index > 5) {
        return
    }
    if ($_index < 5) {
        $(".user1-active").removeClass("user1-active");
    }

    // $_index ++;
    _this.addClass("user1-active")

    console.log("$_index", $_index, "$can_choose", $can_choose, "$answer", $answer)
    if ($(this).attr("data") == $answer) {
        console.log("user1 ------- 回答正确！")
        setTimeout(function () {
            _this.addClass("user1-dui");
            $(".user1_jindu-con").animate({}, function () {
                userHeight_1 += patentHeight / 5;
                $(".user1_jindu-con").animate({
                    height: userHeight_1
                }, "fast");
                $score_1 = Number($("#user1-number").get(0).innerText) + 100;
                $("#user1-number").text($score_1);
            })
        }, 500)

        var postData = {
            room_knowledge_id: $(".questions-wrapper").attr("data-know_id"),
            user_id: $user_id,
            to_user_id: $to_user_id,
            answer: $(this).attr("data"),
            is_right: 1
        }
        console.log(postData)
        $.ajax({
            type: 'POST',
            url: "http://pkapp.staraise.com.cn/Api/pk/choose",
            data: postData,
            dataType: "json",
            success: function (data) {
                console.log(data)
                console.log("选择正确 ************* success")
            },
            error: function () {
                console.log("选择正确 *************error")
            }
        })
    } else {
        console.log("回答错误！")
        setTimeout(function () {
            _this.addClass("user1-cuo");
        }, 500)

        var postData = {
            room_knowledge_id: $(".questions-wrapper").attr("data-know_id"),
            user_id: $user_id,
            to_user_id: $to_user_id,
            answer: $(this).attr("data"),
            is_right: 2
        }
        console.log(postData)
        $.ajax({
            type: 'POST',
            url: "http://pkapp.staraise.com.cn/Api/pk/choose",
            data: postData,
            dataType: "json",
            success: function (data) {
                console.log(data)
                console.log("选择错误 **************** success")
            },
            error: function () {
                console.log("选择错误 ************* error")
            }
        })
    }

    $('.pk-end-wrapper .user1 img').prop('src', user1OBJ.pic);
    $('.info-wrapper .poster img').prop('src', user1OBJ.pic)
    // 判断user2是否选择完
    let timer = setInterval(function () {
        console.log($is_choose_2);
        if ($is_choose_2) {
            $time_number = 1;
            $can_choose = true;
            if ($_index == 5) {
                $answer_end = true;
            }
            console.log("$_index", $_index, "$can_choose", $can_choose)
            setTimeout(function () {
                // createQuestion($_index);
                if ($answer_end) {
                    // 判断胜负
                    if ($score_1 > $score_2) {
                        $winer_id = $user_id
                        $result = 1
                        console.log("胜利")
                        $(".pk-end-wrapper .info").text("胜利");
                        
                    } else if ($score_1 < $score_2) {
                        $winer_id = $to_user_id
                        console.log("失败")
                        $result = 2
                        $(".pk-end-wrapper .info").text("失败");
                    } else {
                        console.log("平局")
                        $result = 3
                        $(".pk-end-wrapper .info").text("平局");
                    }
                    // 答题分数
                    $("#score1").text($score_1);
                    $("#score2").text($score_2);

                    // 胜负页面显示        
                    $questionsWrapper = '';
                    $(".choose-wrapper").html($questionsWrapper);
                    $("#load-wrapper").css("display", "none");
                    $(".list-wrapper").css("display", "none");
                    $("#pk-display").css("display", "none");
                    $(".pk-end-wrapper").css("display", "block");


                    $('.pk-end-wrapper .user1-info img').attr('src', )
                    var postData = {
                        room_id: selfRoomID,
                        user_id: $user_id,
                        score: $score_1,
                        res: $result
                    }
                    console.log("postData", postData, "***************************************************************************")
                    if(endAction != '1') {
                        $.ajax({
                            type: 'POST',
                            url: "http://pkapp.staraise.com.cn/Api/pk/sendResult",
                            data: postData,
                            dataType: "json",
                            success: function (data) {
                                console.log(data)
                                console.log("结束 **************** success")
                                $sendResult_data = data;
                            },
                            error: function () {
                                console.log("结束 ************* error")
                            }
                        })
                    }
                }
            }, 1500)

            // 对手class显示
            if ($user2_isright == 1) {
                $.each($(".choose-wrapper .choose-btn"), function (index, item) {
                    if ($(item).attr("data") == $user2_answer) {
                        console.log("user2正确")
                        $(item).addClass("user2-dui");
                    }
                })
            } else if ($user2_isright == 2) {
                $.each($(".choose-wrapper .choose-btn"), function (index, item) {
                    if ($(item).attr("data") == $user2_answer) {
                        console.log("user2错误")
                        $(item).addClass("user2-cuo");
                    }
                })
            }
            clearInterval(timer);
        }
    }, 500)
})

// 清除样式
function remove() {
    $(".user1-active").removeClass("user1-active");
    $(".user1-dui").removeClass("user1-dui");
    $(".user1-cuo").removeClass("user1-cuo");
    $(".user2-dui").removeClass("user2-dui");
    $(".user2-cuo").removeClass("user2-cuo");
}

// 点击继续挑战按钮  ----》 跳转首页
$(".contain-btn").on("click", function () {
    
    endAction = '';
    // window.location.href='http://pkapp.staraise.com.cn/index.php/mobile/weixin/get_userinfo'
    // 显示加载页面
    $("#load-wrapper").css("display", "none");
    $(".list-wrapper").css("display", "none");
    $("#pk-display").css("display", "none");
    $(".pk-end-wrapper").css("display", "none");
    $(".list-wrapper").css("display", "block");
    $(".tanchutn-wrapper").css("display", "none")

    $(".pk-bg").removeClass("pk-bg");
    $("body").addClass("pkb-bg");

    // $user_id = '',
    // $client_id = '',
    $touserinfo = {}
    $action = '',
        $room_id = '',
        $to_user_id = '',
        $data = {},
        $knowledgeList = [],
        $user2_answer = '',
        $user2_isright = '',
        $comeMe = false //告诉我进来了

    patentHeight = $(".jindu").height(),
        userHeight_1 = 0,
        userHeight_2 = 0,
        $quset_index = 0,
        timeText = 0,
        $_index = 0, // 渲
        $is_choose_2 = false,
        $can_choose = false,
        $score_1 = 0,
        $score_2 = 0,
        $winer_id = '',
        $sendResult_data = {},
        $result = '',
        $time_number = 10,
        $time_text = 0,
        $answer_end = false;

    $("#user1-number").text($score_1);
    $("#user2-number").text($score_2);
    // $questionsWrapper = '';          
    // $(".choose-wrapper").html($questionsWrapper);
    $(".user1_jindu-con").animate({
        height: 0
    }, "fast")
    $(".user2_jindu-con").animate({
        height: 0
    }, "fast")

    let $questionsWrapper = `<form class="questions-wrapper" action="" data-know_id = ${$knowledgeList[index].room_knowledge_id} data-answer=${$knowledgeList[index].answer}>
                                <h5></h5>
                                <label class="choose-btn" for="a" data="a">
                                    <input type="checkbox" name="" style="display:none">
                                </label>
                                <label class="choose-btn" for="a" data="b">
                                    <input type="checkbox" name="" style="display:none"> 
                                </label>
                                <label class="choose-btn" for="a" data="c">
                                    <input type="checkbox" name="" style="display:none"> 
                                </label>
                                <label class="choose-btn" for="a" data="d">
                                    <input type="checkbox" name="" style="display:none">
                                </label>
                            </form>`
    $(".choose-wrapper").html($questionsWrapper);
})

// 点击头像跳转我的页面
$(".user-btn").on("click", function () {
    console.log("跳转到我的页面")
//     window.location.href = 'http://pkapp.staraise.com.cn/index.php/mobile/user/index'
})

$(".abandon-b").on("click", function () {
    // 显示加载页面
    $(".list-wrapper").css("display", "block");
    $("#load-wrapper").css("display", "none");
    $("#pk-display").css("display", "none");
    $(".pk-end-wrapper").css("display", "none");
    $(".tanchutn-wrapper").css("display", "none");

    $(".pk-bg").removeClass("pk-bg");
    $("body").addClass("pkb-bg");
})