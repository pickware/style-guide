#!/bin/bash

# Promote SwiftLint's `todo` rule to `error` severity
CI_CONFIG_PATH=".swiftlint-ci.yml"
cat ".swiftlint.yml" > "$CI_CONFIG_PATH"
echo "todo:" >> "$CI_CONFIG_PATH"
echo "  severity: error" >> "$CI_CONFIG_PATH"

if [ -d "./Frameworks" ]; then
    # Ignore all frameworks when linting
    cd "./Frameworks"
    FRAMEWORKS_CONFIG_PATH=".swiftlint.yml"
    touch "$FRAMEWORKS_CONFIG_PATH"
    echo "excluded:" >> "$FRAMEWORKS_CONFIG_PATH"
    for D in *; do
        if [ -d "${D}" ]; then
            echo "  - '${D}'" >> "$FRAMEWORKS_CONFIG_PATH"
        fi
    done
fi
