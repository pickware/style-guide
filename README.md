# VIISON style-guide

This repository describes some of our processes and standards.

## Github issues labels

Name              | Description
------------------|-----------------------------------------------------------------------------------------------------------
`bug`             | A mistake in our software
`enhancement`     | Something that we should improve in our software, also includes new features
`question`        | A request for information from the developer who is responsible for the component
`support`         | A support case, i.e. a customer's support request which is not yet confirmed as a bug
`critical`        | A bug that is so severe that one or more customers are unable to work with our software
`major`           | A bug that prevents important functionality of our software from being used
`minor`           | A bug that we should fix, but for which a workaround exists or which has little impact on day-to-day use
`trivial`         | A bug that has next to no impact and is easy to fix (e.g. translation error, small glitches)

## ESLint

Name                            | Description
--------------------------------|-----------------------------------------
`javascript/eslintrc.js`        | Describes the ES6 ESLint configuration
`javascript/legacy/eslintrc.js` | Describes the ES5 ESLint configuration

## PHP CodeSniffer

To lint PHP Code using _PHP CodeSniffer_, you have to install the following packages:

* php-code-sniffer
* php-cs-fixer
* php-md

To install them on OS X using homebrew, run the following:

```
brew install php-cs-fixer phpmd php-code-sniffer
```

The ruleset required to lint Shopware plugins is located in `php/php-codesniffer-standard/VIISON` and is mostly based on the `PSR-1` and `PSR-2` coding standards. You must configure your php-cs plugin in your IDE to use that standard by setting the `standard` option to that path.
