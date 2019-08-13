/* eslint-disable quote-props */

module.exports = {
    'extends': require.resolve('./ecmascript-5.js'),
    'globals': {
        'Ext': false,
        'ViisonCommonApp': false,
        'ViisonCommonEventBus': false,
        'ViisonCommonShopwareVersionUtil': false,
        'ViisonCurrencyFormatter': false,
        'ViisonStatusTranslator': false,
        'Shopware': false,
        'timeFormat': false,
    },
    'rules': {
        // Downgrade max-len to warning since this is impractical with ExtJS's protracted class names
        'max-len': ['warn', 120],
        'consistent-this': ['warn', 'actuallyWeDontAllowThisAtAll'],
        'no-console': 'error',
        'no-restricted-properties': ['error', {
            object: 'Ext',
            property: 'bind',
            message: 'Please use Function.prototype.bind() instead.',
        }],
        'object-curly-newline': ['error', {
            ObjectExpression: {
                multiline: true,
                minProperties: 100,
                consistent: true,
            },
        }],
        'object-property-newline': ['error', {
            'allowAllPropertiesOnSameLine': true,
        }],
    },
};
