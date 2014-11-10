$(function() {
    // goto top
    $(window).on('scroll load', smartBackToTop);

    $('#gotoTop').click(function() {
        $('html, body').animate({
            scrollTop: 0
        });

        return false;
    });

    function smartBackToTop(e) {
        if ($(this).scrollTop()) {
            $('#gotoTop:hidden').stop(true, true).fadeIn();
        } else {
            $('#gotoTop').stop(true, true).fadeOut();
        }
    }
});
