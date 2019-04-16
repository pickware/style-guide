/* eslint-disable quote-props */

module.exports = {
    'extends': [
        'airbnb-base/rules/best-practices',
        'airbnb-base/rules/errors',
        'airbnb-base/rules/node',
        'airbnb-base/rules/style',
        'airbnb-base/rules/variables',
        'airbnb-base/rules/es6',
        require.resolve('./pickware-rules.js'),
        require.resolve('./pickware-modern-rules.js'),
    ],
    'rules': {
        'no-console': 'error',
        // Disable rules that prohibit manual callback/promise interoparability code until we have a build process for
        // Shopware plugins which can ship an interoperability library (e.g. pify).
        'promise/no-callback-in-promise': 'off',
        'promise/avoid-new': 'off',
    },
    'parserOptions': {
        ecmaVersion: 2018,
    },
};
