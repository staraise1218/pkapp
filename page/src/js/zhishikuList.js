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


// 刷新
$('.shuaxin img').click(function () {
    $(this).addClass('active');
    getContent (user_id);
})

/**
 * @methods {加载列表}
 */
function getContent (user_id){
    $.ajax({
        type: 'post',
        url: GlobalHost+ '/Api/knowledge/getList',
        data: {
            lesson_type : urlArr.lesson_type,
            grade_type : urlArr.grade_type,
            level : urlArr.level,
        },
        success: function(res) {
            var list = '';
            $.each(res.data, function(i){
                list += `<li class="item" id="${this.id}" right_answer="${this.answer}">
                            <p>${this.title}</p>
                            <div class="select_item" answer_item="a">
                                <img src="./src/img/1/a1.png" alt="">
                                ${this.a}
                            </div>
                            <div class="select_item" answer_item="b">
                                <img src="./src/img/1/b1.png" alt="">
                                ${this.b}
                            </div>
                            <div class="select_item" answer_item="d">
                                <img src="./src/img/1/c1.png" alt="">
                                ${this.c}
                            </div>
                            <div class="select_item" answer_item="d">
                                <img src="./src/img/1/d1.png" alt="">
                                ${this.d}
                            </div>
                        </li>`;
            })
            $('.list').html(list);
            setTimeout(() => {
                $('.shuaxin img').removeClass('active')
            }, 500);
        }
    })
}

// 选择选项
$('.list').delegate('.item .select_item', 'click', function(){
    //  取消已选择的
    var selectedObj = $(this).parent().find('.selected');
    if(selectedObj.length > 0){
        selectedObj.removeClass('selected');

        var selected_answer_item = selectedObj.attr('answer_item');
        selectedObj.find('img').attr('src', './src/img/1/'+selected_answer_item+'1'+'.png')
    }
    // 选中
    var answer_item = $(this).attr('answer_item');
    $(this).find('img').attr('src', './src/img/1/'+answer_item+'2'+'.png');
    $(this).addClass('selected');
})

// 提交答案
$('.submit').click(function(){
    // 整理结果
    var result = [];
    $.each($('.list .item'), function() {
        var knowledge_id = $(this).attr('id');
        var answer = $(this).find('.selected').attr('answer_item');
        answer = answer ? answer : 0;
        // console.log(answer);
        result.push({knowledge_id: knowledge_id, answer: answer});
    })
    console.log(result);
    // 提交结果
    $.ajax({
        type: 'post',
        url: GlobalHost+ '/Api/knowledge/answer',
        data: {
            user_id: user_id,
            answer: JSON.stringify(result),
        },
        success: function(res) {
           if (res.code == 200) {
                alert('提交成功');
           } else {
                alert(res.msg);
           }
        }
    })
})

