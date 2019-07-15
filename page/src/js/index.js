/**
 * 顶部轮播图
 */
var mySwiper = new Swiper('.swiper-container', {
    navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
    },
    pagination: {
        el: '.swiper-pagination',
        clickable: true,
    },
    a11y: {
        prevSlideMessage: 'Previous slide',
        nextSlideMessage: 'Next slide',
        firstSlideMessage: 'This is the first slide',
        lastSlideMessage: 'This is the last slide',
        paginationBulletMessage: 'Go to slide {{index}}',
        notificationClass: 'swiper-notification',
    },
    speed: 500,
    autoplay: {
        delay: 2000,
        disableOnInteraction: false,
    },
    // effect : 'flip',
})



/**
 * 了解我们的服务切换
 */
$('.service .s_list li').hover(function () {
    $('.service .s_list li').removeClass('on');
    $(this).addClass('on')
})

$('.zs1 .z1').mouseover(function () {
    $(this).find('.k1').css('display', 'flex')
})
$('.zs1 .z1').mouseout(function () {
    $(this).find('.k1').hide()
})



$(function(){
    $(".htmleaf-container").jParticle({
        background: "#141414",
        color: "#E6E6E6"
    });
});