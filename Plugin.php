<?php namespace Waka\Tbser;

use Backend;
use System\Classes\PluginBase;
use Lang;

/**
 * tbser Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * @var array Plugin dependencies
     */
    public $require = [
        'Waka.Utils',
    ];
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'tbser',
            'description' => 'No description provided yet...',
            'author'      => 'waka',
            'icon'        => 'icon-leaf'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
        \DataSources::registerDataSources(plugins_path().'/waka/tbser/config/datasources.yaml');

    }

    public function bootPackages()
    {
        // Get the namespace of the current plugin to use in accessing the Config of the plugin
        $pluginNamespace = str_replace('\\', '.', strtolower(__NAMESPACE__));

        // Instantiate the AliasLoader for any aliases that will be loaded
        $aliasLoader = AliasLoader::getInstance();

        // Get the packages to boot
        $packages = Config::get($pluginNamespace . '::packages');

        // Boot each package
        foreach ($packages as $name => $options) {
            // Setup the configuration for the package, pulling from this plugin's config
            if (!empty($options['config']) && !empty($options['config_namespace'])) {
                Config::set($options['config_namespace'], $options['config']);
            }

            // Register any Service Providers for the package
            if (!empty($options['providers'])) {
                foreach ($options['providers'] as $provider) {
                    App::register($provider);
                }
            }

            // Register any Aliases for the package
            if (!empty($options['aliases'])) {
                foreach ($options['aliases'] as $alias => $path) {
                    $aliasLoader->alias($alias, $path);
                }
            }
        }
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return []; // Remove this line to activate

        return [
            'Waka\Tbser\Components\MyComponent' => 'myComponent',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'waka.tbser.admin.super' => [
                'tab' => 'Waka - Tbser',
                'label' => 'Super Administrateur de Tbser',
            ],
            'waka.tbser.admin.base' => [
                'tab' => 'Waka - Tbser',
                'label' => 'Administrateur de Tbser',
            ],
            'waka.tbser.user' => [
                'tab' => 'Waka - Tbser',
                'label' => 'Utilisateur de Tbser',
            ],
        ];
    }

    public function registerModelToClean()
    {
        return [
            'cleanSoftDelete' => [
                \Waka\Tbser\Models\Presentation::class => 0,
            ],
        ];
    }

    public function registerWakaRules()
    {
        return [
            'asks' => [
                ['\Waka\Tbser\WakaRules\Asks\chartOpenXml',  'onlyClass' => ['presentation']],
            ],
            'fncs' => [],
            'conditions' => [],
        ];
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
    public function registerSettings()
    {
        return [
            'presentations' => [
                'label' => Lang::get('waka.tbser::lang.menu.presentations'),
                'description' => Lang::get('waka.tbser::lang.menu.presentations_description'),
                'category' => Lang::get('waka.utils::lang.menu.settings_category_model'),
                'icon' => 'wicon-clipboard-move',
                'url' => Backend::url('waka/tbser/presentations'),
                'permissions' => ['waka.tbser.admin.*'],
                'order' => 10,
            ],
        ];
    }
}
