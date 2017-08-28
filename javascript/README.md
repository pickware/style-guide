# ESLint configuration

Include `"viison-style-guide": "git+https://github.com/VIISON/style-guide.git"` in devDependencies in your `package.json`.

Add the following lines to your `.gitignore`
```
/node_modules
/package-lock.json
```

Create a `.eslintignore` file with the following lines
```
/community-store
/ViisonCommon
/vendor
```

Create a `package.json` file with the following lines and change it to your plugin
```
{
    "name": "shopware-my-plugin",
    "description": "The plugin that adds MyPlugin functionality to Shopware.",
    "author": "VIISON",
    "license": "UNLICENSED",
    "private": true,
    "scripts": {
        "eslint": "eslint .",
        "eslint:fix": "eslint --fix ."
    },
    "devDependencies": {
        "viison-style-guide": "github:VIISON/style-guide#semver:*"
    },
    "repository": {
        "type": "git",
        "url": "git+https://github.com/VIISON/ShopwareMyPlugin.git"
    },
    "bugs": {
        "url": "https://github.com/VIISON/ShopwareMyPlugin/issues"
    },
    "homepage": "https://github.com/VIISON/ShopwareMyPlugin#readme"
}

```

Create a `.eslintrc.js` file and configure it.

## Configure .eslintrc.js

To utilize Modern Javascript (ES2016 and beyond) *ES6* linting
```
module.exports = require('viison-style-guide/javascript/eslintrc');
```


To utilize Legacy *ES5* linting
```
module.exports = require('viison-style-guide/javascript/legacy/eslintrc');
```

To utilize our custom Shopware Plugin linting
```
module.exports = {
    extends: "./node_modules/viison-style-guide/javascript/shopware-plugin/eslintrc.js"
};
```

# ESLint usage

For detailed information see [ESLint Getting Started Guide](https://eslint.org/docs/user-guide/getting-started)
To check all of your JS files, run the following command in your plugin root directory.
```
npm run eslint
```
