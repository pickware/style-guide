/* eslint-disable quote-props */

module.exports = {
    'extends': '../legacy/eslintrc.js',
    'globals': {
        'Ext': false,
        'ViisonCurrencyFormatter': false,
        'Shopware': false,
    },
    rules: {
        // Downgrade max-len to warning since this is impractical with ExtJS's protracted class names
        'max-len': [1, 120],
        'consistent-this': [1, 'actuallyWeDontAllowThisAtAll'],
    },
    rules: {
        'spaced-comment': 0,
        'comma-dangle': 0, // trailing comma after last element of array / object
        'space-before-function-paren': 0, // function(){} => function (){}
        'vars-on-top': 0 // Declaration on top of scope
    }
};
