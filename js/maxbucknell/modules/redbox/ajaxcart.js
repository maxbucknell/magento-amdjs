/* jshint unused: true, undef: true, prototypejs: true, camelcase: true, es3: true */
/* global define: false, require: false */

/**
 * Utility for adding products to the cart via AJAX.
 *
 * @example
 * require('redbox/ajaxcart', function (AjaxCart) {
 *     new AjaxCart({
 *         overlayLoadingHTML: '<div class="overlay">Adding to basket</div>'
 *     });
 * });
 *
 * @package redbox
 * @module ajaxcart
 * @copyright 2013 Redbox Digital
 * @author Max Bucknell
 *
 * @param  {Object} hogan
 * hogan.js templating engine.
 */
define('redbox/ajaxcart', ['redbox/statemachine', 'lib/hogan', 'lib/hammer'], function(StateMachine, hogan, hammer) {
    'use strict';
    var config,
        getParameters,
        states,
        init,
        fsm,
        reset;

    /**
     * Options for AJAX cart.
     *
     * @type {Object}
     *
     * @property {Element} submissionElement
     * The element that triggers the submission of the add to cart form.
     *
     * @property {Number} finishedDelay
     * The length of time, in seconds, that the buttons and
     * overlays should remain after the ajax request completes.
     *
     * @property {Mustache} buttonAddingHTML
     * A Mustache template of the innerHTML of the submission element,
     * to be used between a form submission and an AJAX response.
     * Leave empty for no change.
     *
     *
     * @property {String} buttonAddingClassName
     * The classname to be added to the submission element between a
     * submission and an AJAX response.
     *
     * @property {Mustache} overlayAddingHTML
     * Arbitrary HTML to be appended to the body element between a
     * submission and an AJAX response. Leave empty for no overlay.
     *
     * @property {Mustache} buttonAddedHTML
     * A Mustache template of the innerHTML of the submission element,
     * to be applied after a successful AJAX response. Leave empty for
     * no change.
     *
     * Available Variables:
     * + productName
     *
     * @property {String} buttonAddedClassName
     * The classname to be added to the submission element after a
     * successful AJAX response.
     *
     * @property {Mustache} overlayAddedHTML
     * Arbitrary HTML to be appended to the body element after a
     * successful AJAX response. Leave empty for no overlay.
     *
     * Available Variables:
     * + productName
     *
     * @property {Mustache} buttonFailedHTML
     * A Mustache template of the innerHTML of the submission element,
     * to be applied after an unsuccessful AJAX response. Leave empty
     * for no change.
     *
     * @property {String} buttonFailedClassName
     * The classname to be added to the submission element after an
     * unsuccessful AJAX response.
     *
     * @property {Mustache} overlayFailedHTML
     * Arbitrary HTML to be appended to the body element after an
     * unsuccessful AJAX response. Leave empty for no overlay.
     */
    config = {
        submissionElement: $('maximo-cart-submit'),
        finishedDelay: 5,
        buttonAddingHTML: '',
        buttonAddingClassName: 'ajax-cart-adding',
        overlayAddingHTML: '',
        buttonAddedHTML: '',
        buttonAddedClassName: 'ajax-cart-added',
        overlayAddedHTML: '',
        buttonFailedHTML: '',
        buttonFailedClassName: 'ajax-cart-failed',
        overlayFailedHTML: ''
    };

    getParameters = function () {
        var paramsArray,
            params;

        paramsArray = config.submissionElement.form.action.split('/');
        paramsArray = paramsArray.slice(paramsArray.indexOf('add') + 1);
        params = paramsArray.inject({}, (function () {
            var key;
            return function (params, value, idx) {
                if (idx % 2) {
                    params[key] = value;
                } else if (value) {
                    key = value;
                }
                return params;
            };
        })());

        Object.extend(params, config.submissionElement.form.serialize(true));
        return Object.keys(params).inject('', function (query, key) {
            return query + '/' + key + '/' + params[key];
        });
    };

    states = [
        {
            name: 'add',
            initial: true,
            events: {
                click: 'adding'
            }
        },
        {
            name: 'adding',
            events: {
                goodResponse: 'added',
                badResponse: 'failed'
            },
            onEntry: function () {
                var action,
                    url,
                    parameters,
                    overlay;

                config.submissionElement.addClassName(config.buttonAddingClassName);

                if (config.buttonAddingHTML) {
                    config.submissionElement.innerHTML = config.buttonAddingHTML;
                }

                if (config.overlayAddingHTML) {
                    overlay = hogan.compile(config.overlayAddingHTML).render();
                    document.body.insert(overlay);
                    overlay = document.body.childElements().last().addClassName('maximo-ajax-cart-overlay');
                }

                action = config.submissionElement.form.action;
                url = action.slice(0, action.indexOf('checkout')) + 'maximo/cart/add';
                parameters = getParameters();

                new Ajax.Request(url + parameters, {
                    method: 'post',
                    onComplete: function (r) {
                        var good;
                        good = r.status === 200 && r.responseJSON.successful;
                        fsm.fire((good ? 'good' : 'bad') + 'Response', r);
                    }
                });
            }
        },
        {
            name: 'added',
            events: {
                timeOut: 'add'
            },
            onEntry: function (r) {
                var button,
                    overlay;

                reset();

                $('mini-cart').replace(r.responseJSON.minicart);

                if (config.buttonAddedHTML) {
                    button = hogan.compile(config.buttonAddedHTML).render(r.responseJSON.vars);
                    config.submissionElement.innerHTML = button;
                }

                if (config.overlayAddedHTML) {
                    overlay = hogan.compile(config.overlayAddedHTML).render(r.responseJSON.vars);
                    document.body.insert(overlay);
                    overlay = document.body.childElements().last().addClassName('maximo-ajax-cart-overlay');
                }

                fsm.fire.delay(config.finishedDelay, 'timeOut');
            },
            onExit: function () {
                reset();
            }
        },
        {
            name: 'failed',
            events: {
                timeOut: 'add'
            },
            onEntry: function (r) {
                var button,
                    overlay;

                reset();

                if (config.buttonFailedHTML) {
                    button = hogan.compile(config.buttonFailedHTML).render(r.responseJSON.vars);
                    config.submissionElement.innerHTML = button;
                }

                if (config.overlayFailedHTML) {
                    overlay = hogan.compile(config.overlayFailedHTML).render(r.responseJSON.vars);
                    document.body.insert(overlay);
                    overlay = document.body.childElements().last().addClassName('maximo-ajax-cart-overlay');
                }
                fsm.fire.delay(config.finishedDelay, 'timeOut');
            },
            onExit: function () {
                reset();
            }
        }
    ];

    /**
     * Set the config and start the event listener.
     *
     * @param  {Object}   newConfig
     * Override the default config options.
     *
     * @param  {Function} callback
     * An arbitrary function to run any custom behaviour.
     *
     * @return {void}
     */
    init = function (newConfig) {
        config = Object.extend(config, newConfig);
        fsm = new StateMachine(states);

        reset = (function () {
            var innerHTML, className;
            innerHTML = config.submissionElement.innerHTML;
            className = config.submissionElement.className;

            return function () {
                config.submissionElement.innerHTML = innerHTML;
                config.submissionElement.className = className;
                $$('.maximo-ajax-cart-overlay').each(Element.remove);
            };
        })();

        hammer(config.submissionElement);
        config.submissionElement.on('tap', fsm.fire.curry('click'));
    };

    return {
        init: init
    };
});
