// let patentHeight = $(".jindu").height();
// let userHeight_1 = 0;
// let userHeight_2 = 0;
// let quset_index = 0;
// let timer = 5000;
// let timeText = 0;

// var user1_info = {
//     poster: '/application/mobile/view/static/image/PK-11.jpg',
//     user_name: '小明',
//     user_id: '123456',
//     count: 0,
//     questions: [
//         {title:'问题1',answers:[['选项1','1'],['选项2','2'],['选项3','2'],['选项4','2']]},
//         {title:'问题2',answers:[['选项1','2'],['选项2','1'],['选项3','2'],['选项4','2']]},
//         {title:'问题3',answers:[['选项1','2'],['选项2','2'],['选项3','1'],['选项4','2']]},
//         {title:'问题4',answers:[['选项1','2'],['选项2','2'],['选项3','2'],['选项4','1']]},
//         {title:'问题5',answers:[['选项1','2'],['选项2','2'],['选项3','2'],['选项4','1']]}
//     ],
//     answers: [[],[],[],[],[]],
//     duishou: {
//         poster: '/application/mobile/view/static/image/PK-12.jpg',
//         user_name: '小王',
//         user_id: '654321',
//         count: 0,
//         answers: [[],[],[],[],[]]
//     }
// }

// window.onload = function () {
//     $(".user1_poster").prop("src",user1_info.poster);
//     $(".user1-name").text(user1_info.user_name);
//     $(".user2_poster").prop("src",user1_info.duishou.poster);
//     $(".user2-name").text(user1_info.duishou.user_name);

//     for(let j = 0; j < 5; j++) {
//         (function(j) {
//             setTimeout(function() {
//                 createQuestion(quset_index);
//                 quset_index++;
//                 console.log(quset_index)
//             }, j*timer)
//         })(j)
//     }
//     timeFunc();
// }

// // 设置定时器
// function timeFunc () {
//     setInterval(function() {
//         if(timeText >= 0) {
//             // console.log(timeText)
//             if(timeText > 0) {
//                 timeText--;
//             }
//             $(".daojishi-content").text(timeText);
//         }
//         // console.log(localStorage.getItem("user2.answers"))
//     }, 1000)
// }



// function createQuestion(index) {
//     timeText = 5;
//     let questionsStr = '';
//     // user1_info.questions[index].answers[i][1] = 3;
//     if(index < 5) {
//         questionsStr += `<h5>${user1_info.questions[index].title}</h5>`
//         for(var i = 0; i < 4; i++) {
//             questionsStr += `<label class="choose-btn" for="a" data="${user1_info.questions[index].answers[i][1]}">
//             <input type="checkbox" name="" style="display:none">${user1_info.questions[quset_index].answers[i][0]}
//             </label>`
//         }
//         $(".questions-wrapper").html(questionsStr);
//     }
//     localStorage.setItem("user1.answers","")
//     localStorage.setItem("user2.answers","")
// }


// // user1
// $(".questions-wrapper").delegate(".choose-btn","touchstart", function () {
//     var _this = $(this);
//     var ws = new WebSocket("ws://120.92.10.2:2345")
//     ws.onopen=function(){
//         ws.send("youaremybaby")
//     }
//     if($(".user1-active").length > 0 || quset_index > 5) {
//         return
//     }
//     if(quset_index == 5 && timeText == 0) {
//         return
//     }
//     if(quset_index < 5) {
//         $(".user1-active").removeClass("user1-active");
//     }
//     _this.addClass("user1-active")
    
//     // user2
//     console.log(localStorage.getItem("user2.answers"))
//     if(localStorage.getItem("user2.answers") == 1) {
//         console.log('user2选择完毕---正确')
//         setTimeout(function() {
//             _this.addClass("user2-dui");
//             $(".user2_jindu-con").animate({},function() {
//                 userHeight_2 += patentHeight / 5;
//                 $(".user2_jindu-con").animate({height:userHeight_2},"fast")
//                 $("#user2-number").get(0).innerText = Number($("#user1-number").get(0).innerText) + 100;
//             })
//         }, 500)


