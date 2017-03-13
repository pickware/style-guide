# ESLint configuration

Include `"viison-style-guide": "git+https://github.com/VIISON/style-guide.git"` in devDependencies in your `package.json`

## ES6

To utilize ES6 linting create a `.eslintrc.js` in the desired directory and insert
```
module.exports = require('viison-style-guide/javascript/eslintrc');
```

## ES5

To utilize ES5 linting create a `.eslintrc.js` in the desired directory and insert
```
module.exports = require('viison-style-guide/javascript/legacy/eslintrc');
```
