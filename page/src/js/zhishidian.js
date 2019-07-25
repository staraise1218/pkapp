
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
createList (user_id, page, "0");


/**
 * @methods {加载列表}
 */
function createList (user_id, page, status){
    $.ajax({
        type: 'post',
        url: GlobalHost+ '/Api/article/index',
        data: {
            user_id: user_id,
            page: page
        },
        success: function(res) {
            console.log(res)
            let data = res.data.list;
            if(data.length == 0) {
                page = "-1";
            } else {
                if(data.length > 0) {
                    page++;
                } else {
                    page == '-1';
                }
                let list = '';
                data.forEach(item => {
                    list += `<div class="list-item" article_id="${item.article_id}">
                                <div class="left">
                                    <img src="${GlobalHost + item.thumb}" alt="">
                                </div>
                                <div class="right">
                                    <p>${item.title}</p>
                                    <p>${item.description}</p>
                                    <div>
                                        <img src="./src/img/1/123321.png" alt="" class="collectBtn">
                                    </div>
                                </div>
                            </div>`
                });
                console.log(list)
                $('.list-wrap').append(list);
            }
        }
    })
}

// 加载更多
$(window).scroll(function() {
    var scrollTop = $(this).scrollTop();    //滚动条距离顶部的高度
    var scrollHeight = $(document).height();   //当前页面的总高度
    var clientHeight = $(this).height();    //当前可视的页面高度
    if(scrollTop + clientHeight >= scrollHeight){   //距离顶部+当前高度 >=文档总高度 即代表滑动到底部 count++;         //每次滑动count加1
        console.log('getMore')
        if(page == '-1') {
            console.log('没有更多了')
        } else {
            createList (user_id, page, "1");
        }
    } else if (scrollTop<=0){
        console.log('down')
    }
});


// 点击进入详情
$('.list-wrap').delegate('.list-item', 'click', function () {
	var article_id = $(this).attr('article_id');
	window.location.href=GlobalHost+"/page/zhishidianDetail.html?article_id="+article_id;
})

$('.list-wrap').delegate('.collectBtn', 'click', function(){
    var article_id = $(this).parents('.list-item').attr('article_id');

     $.ajax({
        type: 'post',
        url: GlobalHost+ '/Api/article/collect',
        data: {
            user_id: user_id,
            article_id: article_id,
        },
        success: function(res) {

            if (res.code == 200) {
                alert(res.msg);
            } else {
                alert(res.msg);
            }
        }
    })
    return false;
})


