/**
 * [知识库页面]
 * @param  {[type]}
 * @return {[type]}
 */

if(localStorage.getItem('USERINFO')) {
    let myUsetInfo = localStorage.getItem('USERINFO');
    myUsetInfo = JSON.parse(myUsetInfo);
    console.log(myUsetInfo)
    user_id = myUsetInfo.user_id;
} else {
    user_id = 0;
}

let page = 1;

// 获取初始化页面数据
getContent (user_id);

/**
 * @methods {加载列表}
 */
function getContent (user_id){
    $.ajax({
        type: 'post',
        url: GlobalHost+ '/Api/pk/index',
        data: {
            user_id: user_id,
        },
        success: function(res) {
            $('.total_num').html(res.data.total_num+'场');
            $('.win_num').html(res.data.win_num+'场');
            $('.fail_num').html(res.data.fail_num+'场');
        }
    })
}


// 时间下拉
$('.title2 .right p').click(function () {
    $('.title2 .right ul').slideToggle();
})
$('.title2 .right li').click(function () {
    var time = $(this).attr('time');
    var current_time = $('.title2 .right p').attr('time');
    console.log(time, current_time);
    if(current_time == time) {
        $('.title2 .right ul').slideToggle();
        return;
    }

    $.ajax({
        type: 'post',
        url: GlobalHost+ '/Api/pk/index',
        data: {
            user_id: user_id,
            time: time,
        },
        success: function(res) {
            $('.total_num').html(res.data.total_num+'场');
            $('.win_num').html(res.data.win_num+'场');
            $('.fail_num').html(res.data.fail_num+'场');
        }
    })
    $('.title2 .right p').text($(this).text());
    $('.title2 .right p').attr('time', time);
    $('.title2 .right ul').slideToggle();
})
