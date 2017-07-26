# JetBrains IDE configuration

This folder contains config information for the JetBrains IntelliJ
family of IDEs:

* WebStorm
* PhpStorm
* AppCode
* IntelliJ

## PhpStorm config for plugin development

**Our custom coding standard requires at least `v3.0.2` of `phpcs`!**

* Check out style-guide below your project root (or somewhere else, but then you'll have to adjust the configuration accordingly)
* Install php-code-sniffer globally (using `brew`, `pear`, `composer`, ...)
* In PhpStorm's _Settings -> Languages & Frameworks -> PHP -> Code Sniffer_, specify the path where the `phpcs` script lives (usually `<install_dir>/scripts/phpcs`)
* Import the `VIISON_Shopware_Plugin_inspections.xml` inspection profile (_Editor -> Inspections_)
* Make sure the inspection _PHP -> PHP Code Sniffer validation_ has _Coding Standard: Custom_ set to the folder `php/php-codesniffer-standard/VIISON` in the `style-guide` repo.
* Go to _Settings -> Editor -> Code Style_ and import `VIISON Shopware plugin code style.xml`
* Install the latest version of nodejs (best use [nvm](https://github.com/creationix/nvm)
* Run `npm install` inside your working copy of `VIISON/style-guide`
* Configure the PhpStorm ESLint integration to point to your node installation and the `eslintrc.js` for Shopware plugins inside `style-guide`: ![image](https://user-images.githubusercontent.com/2817302/28622575-565ca8fc-7215-11e7-9d7c-582b920914b9.png)
