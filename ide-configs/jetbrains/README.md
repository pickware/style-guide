# JetBrains IDE configuration

This folder contains config information for the JetBrains IntelliJ
family of IDEs:

* WebStorm
* PhpStorm
* AppCode
* IntelliJ

## PHPStorm config for plugin development

* Check out style-guide below your project root (or somewhere else, but then you'll have to adjust the configuration accordingly)
* Install php-code-sniffer (using `brew`, `pear`, `composer`, ...)
* In PhpStorm's _Settings -> Languages & Frameworks -> PHP -> Code Sniffer_, specify the path where the `phpcs` script lives (usually `<install_dir>/scripts/phpcs`)
* Import the `VIISON_Shopware_Plugin_inspections.xml` inspection profile (Editor -> Inspections)
* Make sure the inspection _PHP -> PHP Code Sniffer validation_ has _Coding Standard: Custom_ set to the folder `php/php-codesniffer-standard/VIISON` in the `code-style` repo.
