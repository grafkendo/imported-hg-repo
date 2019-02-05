jQuery(document).ready(function($){    
    
    var rtl, mrtl;
    
    if( remoter_data.rtl == '1' ){
        rtl = true;
        mrtl = false;
    }else{
        rtl = false;
        mrtl = true;
    }

    //banner layout two
    $('#banner-slider-two').owlCarousel({
        loop       : true,
        nav        : true,
        dots       : true,
        autoplay   : true,
        animateOut : '',
        navText    : '',
        rtl        : rtl,
        lazyLoad   : true,
        responsive : {
            1440: {
                margin: 40,
                items: 4
            },
            1025: {
                items: 3,
                margin: 40,
            },
            768: {
                items: 2,
                margin: 20
            },
            0: {
                items: 1,
                margin: 0
            }
        }
    });

    $('.site-header .form-holder').prepend('<div class="btn-close-form"><span></span></div>');

    $('.btn-close-form').click(function(){
        $(this).parent('.form-holder').hide("fast");
    });

    // Sticky Header
    var navBar = $('.site-header');
    navBar.next().css('margin-top', $(navBar).height()+12+'px');
    function stickyNav() {
        var $window = $(window);
        var $document = $(document);
        var eloffset = navBar.offset().top;

        return $window.scroll(function() {

            var c = $document.scrollTop();

            if (eloffset < c ){
                navBar.addClass('stick-header');
            } else {
                navBar.removeClass('stick-header');
            }

        });
    }

    stickyNav();
});