/**
 *--------------------------------------------------------------------------
 *
 *--------------------------------------------------------------------------
 */

window.fbAsyncInit = function() {
    FB.init({
        appId: FB_ID,
        cookie: true,
        xfbml: true,
        version: 'v2.2'
    });
};

(function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s);
    js.id = id;
    js.src = "//connect.facebook.net/vi_VN/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));


/**
 *--------------------------------------------------------------------------
 * Resize FB comment plugin
 *--------------------------------------------------------------------------
 */

$(function() {
    var fb_comments = $('.fb-comments');

    if (fb_comments.length) {
        function resizeFBComments() {
            fb_comments.each(function() {
                var self = $(this);

                self.css('display', 'block');
                var _w = self.width();
                self.children('span').css('width', _w + 'px').children('iframe').css('width', _w + 'px');
            });
        }

        $(window).resize(resizeFBComments);
    }
});
