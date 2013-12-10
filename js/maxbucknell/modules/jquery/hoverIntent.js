/* jshint unused: true, undef: true, prototypejs: true, camelcase: true, es3: true */
/* global define: false, require: false */

/**
 * HoverIntent plugin for jQuery.
 *
 * Will fire after a user has hovered over an element for a given
 * amount of time, to prevent accidental hovers fired when a user
 * is in fact just moving a mouse.
 *
 * @example
 * $('a').on('hoverIntent', function () { $(this).addClass('hovered'); });
 *
 * @copyright 2013 Redbox Digital
 * @author Max Bucknell
 */
define('jquery/hoverIntent', ['jquery'], function ($) {
    $.event.special.hoverIntent = (function () {
        var hoverIntentDelay = 200;
        return {
            'setup': function (data, namespaces) {
                var namespace,
                    $el;
                
                namespace = namespaces.length ? '.' + namespaces.join('.'): '';
                $el = $(this);
            
                $el.on('mouseenter.hoverIntent' + namespace, (function () {
                    var timeoutID;
                    return function () {
                        timeoutID = window.setTimeout(function () {
                            $el.trigger('hoverIntent' + namespace);
                        }, hoverIntentDelay);
                    
                        $el.one('mouseleave', function () {
                            window.clearTimeout(timeoutID);
                        });
                    };
                })());
            },
        
            'teardown': function (namespaces) {
                var namespace,
                    $el;
            
                namespace = namespaces.length ? '.' + namespaces.join('.'): '';
                $el = $(this);
            
                $el.off('mousenter.hoverIntent' + namespace);
            }
        };
    })();

    $.fn.hoverIntent = function (fnOver, fnOut) {
        return this.on('hoverIntent', fnOver).on('mouseleave', fnOut || fnOver);
    };

    return $;
});