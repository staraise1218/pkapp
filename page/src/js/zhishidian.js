
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
createList (user_id, page);


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

            let list = '';
            data.forEach(item => {
                list += `<div class="list-item" article_id="${item.article_id}">
				            <div class="left">
				                <img src="${item.thumb}" alt="">
				            </div>
				            <div class="right">
				                <p>${item.title}</p>
				                <p>${item.description}</p>
				                <div>
				                    <img src="./src/img/1/123321.png" alt="">
				                </div>
				            </div>
				        </div>`
			});
            $('.list-wrap').html(list);
        }
    })
}

// 点击进入详情
$('.list-wrap').delegate('.list-item', 'click', function () {
	var article_id = $(this).attr('article_id');
	window.location.href=GlobalHost+"/page/zhishidianDetail.html?article_id="+article_id;
})