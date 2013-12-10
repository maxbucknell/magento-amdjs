/* jshint unused: true, undef: true, prototypejs: true, camelcase: true, es3: true */
/* global define: false, require: false */

/**
 * A module to store basic foundational utilities that are used in a
 * number of other foundation modules.
 *
 * @package redbox
 * @module foundation
 * @author Max Bucknell
 * @copyright 2013 Redbox Digital
 */
define('redbox/foundation', function () {
    'use strict';
    var identifiedTouch,
        capitalizeFirstLetter,
        prefixedStyles;

    /**
     * Return a touch based on id, will return undefined if not found.
     * @param  {Enumerable} touchList The touches to search
     * @param  {Number} id            The identifier of the touch.
     * @return {Touch|undefined}      Either the touch specified, or undefined if not found.
     */
    identifiedTouch = function (touchList, id) {
        var matchId;

        matchId = function (touch) {
            return touch.identifier === id;
        };

        touchList = [].toArray.call(touchList);
        touchList = touchList.filter(matchId);

        return touchList[0];
    };

    /**
     * Turn the first letter of a string to upper case.
     * @param  {String} s The string to be capitalized.
     * @return {String}
     */
    capitalizeFirstLetter = function (s) {
        return s[0].toUpperCase() + s.slice(1);
    };

    /**
     * Generate an object containing CSS rules with vendor prefixes
     * @param  {String} rule     The unprefixed name of the rule.
     * @param  {String} value    The value the rule should take.
     * @param  {Array} prefixes  The prefixes to be used (default to webkit, moz, ms, o)
     * @return {Object}          An object to be used with jQuery or Prototype
     */
    prefixedStyles = function (rule, value, prefixes) {
        var propertyName,
            addDeclaration,
            propertyNames,
            declarationBlock;

        /**
         * Add a prefix to a rule name
         * @param  {String} rule   The unprefixed rule name
         * @param  {String} prefix The prefix to be added
         * @return {String}
         */
        propertyName = function (rule, prefix) {
            return prefix + capitalizeFirstLetter(rule);
        };

        /**
         * Add a rule to an object.
         * @param {String} ruleValue The value that the rule is to take
         * @param {Object} rules     An object containing the rules.
         * @param {String} ruleKey   The name of the property.
         */
        addDeclaration = function (ruleValue, rules, ruleKey) {
            rules[ruleKey] = ruleValue;
            return rules;
        };

        /**
         * Set default prefixes
         * @type {Array}
         */
        prefixes = prefixes || ['webkit', 'moz', 'ms', 'o'];

        propertyNames = prefixes.map(_propertyName.curry(rule)).concat(rule);
        declarationBlock = propertyNames.inject({}, _addDeclaration.curry(value));
        return declarationBlock;
    };

    return {
        'identifiedTouch': identifiedTouch,
        'capitalizeFirstLetter': capitalizeFirstLetter,
        'prefixedStyles': prefixedStyles
    };
});
