jQuery(document).ready(function($){
    console.log('test');

    if ($(window).width() <= '991') {

        $('.header-menu li.has-childs a').on('click', function (e) {
            if (!$(this).closest('li.has-childs').hasClass('opened')) {
                e.preventDefault();
                $(this).closest('li.has-childs').find('.sub-menu').slideToggle();
                $(this).closest('li.has-childs').toggleClass('opened');
            }
            $(document).mouseup(function (e) { // событие клика по веб-документу
                var div = $(".nav-menu-element.has-childs"); // тут указываем ID элемента
                if (!div.is(e.target) && div.hasClass('opened')  // если клик был не по нашему блоку
                    && div.has(e.target).length === 0) { // и не по его дочерним элементам
                    $(this).find('.sub-menu').slideUp();
                    $('.nav-menu-element.has-childs').removeClass('opened');
                }
            });
        })

    }


    $('input[type=tel]').inputmask({"mask": "+7 999 999-99-99"}); //specifying options
    
    $(document).scroll(function() {
        if ($(this).scrollTop() >= 50) {
        $('#header').addClass('painted');
        // console.log('scroll')
        }else{
        $('#header').removeClass('painted');
        }
    });
    


    $('.tab__btn').click(function() {
        let key = $(this).attr('data-tab');
        $('.tab__btn').removeClass('active');
        $(this).addClass('active');
        $('.tab').removeClass('active');
        $('#' + key).addClass('active');
    });


    /*============ FUNCTIONS ===========*/

    function callbackViewHook(modal, props) {
        console.log(modal, props)
    }
    
    let mobileMenu = new MobileMenu(); // Вызов объекта класса мобильного меню
    mobileMenu.init(); // Инициализация мобильного меню
    let themeModal = new ThemeModal(); // Вызов объекта класса модалок
    
    // themeModal.modalsView['callback'] = {
    // 	callback: callbackViewHook
    // };
    themeModal.init(); // Инициализация модалок

});

document.addEventListener('wpcf7mailsent', function(ev) {
    // Один общий URL для всех форм:
    window.location.href = '/spasibo/';   // поменяй на свой URL
}, false);
