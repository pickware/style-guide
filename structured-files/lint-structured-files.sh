#! /bin/bash

# * Called with no arguments, verifies all files are formatted correctly.
# * To format all files, call the script with the --write argument.
# * If you supply further arguments, you need to add the --check or --write argument
#   for prettier yourself.
# * Uses prettier for formatting. See <https://prettier.io/>.

set -eu

maybe_check_arg=
if [[ $# = 0 ]] # If the count of arguments supplied to this script is zero, ...
then
    # ..., default to adding a --check argument for prettier
    maybe_check_arg=--check
fi

# The prettier config is in the same directory as this script ("$0").
# Use realpath to resolve all symbolic links leading to this file so we get the actual directory (and not
# `node_modules/.bin`).
prettier_config="$(dirname "$(realpath "$0")")"/.prettierrc.json

# Run prettier and append all other arguments ("$@") given on the command line:
npx prettier \
    --config-precedence prefer-file \
    --config "$prettier_config" \
    '**/*.{json,yml,yaml,babelrc,xml}' \
    $maybe_check_arg \
    "$@"
