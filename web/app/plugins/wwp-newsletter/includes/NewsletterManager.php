<?php

namespace WonderWp\Plugin\Newsletter;

//Must uses
use \Composer\Autoload\ClassLoader as AutoLoader; //Must use the autoloader
use Pimple\Container as PContainer;
use WonderWp\APlugin\AbstractManager;
use WonderWp\APlugin\AbstractPluginManager;
use WonderWp\DI\Container;

/**
 * Class NewsletterManager
 * @package WonderWp\Plugin\Newsletter
 * The manager is the file that registers everything your plugin is going to use / need.
 * It's the most important file for your plugin, the one that bootstraps everything.
 * The manager registers itself with the DI container, so you can retrieve it somewhere else and use its config / controllers / services
 */
class NewsletterManager extends AbstractPluginManager{

    /**
     * Register AutoLoad dependencies for this plugin.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     */
    public function autoLoad(AutoLoader $loader){

        $pluginDir = plugin_dir_path( dirname( __FILE__ ) );
        $loader->addPsr4('WonderWp\\Plugin\\Newsletter\\',array(
            $pluginDir . 'includes',
            $pluginDir . 'admin',
            $pluginDir . 'public',
        ));

    }

    /**
     * Registers config, controllers, services etc usable by the plugin components
     * @param PContainer $container
     * @return $this
     */
    public function register(PContainer $container)
    {
        parent::register($container);

        //Register Config
        $this->setConfig('path.root',plugin_dir_path( dirname( __FILE__ ) ));
        $this->setConfig('path.base',dirname( dirname( plugin_basename( __FILE__ ) ) ));
        $this->setConfig('path.url',plugin_dir_url( dirname( __FILE__ ) ));
        $this->setConfig('entityName',NewsletterEntity::class);
        $this->setConfig('textDomain',WWP_NEWSLETTER_TEXTDOMAIN);

        //Register Controllers
        $this->addController(AbstractManager::$ADMINCONTROLLERTYPE,function(){
            return new NewsletterAdminController( $this );
        });
        /*$container[$this->plugin_name.'.publicController'] = function() {
            return $plugin_public = new NewsletterPublicController($this);
        };*/

        //Register Services
        $this->addService(AbstractManager::$HOOKSERVICENAME,$container->factory(function($c){
            //Hook service
            return new NewsletterHookService();
        }));
        $this->addService(AbstractManager::$MODELFORMSERVICENAME,$container->factory(function($c){
            //Model Form service
            return new NewsletterForm();
        }));
        $this->addService(AbstractManager::$LISTTABLESERVICENAME, function($container){
            //List Table service
            return new NewsletterListTable();
        });
        /* //Uncomment this if your plugin has assets
        $this->addService(AbstractManager::$ASSETSSERVICENAME,function(){
            //Asset service
            return new NewsletterAssetService();
        });*/

        return $this;
    }

}