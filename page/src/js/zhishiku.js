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
        url: GlobalHost+ '/Api/knowledge/zhishikuPage',
        data: {
            user_id: user_id,
        },
        success: function(res) {
            // 初始化年级下拉列表
            var gradeList = res.data.gradeList;
            var selectList = '';

            $.each(gradeList, function(i){
                selectList += `<li grade_id="${i}">${this}</li>`;
            })

            $('.gradeList').html(selectList);

            // 默认显示第一个年级
            var firstGrade = $(selectList).first();
            $('.current_grade_title').html(firstGrade.html())
            $('.current_grade_a').attr('grade_id', firstGrade.attr('grade_id'))
        }
    })
}

// 选择年级
$('.gradeList').delegate('li', 'click', function(){
    var grade_id = $(this).attr('grade_id');
    var current_grade = $(this).html();
    $('.current_grade_title').html(current_grade)
    $('.current_grade_a').attr('grade_id', grade_id)
})

// 点击级别
$('.item').click(function(){
    var grade_type = $('.current_grade_a').attr('grade_id');
    var lesson_type = $(this).attr('lesson_type');
    var level = $(this).attr('level');
    
    var grade_title = $('.current_grade_title').html();
    var lesson_title = $(this).parents('.yuwen').find('.lesson_title').html();
    var level_title = $(this).find('.level_title').html();

    window.location=GlobalHost+'/page/zhishikuList.html?grade_type='+grade_type+'&lesson_type='+lesson_type+'&level='+level+'&grade_title='+grade_title+'&lesson_title='+lesson_title+'&level_title='+level_title;
})
