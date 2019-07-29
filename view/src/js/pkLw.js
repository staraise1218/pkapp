var GlobalHost = 'http://pkapp.staraise.com.cn'
var gift_id = '';
var to_user_id = '';
var num = '1';


$('#userList').delegate('.send','click', function () {
    console.log($(this).attr('data-id'))
    to_user_id = $(this).attr('data-id');
    $(".lwWrap").show();
    $('.lw_bg').show();
})



$('.lwWrap .close').click(function () {
    $(".lwWrap").hide();
    $('.lw_bg').hide();
})



$.ajax({
    type: 'post',
    url: GlobalHost + '/Api/gift/myGift',
    data: {
        user_id: $user_id
    },
    success: function (res) {
        console.log(res);

        var list = '';
        res.data.forEach(item => {
            list += `<div class="item" data-id="${item.gift_id}">
                        <img src="${GlobalHost + item.image}" alt="">
                        <div class="t">
                            <p>${item.price}</p>
                            <img src="./src/img/1/jinbi.png" alt="">
                        </div>
                    </div>`
            $('.lwWrap .con').html(list)
        })


    }
})



$('.lwWrap .con').delegate('.item', 'click', function () {
    console.log($(this).attr('data-id'));
    gift_id = $(this).attr('data-id');
    $.ajax({
        type: 'post',
        url: GlobalHost + '/Api/gift/give',
        data: {
            user_id: $user_id,
            to_user_id: to_user_id, 
            gift_id: gift_id,
            num: num
        },
        success: function (res) {
            console.log(res)
                $('.lwWrap').hide();
                $('._lw_alert').text(res.msg).show();
                setTimeout(() => {
                    $('._lw_alert').hide();
                    $('.lw_bg').hide();
                }, 1000)
        }
    })
})








