/* eslint-disable quote-props */

module.exports = {
    'extends': '../legacy/eslintrc.js',
    'globals': {
        // global Ext object should not be written
        'Ext': false,
    },
    rules: {
        'spaced-comment': 0,
        'comma-dangle': 0, // trailing comma after last element of array / object
        'space-before-function-paren': 0, // function(){} => function (){}
        'vars-on-top': 0 // Declaration on top of scope
    }
};
