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

// 初始化页面数据
var urlArr = getRequest();

$('.grade_title').html(urlArr.grade_title);
$('.lesson_title').html(urlArr.lesson_title);
$('.level_title').html(urlArr.level_title);

// 获取初始化页面数据
getContent (user_id);

/**
 * @methods {加载列表}
 */
function getContent (user_id){
    $.ajax({
        type: 'post',
        url: GlobalHost+ '/Api/user/signPage',
        data: {
            user_id: user_id,
        },
        success: function(res) {
            var data = res.data;
            $.each(res.data, function(i){
                $('.qd_wrap li').eq(i).attr('date', data[i].date);
                $('.qd_wrap li').eq(i).attr('is_sign', data[i].is_sign);
                if(this.is_sign == 1) $('.qd_wrap li').eq(i).addClass('active');
            })

        }
    })
}


// 提交答案
$('.signBtn').click(function(){
    var now = new Date();
    var year = now.getFullYear(); //得到年份
    var month = now.getMonth()+1;//得到月份
    var date = now.getDate();//得到日期
    month = month < 10 ? '0'+month : month;
    date = date < 10 ? '0' + date : date;
    var today_date = year+'-'+month+'-'+date;

    var is_sign = $('.qd_wrap li[date='+today_date+']').attr('is_sign');
    console.log(is_sign);

    if(is_sign == 1) alert('今日已签到');

    $.ajax({
        type: 'post',
        url: GlobalHost+ '/Api/user/sign',
        data: {
            user_id: user_id,
        },
        success: function(res) {
            if(res.code == 200) {
                $('.qd_wrap li[date='+today_date+']').attr('is_sign', 1)
                $('.qd_wrap li[date='+today_date+']').addClass('active')
                alert(res.msg);
            }
        }
    })
})

