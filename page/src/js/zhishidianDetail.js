
if(localStorage.getItem('USERINFO')) {
    let myUsetInfo = localStorage.getItem('USERINFO');
    myUsetInfo = JSON.parse(myUsetInfo);
    console.log(myUsetInfo)
    user_id = myUsetInfo.user_id;
} else {
    user_id = 0;
}

let page = 1;


// 请求列表
getContent ();

function getContent(){
    var article_id = getParam('article_id');
    $.ajax({
        type: 'post',
        url: GlobalHost+ '/Api/article/getContent',
        data: {
            article_id: article_id,
        },
        success: function(res) {
            console.log(res.data.content)
            $('.content-wrap').html(res.data.content)
        }
    })
}