/* eslint-disable quote-props */

module.exports = {
    'rules': {
        'brace-style': ['error', '1tbs', { 'allowSingleLine': false }],
        'class-methods-use-this': 'off',
        'comma-dangle': ['error', {
            arrays: 'always-multiline',
            objects: 'always-multiline',
            imports: 'always-multiline',
            exports: 'always-multiline',
            functions: 'never',
        }],
        'curly': ['error', 'all'],
        'func-names': 'off',
        'indent': ['error', 4, { 'SwitchCase': 1 }],
        'max-len': ['error', 120],
        'multiline-ternary': ['error', 'never'],
        'newline-before-return': ['error'],
        'no-console': 'off',
        'no-multiple-empty-lines': ['error', {
            max: 1,
            maxEOF: 1,
        }],
        'no-param-reassign': 'off',
        'no-use-before-define': ['error', { 'functions': false }],
        'object-curly-newline': ['error', {
            ObjectExpression: {
                multiline: true,
                minProperties: 2,
                consistent: true,
            },
            ImportDeclaration: {
                multiline: true,
                minProperties: 100,
                consistent: true,
            },
        }],
        'object-property-newline': ['error', {
            'allowAllPropertiesOnSameLine': false,
        }],
        'prefer-destructuring': 'off',
    },
};
