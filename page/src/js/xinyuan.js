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
getContent (user_id, page);

/**
 * @methods {加载列表}
 */
function getContent (user_id){
    $.ajax({
        type: 'post',
        url: GlobalHost+ '/Api/user/planPage',
        data: {
            user_id: user_id,
            page: page,
        },
        success: function(res) {
           var list = '';
           $.each(res.data, function(i){
                list += `<li class="xyq_list_item">
                            <div class="left">
                                <img src="./src/img/1/te1.png" alt="">
                                <div class="cc">
                                    <p>${this.content}</p>
                                    <p>完成日期：${this.complete_date}</p>
                                </div>
                            </div>
                            <div class="right">`;
                                if(this.is_complete == 1) {
                                    list += '<img src="./src/img/1/z1.png" alt="">';
                                }
                            list += `</div>
                        </li>`;
           })

           $('.xyq_list_wrap').html(list);
        }
    })
}



// 心愿
$('.submit').click(function(){
    var num = $('.num').html();
    var complete_date = $('#date').html();
    // console.log(num, complete_date);

    $.ajax({
        type: 'post',
        url: GlobalHost+ '/Api/user/plan',
        data: {
            user_id: user_id,
            num: num,
            complete_date: complete_date,
        },
        success: function(res) {
           if(res.code == 200) {
                alert(res.msg)
           } else {
                alert(res.msg)
           }
        }
    })  
})