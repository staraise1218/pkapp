;(function () {
  // 点击显示全部
  // $(document).on('click', '.invitation .options .all', function () {
  //   $('.invitation .classify ul').toggle()
  // })
  // 点击显示弹出层
  // $('.plus').click(function () {
  //   $('.mask').toggle();
  //   $('._shade').toggle();
  //   $(this).css("transform", "rotate(45deg)");
  // })
  // // 点击tab切换
  // $(document).on('click', '.horizontal li', function () {
  //   $(this).addClass('active').siblings().removeClass('active')
  // })
  // 点击确定
  // $(document).on('click', '.mask .sure', function () {
  //   $('.mask').hide();
  //   $('._shade').hide();
  // })
  // // 按钮切换
  // $(document).on('click', '.weui-switch', function () {
  //   $('.mask').show();
  //   $('._shade').show();
  // })
  // $(document).on('click', '.mask .cancle', function () {
  //   $('.mask').hide();
  //   $('._shade').hide();
  // })
  // $(document).on('click', '.mask .disturb', function () {
  //   $('.mask').hide();
  //   $('._shade').hide();
  // })
  // 点击切换价格选择
  $(document) .on('click', '.cost-list li', function () {
    $(this).addClass('active').siblings().removeClass('active')
  }) 
  // 点击切换gender
  $(document) .on('click', '.opened .gender .fr span', function () {
    $(this).addClass('active').siblings().removeClass('active')
  }) 
  // 点击分享
  $(document) .on('click', 'header .share', function () {
    $('._shade').show()
    $('._share').show()
  }) 
  $(document) .on('click', '._share p', function () {
    $('._shade').hide()
    $('._share').hide()
  }) 
})()
