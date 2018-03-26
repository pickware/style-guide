/* eslint-disable quote-props */

module.exports = {
    'extends': 'airbnb-base/legacy',
    'rules': {
        'no-console': 'off',
        'max-len': ['error', 120],
        'no-param-reassign': 'off',
        'comma-dangle': ['error', {
            arrays: 'always-multiline',
            objects: 'always-multiline',
            imports: 'always-multiline',
            exports: 'always-multiline',
            functions: 'never',
        }],
        'class-methods-use-this': 'off',
        'func-names': 'off',
        'newline-before-return': ['error'],
        'indent': ['error', 4, { 'SwitchCase': 1 }],
        'no-use-before-define': ['error', { 'functions': false }],
        'vars-on-top': 'off',
    },
};
