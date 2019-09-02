## Artisan helpers

A small utility to give you a nice extra artisan command to play with during your development cycles. This is intended for development only, for use on your development PC. Do not include this in your live distributions.

Right now there is one command, pluginversionshift, which is intended for preventing lengthy seeding cycles plugin:refresh might encumber you with. Sometimes you just wish to check out one little change and not spend 5 minutes or longer waiting for the seeding to complete, or lose all that testing data you just set up because you don't have a seeder for that plugin with test data.

More utilities may follow, if you have suggestions, leave me a comment or open an issue on the repository at https://github.com/tschallacka/octobercms-artisan-helpers

Please check out the documentation tab for the relevant commands.

## Commands

These commands are to be executed in the root directory of your October installation, in a shell, at the same location where you'd execute the commands laid out in https://octobercms.com/docs/console/commands#plugin-install-command

    php artisan tschallacka:pluginversionshift AuthorName.PluginName up

This will push the given plugin UP one version. From version 1.0.1 to 1.0.2. If there is no higher version then the currently active one it will show:

You know how they say, there's no way but up? well, not anymore.

    php artisan tschallacka:pluginversionshift AuthorName.PluginName down

This will push the given plugin DOWN one version. From version 1.0.2 to 1.0.1 If there is no lower version it show:

You're at the bottom... there is no more down.

Be careful with this, if you run a down then up and it fails and in the next cycle wish to repeat it, remember that the up didn't complete so you're still at the down version.

     php artisan tschallacka:pluginversionshift AuthorName.PluginName reload
     php artisan tschallacka:pluginversionshift AuthorName.PluginName refresh

Executes down() and then up() to reload the current version.

If it fails because of an error, you'll have to execute up.

At the end of an upgrade cycle it will always attempt to output the current version number.

> done. Current version is now at 1.0.1

    php artisan tschallacka:pluginversionshift --help

outputs the command help
