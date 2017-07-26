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
        'no-console': 'error',
    },
};
