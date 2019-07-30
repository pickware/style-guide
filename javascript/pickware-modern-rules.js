/* eslint-disable quote-props */

module.exports = {
    'plugins': [
        'promise',
    ],
    'rules': {
        'comma-dangle': ['error', {
            arrays: 'always-multiline',
            objects: 'always-multiline',
            imports: 'always-multiline',
            exports: 'always-multiline',
            functions: 'always-multiline',
        }],
        // See https://github.com/xjamundx/eslint-plugin-promise#rules
        'promise/catch-or-return': 'error',
        'promise/no-return-wrap': 'error',
        'promise/param-names': 'error',
        'promise/always-return': 'error',
        'promise/no-native': 'off',
        'promise/no-nesting': 'error',
        'promise/no-promise-in-callback': 'error',
        'promise/no-callback-in-promise': 'error',
        'promise/avoid-new': 'error',
        'promise/no-new-statics': 'error',
        'promise/no-return-in-finally': 'error',
        'promise/valid-params': 'error',
        'promise/prefer-await-to-then': 'error',
        'promise/prefer-await-to-callbacks': 'error',

        // Overwrite airbnb-base:
        'no-unused-vars': ['error', {
            // Added: Allow keeping unused variables when prefixed with an underscore.
            // Intended for unused arguments that precede used ones in an argument list.
            argsIgnorePattern: '^_',
            // Changed: Now, all unused arguments must be removed or prefixed.
            args: 'all',
            // Copied from airbnb-base:
            vars: 'all',
            ignoreRestSiblings: true,
        }],
    },
};
