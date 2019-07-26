var GlobalHost = 'http://pkapp.staraise.com.cn'






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