

// // 初始化用户信息
// window.onload = function () {
//     // $(".user1_poster").prop("src",user1_info.poster);
//     // $(".user1-name").text(user1_info.user_name);
//     // $(".user2_poster").prop("src",user1_info.duishou.poster);
//     // $(".user2-name").text(user1_info.duishou.user_name);
// }

// // 开始函数
// function init() {
//     for(let j = 0; j < 5; j++) {
//         (function(j) {
//             setTimeout(function() {
//                 createQuestion(quset_index);
//                 quset_index++;
//                 // console.log(quset_index)
//             }, j*timer)
//         })(j)
//     }
//     timeFunc();
// }


// // 设置定时器
// function timeFunc () {
//     setInterval(function() {
//         if(timeText >= 0) {
//             if(timeText > 0) {
//                 timeText--;
//             }
//             $(".daojishi-content").text(timeText);
//         }
//     }, 1000)
// }


// // 渲染题目与用户信息
// function createQuestion(index) {
//     timeText = 10;
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



// // 选择答案
// $(".questions-wrapper").delegate(".choose-btn","touchstart", function () {
//    console.log($(this))
//    console.log($(this).attr("data"))
// })
