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


// 提交答案
$('.signBtn').click(function(){
    
})

