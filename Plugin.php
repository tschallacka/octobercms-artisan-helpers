<?php namespace Tschallacka\Artisan;

use Backend;
use System\Classes\PluginBase;

/**
 * Artisan Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Tschallacka\'s Artisan Tools',
            'description' => 'Artisan extensions to provide nice artisan commands for allowing you to go up and down in plugin versions without erasing the lot. 
Please node, it\'s intended for developers. Not for live distrobutions! More artisan commands may follow',
            'author'      => 'Tschallacka',
            'icon'        => 'icon-code'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {
    	$this->registerConsoleCommand('Tschallacka.PluginVersionShift','Tschallacka\Artisan\Console\PluginVersionShift');
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {

    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return []; // Remove this line to activate
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return []; // Remove this line to activate
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return []; 
    }
}
