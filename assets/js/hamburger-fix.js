$(function(){
  var bindFallback=function(){
    var $btn=$('#mobile-collapse');
    if(!$btn.length) return;
    $btn.on('click.hamburgerFix',function(){
      setTimeout(function(){
        var isHorizontal=$('html').hasClass('nxl-horizontal');
        var $container=isHorizontal?$('.topbar'):$('.nxl-navigation');
        if(!$container.hasClass('mob-navigation-active')){
          var $overlay=$('<div class="nxl-menu-overlay"></div>');
          $container.addClass('mob-navigation-active').append($overlay);
          $overlay.on('click',function(){
            $container.removeClass('mob-navigation-active');
            $('.hamburger').removeClass('is-active');
            $(this).remove();
          });
        }
      },50);
    });
  };
  bindFallback();
});
