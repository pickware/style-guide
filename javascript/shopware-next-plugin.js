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
    ],
    'rules': {
        'no-console': 'error',
    },
    parserOptions: {
        ecmaVersion: 2018,
    },
};
