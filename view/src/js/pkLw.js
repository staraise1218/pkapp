



$('#userList').delegate('.send','click', function () {
    console.log($(this).attr('data-id'))
    $(".lwWrap").show();
    $('.lw_bg').show();
})



$('.lwWrap .close').click(function () {
    $(".lwWrap").hide();
    $('.lw_bg').hide();
})
















