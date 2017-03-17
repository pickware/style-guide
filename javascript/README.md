# ESLint configuration

Include `"viison-style-guide": "git+https://github.com/VIISON/style-guide.git"` in devDependencies in your `package.json`

## Modern Javascript (ES2016 and beyond)

To utilize ES6 linting create a `.eslintrc.js` in the desired directory and insert
```
module.exports = require('viison-style-guide/javascript/eslintrc');
```

## Legacy

To utilize ES5 linting create a `.eslintrc.js` in the desired directory and insert
```
module.exports = require('viison-style-guide/javascript/legacy/eslintrc');
```

## Shopware plugin

`./shopware-plugin/.eslintrc.js` contains an ESLint config that is optimized for Shopware Plugin development.
