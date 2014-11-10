(function($) {

    /**
     *--------------------------------------------------------------------------
     * Avoid `console` errors in browsers that lack a console.
     *--------------------------------------------------------------------------
     */

    var method;
    var noop = function() {};
    var methods = [
        'assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error',
        'exception', 'group', 'groupCollapsed', 'groupEnd', 'info', 'log',
        'markTimeline', 'profile', 'profileEnd', 'table', 'time', 'timeEnd',
        'timeStamp', 'trace', 'warn'
    ];
    var length = methods.length;
    var console = (window.console = window.console || {});

    while (length--) {
        method = methods[length];

        // Only stub undefined methods.
        if (!console[method]) {
            console[method] = noop;
        }
    }


    /**
     *--------------------------------------------------------------------------
     * Browser hacks
     *--------------------------------------------------------------------------
     */

    var nua = navigator.userAgent

    // android native browser
    var isAndroid = (nua.indexOf('Mozilla/5.0') > -1 && nua.indexOf('Android ') > -1 && nua.indexOf('AppleWebKit') > -1 && nua.indexOf('Chrome') === -1)
    if (isAndroid) {
        $('select.form-control').removeClass('form-control').css('width', '100%')
    }

    // viewport fix
    if (nua.match(/IEMobile\/10\.0/)) {
        var msViewportStyle = document.createElement('style')
        msViewportStyle.appendChild(
            document.createTextNode(
                '@-ms-viewport{width:auto!important}'
            )
        )
        document.querySelector('head').appendChild(msViewportStyle)
    }


    /**
     *--------------------------------------------------------------------------
     * jQuery Scrollstop Plugin v1.1.0
     * https://github.com/ssorallen/jquery-scrollstop
     *--------------------------------------------------------------------------
     */

    var dispatch = $.event.dispatch || $.event.handle;

    var special = $.event.special,
        uid1 = 'D' + (+new Date()),
        uid2 = 'D' + (+new Date() + 1);

    special.scrollstart = {
        setup: function(data) {
            var _data = $.extend({
                latency: special.scrollstop.latency
            }, data);

            var timer,
                handler = function(evt) {
                    var _self = this,
                        _args = arguments;

                    if (timer) {
                        clearTimeout(timer);
                    } else {
                        evt.type = 'scrollstart';
                        dispatch.apply(_self, _args);
                    }

                    timer = setTimeout(function() {
                        timer = null;
                    }, _data.latency);
                };

            $(this).bind('scroll', handler).data(uid1, handler);
        },
        teardown: function() {
            $(this).unbind('scroll', $(this).data(uid1));
        }
    };

    special.scrollstop = {
        latency: 250,
        setup: function(data) {
            var _data = $.extend({
                latency: special.scrollstop.latency
            }, data);

            var timer,
                handler = function(evt) {
                    var _self = this,
                        _args = arguments;

                    if (timer) {
                        clearTimeout(timer);
                    }

                    timer = setTimeout(function() {
                        timer = null;
                        evt.type = 'scrollstop';
                        dispatch.apply(_self, _args);
                    }, _data.latency);
                };

            $(this).bind('scroll', handler).data(uid2, handler);
        },
        teardown: function() {
            $(this).unbind('scroll', $(this).data(uid2));
        }
    };


    /**
     *--------------------------------------------------------------------------
     * Helpers
     *--------------------------------------------------------------------------
     */

    window.gotoElement = function(anchor, timer) {
        setTimeout(function() {
            if (timer) {
                $('html, body').stop().animate({
                    scrollTop: anchor.offset().top - 10
                }, timer);
            } else {
                $(window).scrollTop(anchor.offset().top - 10);
            }
        }, 4);
    }
})(jQuery);


/**
 *--------------------------------------------------------------------------
 * Detect if upload supported
 *--------------------------------------------------------------------------
 */

var isFileInputSupported = (function() {
    // Handle devices which falsely report support
    if (navigator.userAgent.match(/(Android (1.0|1.1|1.5|1.6|2.0|2.1))|(Windows Phone (OS 7|8.0))|(XBLWP)|(ZuneWP)|(w(eb)?OSBrowser)|(webOS)|(Kindle\/(1.0|2.0|2.5|3.0))/)) {
        return false;
    }
    // Create test element
    var el = document.createElement('input');
    el.type = 'file';
    return !el.disabled;
})();


/**
 *--------------------------------------------------------------------------
 * Detect mobile
 *--------------------------------------------------------------------------
 */

var isMobile = (/android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(navigator.userAgent.toLowerCase()));
