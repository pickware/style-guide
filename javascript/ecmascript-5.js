/* eslint-disable quote-props */

module.exports = {
    'extends': [
        'airbnb-base/legacy',
        require.resolve('./pickware-rules.js'),
    ],
    'rules': {
        'vars-on-top': 'off',
    },
};
