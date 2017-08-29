# Configure ESLint for Shopware Plugins

Add the following lines to your `.gitignore`
```
/node_modules
/package-lock.json
```

Create an `.eslintignore` file with the following lines
```
/community-store
/ViisonCommon
/vendor
```

Create a `package.json` file with the following lines and modify it to match your plugin
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

Create an `.eslintrc.js` file and insert the following lines
```
module.exports = {
    extends: "./node_modules/viison-style-guide/javascript/shopware-plugin/eslintrc.js"
};
```

# Configure ESLint for other JavaScript projects

Include `"viison-style-guide": "git+https://github.com/VIISON/style-guide.git"` in devDependencies in your `package.json`.

Create an `.eslintrc.js` file and insert one of the following configuration lines

To lint projects using modern JavaScript (ES2015 and beyond)
```
module.exports = require('viison-style-guide/javascript/eslintrc');
```

To lint projects using legacy ES5
```
module.exports = require('viison-style-guide/javascript/legacy/eslintrc');
```

# ESLint usage

For detailed information see [ESLint Getting Started Guide](https://eslint.org/docs/user-guide/getting-started)
To check all of your JS files, run the following command in your plugin root directory.
```
npm run eslint
```