//     } else {
//         console.log("user2----错误")
//     }
//     // user2 end

//     // user1
//     if($(this).attr("data") == 1) {
//         console.log("回答正确！")
        
//         user1_info.count += 100;
//         user1_info.answers[quset_index] = 1;        
//         localStorage.setItem("user1.answers",user1_info.answers[quset_index])

//         setTimeout(function() {
//             _this.addClass("user1-dui");
//             $(".user1_jindu-con").animate({},function() {
//                 userHeight_1 += patentHeight / 5;
//                 $(".user1_jindu-con").animate({height:userHeight_1},"fast")
//                 $("#user1-number").get(0).innerText = Number($("#user1-number").get(0).innerText) + 100;
//             })
//         }, 500)
//     } else if($(this).attr("data") == 2){
//         console.log("回答错误！")

//         user1_info.answers[quset_index] = 2;
//         localStorage.setItem("user1.answers",user1_info.answers[quset_index])
//         console.log(localStorage.getItem("user2.answers"))
        
//         setTimeout(function() {
//             _this.addClass("user1-cuo");
//             // quset_index++;
//             // createQuestion(quset_index);
//         }, 500)
//     } else {
//         console.log("已经选择过正确答案")
//     }
// })

// function remove() {
//     $(".user1-active").removeClass("user1-active");
//     $(".user1-dui").removeClass("user1-dui");
//     $(".user1-cuo").removeClass("user1-cuo");
//     $(".user2-dui").removeClass("user2-dui");
//     $(".user2-cuo").removeClass("user2-cuo");
// }

let patentHeight = $(".jindu").height();
let userHeight_1 = 0;
let userHeight_2 = 0;
let quset_index = 0;
let timer = 10000;
let timeText = 0;

var user1_info = {
    poster: '/application/mobile/view/static/image/PK-11.jpg',
    user_name: '小明',
    user_id: '123456',
    count: 0,
    questions: [
        {title:'问题1',answers:[['选项1','1'],['选项2','2'],['选项3','2'],['选项4','2']]},
        {title:'问题2',answers:[['选项1','2'],['选项2','1'],['选项3','2'],['选项4','2']]},
        {title:'问题3',answers:[['选项1','2'],['选项2','2'],['选项3','1'],['选项4','2']]},
        {title:'问题4',answers:[['选项1','2'],['选项2','2'],['选项3','2'],['选项4','1']]},
        {title:'问题5',answers:[['选项1','2'],['选项2','2'],['选项3','2'],['选项4','1']]}
    ],
    answers: [[],[],[],[],[]],
    duishou: {
        poster: '/application/mobile/view/static/image/PK-12.jpg',
        user_name: '小王',
        user_id: '654321',
        count: 0,
        answers: [[],[],[],[],[]]
    }
}


window.onload = function () {
    $(".user1_poster").prop("src",user1_info.poster);
    $(".user1-name").text(user1_info.user_name);
    $(".user2_poster").prop("src",user1_info.duishou.poster);
    $(".user2-name").text(user1_info.duishou.user_name);
}


function init() {
    for(let j = 0; j < 5; j++) {
        (function(j) {
            setTimeout(function() {
                createQuestion(quset_index);
                quset_index++;
                // console.log(quset_index)
            }, j*timer)
        })(j)
    }
    timeFunc();
}

// 设置定时器
function timeFunc () {
    setInterval(function() {
        if(timeText >= 0) {
            // console.log(timeText)
            if(timeText > 0) {
                timeText--;
            }
            $(".daojishi-content").text(timeText);
        }
        // console.log(localStorage.getItem("user2.answers"))
    }, 1000)
}



function createQuestion(index) {
    timeText = 10;
    let questionsStr = '';
    // user1_info.questions[index].answers[i][1] = 3;
    if(index < 5) {
        questionsStr += `<h5>${user1_info.questions[index].title}</h5>`
        for(var i = 0; i < 4; i++) {
            questionsStr += `<label class="choose-btn" for="a" data="${user1_info.questions[index].answers[i][1]}">
            <input type="checkbox" name="" style="display:none">${user1_info.questions[quset_index].answers[i][0]}
            </label>`
        }
        $(".questions-wrapper").html(questionsStr);
    }
    localStorage.setItem("user1.answers","")
    localStorage.setItem("user2.answers","")
}


