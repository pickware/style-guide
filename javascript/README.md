# ESLint configuration

Include `"viison-style-guide": "git+https://github.com/VIISON/style-guide.git"` in devDependencies in your `package.json`
Add the following lines to your `.gitignore`
```
/node_modules
/package-lock.json
```

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

To utilize our custom Shopware Plugin linting create a `.eslintrc.js` in the desired directory and insert
```
module.exports = {
    extends: "./node_modules/viison-style-guide/javascript/shopware-plugin/eslintrc.js"
};
```

# ESLint usage

For detailed information see [ESLint Getting Started Guide](https://eslint.org/docs/user-guide/getting-started)
To check a specific JS file in your plugin, use a similar command to the following in your plugin root directory
```
./node_modules/.bin/eslint Views/backend/viisonYourPluginFeature/view/window.js
```
