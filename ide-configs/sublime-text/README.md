# Sublime Text configuration

This folder contains information on how to configure the Sublime Text 2/3 PHP Code Sniffer. Please make sure to install [`sublime-phpcs`](http://benmatselby.github.io/sublime-phpcs/) using the Sublime package manager as well as its required dependencies first (also see `README.md`).

## `sublime-phpcs` config for plugin development

*Note: The following step-by-step guide was created for OS X. If you run Sublime on any other OS you might have to adjust some paths etc.*

1. Check out `VIISON/style-guide` somewhere to your home directory (e.g. on OS X to `~/Documents/VIISON/style-guide`)
2. In Sublime open the `User` package settings of `PHP Code Sniffer` via *Sublime Text > Preferences > Package Settings > PHP Code Sniffer > Settings – User*
3. Replace the contents of the file with the contents of `VIISON_Shopware_Plugin_phpcs.sublime-settings` and adjust the paths as necessary