// user1
$(".questions-wrapper").delegate(".choose-btn","touchstart", function () {
    var _this = $(this);
    if($(".user1-active").length > 0 || quset_index > 5) {
        return
    }
    if(quset_index == 5 && timeText == 0) {
        return
    }
    if(quset_index < 5) {
        $(".user1-active").removeClass("user1-active");
    }
    _this.addClass("user1-active")
    
    // user2
    // console.log(localStorage.getItem("user2.answers"))
    // if(localStorage.getItem("user2.answers") == 1) {
    //     console.log('user2选择完毕---正确')
    //     setTimeout(function() {
    //         _this.addClass("user2-dui");
    //         $(".user2_jindu-con").animate({},function() {
    //             userHeight_2 += patentHeight / 5;
    //             $(".user2_jindu-con").animate({height:userHeight_2},"fast")
    //             $("#user2-number").get(0).innerText = Number($("#user1-number").get(0).innerText) + 100;
    //         })
    //     }, 500)
    // } else if(localStorage.getItem("user2.answers") == 2) {
    //     console.log("user2----错误")
    // } else {
    //     console.log("user2 未选择")
    // }


    if(localStorage.getItem("user2_choose")!= "") {
        var user2_choose = localStorage.getItem("user2_choose") - 1;
        $(".questions-wrapper .choose-btn").eq(user2_choose)
        console.log($(".questions-wrapper .choose-btn").eq(user2_choose).attr("data")   )
        if($(".questions-wrapper .choose-btn").eq(user2_choose).attr("data") == 1) {
            console.log("user2 --- 正确")
            setTimeout(function() {
                $(".questions-wrapper .choose-btn").eq(user2_choose).addClass("user2-dui");
                $(".user2_jindu-con").animate({},function() {
                    userHeight_2 += patentHeight / 5;
                    $(".user2_jindu-con").animate({height:userHeight_2},"fast")
                    $("#user2-number").get(0).innerText = Number($("#user2-number").get(0).innerText) + 100;
                })
            }, 500)
        } else if($(".questions-wrapper .choose-btn").eq(user2_choose).attr("data") == 2) {
            console.log("user2 --- 错误")
            setTimeout(function() {
                $(".questions-wrapper .choose-btn").eq(user2_choose).addClass("user2-cuo");
            }, 500)
        } else {
            console.log("user2 --- 未选择")
        }
    }

    // user2 end

    if($(this).attr("data") == 1) {
        console.log("回答正确！")
        
        user1_info.answers[quset_index] = 1;        
        localStorage.setItem("user1.answers",user1_info.answers[quset_index])

        setTimeout(function() {
            _this.addClass("user1-dui");
            $(".user1_jindu-con").animate({},function() {
                userHeight_1 += patentHeight / 5;
                $(".user1_jindu-con").animate({height:userHeight_1},"fast")
                $("#user1-number").get(0).innerText = Number($("#user1-number").get(0).innerText) + 100;
            })
        }, 500)
    } else if($(this).attr("data") == 2){
        console.log("回答错误！")

        user1_info.answers[quset_index] = 2;
        localStorage.setItem("user1.answers",user1_info.answers[quset_index])
        console.log(localStorage.getItem("user2.answers"))
        
        setTimeout(function() {
            _this.addClass("user1-cuo");
            // quset_index++;
            // createQuestion(quset_index);
        }, 500)
    } else {
        console.log("已经选择过正确答案")
    }
})

function remove() {
    $(".user1-active").removeClass("user1-active");
    $(".user1-dui").removeClass("user1-dui");
    $(".user1-cuo").removeClass("user1-cuo");
    $(".user2-dui").removeClass("user2-dui");
    $(".user2-cuo").removeClass("user2-cuo");
}