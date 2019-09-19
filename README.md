# Pickware style-guide [![CircleCI](https://circleci.com/gh/pickware/style-guide.svg?style=svg)](https://circleci.com/gh/pickware/style-guide)

This repository describes some of our processes and standards.

## Github issues labels

Name              | Description
------------------|-----------------------------------------------------------------------------------------------------------
`bug`             | A mistake in our software
`enhancement`     | Something that we should improve in our software, also includes new features
`question`        | A request for information from the developer who is responsible for the component
`support`         | A support case, i.e. a customer's support request which is not yet confirmed as a bug
`waiting`         | We're waiting for some external response. Only combine with `support`.
`critical`        | A bug that is so severe that one or more customers are unable to work with our software
`major`           | A bug that prevents important functionality of our software from being used
`minor`           | A bug that we should fix, but for which a workaround exists or which has little impact on day-to-day use
`trivial`         | A bug that has next to no impact and is easy to fix (e.g. translation error, small glitches)

## git hooks

You can add automatic code style checks to your shopware plugin repositories using the `pre-commit` and `pre-push` git hooks contained in `git-hooks/shopware-plugin`. Just change your plugin's `composer.json` as follows:

```json
...
"repositories": [
    ...
    {
        "type": "vcs",
        "url": "git@github.com:VIISON/composer-git-hooks-installer-plugin.git"
    },
    {
        "type": "vcs",
        "url": "git@github.com:VIISON/style-guide.git"
    },
    ...
],
...
"require-dev": {
    ...
    "viison/style-guide": "*",
    ...
},
...
"extra": {
    ...
    "required-viison-git-hooks": {
        "viison/style-guide": ["shopware-plugin"]
    },
    ...
},
...
```

Make sure to use the `*` wildcard as the required version of this repository to always use the latest code style definitions.

## ESLint

Name                            | Description
--------------------------------|-----------------------------------------
`javascript/eslintrc.js`        | Describes the ES6 ESLint configuration
`javascript/legacy/eslintrc.js` | Describes the ES5 ESLint configuration

## PHP CodeSniffer

To lint PHP Code using _PHP CodeSniffer_, you have to install the package `php-code-sniffer` (**at least `v3.2.0`**). On macOS you can install it using homebrew `brew install php-cs-fixer`.

The ruleset required to lint Shopware plugins is located in `php/php-codesniffer-standard/VIISON` and is mostly based on the `PSR-1` and `PSR-2` coding standards. You must configure your php-cs plugin in your IDE to use that standard by setting the `standard` option to that path.
