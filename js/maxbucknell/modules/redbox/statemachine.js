/* jshint unused: true, undef: true, prototypejs: true, camelcase: true, es3: true */
/* global define: false, require: false */

/**
 * Module to implement state machines in JavaScript
 *
 * @example
 * require('redbox/statemachine', function (StateMachine) {
 *     var fsm;
 *     fsm = new StateMachine([
 *         {
 *             name: 'working',
 *             initial: true,
 *             events: {
 *                 bored: 'coffee',
 *                 meeting: 'meeting'
 *             },
 *         },
 *         {
 *             name: 'coffee',
 *             events: {
 *                 finished: 'working',
 *                 meeting: 'meeting'
 *             },
 *         },
 *         {
 *             name: 'meeting',
 *             events: {
 *                 finished: 'working'
 *             },
 *         }
 *     ]);
 *
 *     fsm.state(); // 'working'
 *     fsm.fire('bored') // true
 *     fsm.state() // 'coffee'
 *     fsm.fire('bored') // false
 *     fsm.fire('meeting') // true
 *     fsm.fire('finished') // true
 *     fsm.state() // 'working'
 * });
 *
 * @package redbox
 * @module statemachine
 * @copyright 2013 Redbox Digital
 * @author Max Bucknell
 */
define('redbox/statemachine', function () {
    'use strict';
    var StateMachine;

    /**
     * State machine constructor
     * @param {[type]} states [description]
     */
    StateMachine = function (config) {
        var states,
            beforeEvent,
            currentState,
            args,
            findStateByName,
            addState,
            fire,
            state;

        states = config.states;

        beforeEvent = config.beforeEvent || Prototype.emptyFunction;

        currentState = states.findAll(function (state) { return state.initial; })[0];
        if (currentState.onEntry) {
            args = $A(arguments).slice(1);
            currentState.onEntry();
        }

        findStateByName = function (name) {
            return states.findAll(function (state) { return state.name === name; })[0];
        };

        /**
         * Add a state to the list of internal states. Currently does no
         * validation, so don't be an idiot. The events object is a map of
         * new event properties, which augment the existing ones.
         *
         * @param {Object} state
         * @param {Object} events
         */
        addState = function (state, events) {
            states.push(state);
            Object.keys(events).each(function (name) {
                var current, currentEvents;
                current = findStateByName(name);
                currentEvents = current ? current.events : {};
                Object.extend(currentEvents, events[name]);
            });
        };

        /**
         * Fire an event within the state machine.
         *
         * If the current state responds to the given event, then the
         * state will be changed and the function will fire the
         * relevant exit and entry events, then return true.
         *
         * If the current state does not respond to the given event,
         * then the function will return false.
         *
         * @param  {String} event
         * The name of the event
         *
         * @return {Boolean}
         */
        fire = function (event) {
            var newState,
                args;

            newState = findStateByName(currentState.events[event]);
            args = $A(arguments).slice(1);
            if (newState) {
                if (!beforeEvent(event, currentState.name, newState.name)) {
                    return false;
                }

                if (currentState.onExit) {
                    currentState.onExit.apply(this, args);
                }
                currentState = newState;
                if (currentState.onEntry) {
                    currentState.onEntry.apply(this, args);
                }
                return true;
            } else {
                return false;
            }
        };

        /**
         * Return the name of the current state.
         *
         * @return {String}
         */
        state = function () {
            return currentState.name;
        };

        // Define the public API
        this.addState = addState;
        this.fire = fire;
        this.state = state;
    };

    return StateMachine;
});
