/* jshint unused: true, undef: true, prototypejs: true, jquery: true, camelcase: true, es3: true */
/* global define: false, require: false */

/**
 * Module to put content in off screen sidebars.
 *
 * It's great. Just pick some content, a direction, and you're good to
 * go. Works on iPhone, Android, and everything else with a good web
 * browser and a small screen.
 *
 * Multiple sidebars can happily coexist, too.
 *
 * @example
 *
 * require('redbox/sidebar', function (Sidebar) {
 *     Sidebar.getInstance().add({
 *         'name': 'search',
 *         'from': 'right',
 *         'trigger': $$('.search-toggle')[0],
 *         'contentElement': $('search_mini_form'),
 *         'screenSizeBreakPoint': '767px'
 *     });
 * });
 *
 * @package redbox
 * @module sidebar
 * @copyright 2013 Redbox Digital
 * @author Max Bucknell
 *
 * @param {Object} StateMachine
 * The state machine from foundation.
 *
 * @param {Object} enquire
 * enquire framework for media queries.
 *
 * @param {Object} hammer
 * hammer.js from EightMedia for gestures.
 */
define('redbox/sidebar', ['foundation/statemachine', 'lib/enquire', 'lib/hammer'], function (StateMachine, enquire, hammer) {
    'use strict';
    var $,
        Sidebar,
        instance;

    $ = jQuery;

    /**
     * Sidebar constructor.
     */
    Sidebar = function () {
        var configs,
            pageElement,
            disabled,
            disable,
            enable,
            fsm,
            add,
            overlay;

        if (!Modernizr.touch) {
            return;
        }

        configs = {};

        pageElement = $('.page');

        disabled = [];

        disable = function (name) {
            if (disabled.indexOf(name) === -1) {
                disabled.push(name);
                configs[name].rootClassNameWhenEnabled.each(
                    Element.removeClassName.curry(document.documentElement)
                );
            } else {
                return;
            }
        };

        enable = function (name) {
            var idx;
            idx = disabled.indexOf(name);
            if (idx === -1) {
                return;
            } else {
                disabled.splice(idx, 1);
                configs[name].rootClassNameWhenEnabled.each(
                    Element.addClassName.curry(document.documentElement)
                );
            }
        };

        fsm = new StateMachine({
            'states': [
                {
                    'name': 'nothing',
                    'initial': true,
                    'events': {}
                }
            ],
            'beforeEvent': (function (disabled) {
                return function (event, current, destination) {
                    return (disabled.indexOf(destination) === -1);
                };
            })(disabled)
        });

        add = function (config) {
            var defaults,
                container,
                newState,
                newEvents;

            // validate config
            if (!config.name || !config.from || !config.trigger || !config.contentElement || !config.screenSizeBreakPoint) {
                throw new TypeError('config for sidebar must contain at least name, from, trigger, screenSizeBreakPoint and contentElement values.');
            }

            // default values that will be augmented by config
            defaults = {
                'rootClassNameWhenEnabled': ['maximo-sidebar-enabled', 'maximo-sidebar-' + config.name + '-enabled'],
                'rootClassNameWhenVisible': ['maximo-sidebar-visible', 'maximo-sidebar-' + config.from + '-in', 'maximo-sidebar-' + config.name + '-in'],
                'elementClassName': ['maximo-sidebar', 'maximo-sidebar-' + config.name, 'maximo-sidebar-' + config.from],
                'elementId': ['maximo-sidebar-' + config.name]
            };

            Object.keys(defaults).each(function (key) {
                if (!config[key]) {
                    config[key] = [];
                }

                config[key] = config[key].concat(defaults[key]);
            });

            configs[config.name] = config;

            container = $('<div />').addClass(config.elementClassName.join(' ')).attr('id', config.elementId);

            config.element = container;
            config.element.hide();

            pageElement[config.from === 'left' ? 'before' : 'after'](container);

            hammer(config.trigger);

            $(document).trigger('sidebar:' + config.name + 'Created', [config]);

            $(config.trigger).on('tap', function () {
                fsm.fire(config.name);
                return false;
            });

            newState = {
                'name': config.name,
                'onEntry': function () {
                    $(document.body).one('click', 'a', function () { return false; });
                    $(document).trigger('sidebar:' + config.name + 'In', [config]);
                    $(config.element).show();
                    $('.sidebar-overlay').show();
                    $(document.documentElement).addClass(config.rootClassNameWhenVisible.join(' '));
                },
                'onExit': function () {
                    $(document).trigger('sidebar:' + config.name + 'In', [config]);
                    $(config.element).delay(500).hide();
                    $('.sidebar-overlay').hide();
                    $(document.documentElement).removeClass(config.rootClassNameWhenVisible.join(' '));
                },
                'events': {
                    'nothing': 'nothing'
                }
            };

            newEvents = {'nothing': {}};

            newState.events[config.name] = 'nothing';
            newEvents.nothing[config.name] = config.name;
            fsm.addState(newState, newEvents);

            enquire.register(
                'screen and (max-width: ' + config.screenSizeBreakPoint + ')',
                {
                    'match': (function (name) {
                        enable(name);

                        $(config.contentElement).after(
                            $('<span />').attr('id', 'maximo-sidebar-placeholder' + name)
                        ).detach().appendTo($(config.element));
                    }).curry(config.name),
                    'unmatch': (function (name) {
                        $('#maximo-sidebar-placeholder-' + name).after(
                            $(config.contentElement).detach()
                        ).remove();
                    }).curry(config.name)
                }
            );
        };

        overlay = $('<div />').addClass('sidebar-overlay').hide();
        pageElement.after(overlay);
        hammer(overlay[0]);

        overlay.on('tap', function () {
            fsm.fire('nothing');
            return false;
        });

        // public api
        this.add = add;
        this.enable = enable;
        this.disable = disable;
    };

    Sidebar.getInstance = function () {
        if (instance === undefined) {
            instance = new Sidebar();
        }

        return instance;
    };

    return Sidebar;
});
