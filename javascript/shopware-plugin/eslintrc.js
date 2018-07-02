/* eslint-disable quote-props */

module.exports = {
    'extends': '../legacy/eslintrc.js',
    'globals': {
        'Ext': false,
        'ViisonCommonApp': false,
        'ViisonCommonShopwareVersionUtil': false,
        'ViisonCurrencyFormatter': false,
        'Shopware': false,
        'timeFormat': false,
    },
    rules: {
        // Downgrade max-len to warning since this is impractical with ExtJS's protracted class names
        'max-len': ['warn', 120],
        'consistent-this': ['warn', 'actuallyWeDontAllowThisAtAll'],
        'no-console': 'error',
        'no-restricted-properties': ['error', {
            object: 'Ext',
            property: 'bind',
            message: 'Please use Function.prototype.bind() instead.',
        }],
    },
};
