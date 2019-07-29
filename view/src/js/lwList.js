var GlobalHost = 'http://pkapp.staraise.com.cn'
var gift_id = '';
var num = '1';





let $userinfo = JSON.parse(localStorage.getItem('USERINFO'))
console.log($userinfo)
user_id = $userinfo.user_id

$.ajax({
    type: 'post',
    url: GlobalHost + '/Api/Gift/alllist',
    data: {
        user_id: user_id
    },
    success: function (res) {
        console.log(res.data)
        let gift = res.data.gift;
        var str = '';
        gift.forEach(item => {
            str += `<li data-id="${item.id}" data-glamour="${item.glamour}">
                        <img class="j" src="${GlobalHost + item.image}" alt="">
                        <div>
                            <span>${item.price}</span>
                            <img class="m" src="./src/img/1/jinbi.png" alt="">
                        </div>
                    </li>`
        })
        $('.list-wrap').html(str)
    }
})


$('.list-wrap').delegate('li', 'click', function () {
    console.log($(this).attr('data-id'))
    gift_id = $(this).attr('data-id');
    $('.alert_bg').show();
    $('.alert').show();
    $('.alert .b').show();
    $('.alert .tips').text('是否兑换该礼物')
})

$('.alert .c').click(function () {
    $('.alert_bg').hide();
    $('.alert').hide();
})



$('.alert .ok').click(function () {
    $.ajax({
        type: 'post',
        url: GlobalHost + '/Api/gift/buyGift',
        data: {
            user_id: user_id,
            gift_id: gift_id,
            num: num
        },
        success: function (res) {
            console.log(res)
            if(res.code == 200) {
                $('.alert .tips').text(res.msg)
                $('.alert .b').hide();
                setTimeout(function () {
                    $('.alert_bg').fadeOut();
                    $('.alert').fadeOut();
                }, 1000)
            }
        }
    })





})