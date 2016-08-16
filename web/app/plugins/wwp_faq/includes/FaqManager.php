<?php

namespace WonderWp\Plugin\Faq; //Correct namespace

//Must uses
use \Composer\Autoload\ClassLoader as AutoLoader; //Must use the autoloader
use Pimple\Container as PContainer;
use WonderWp\APlugin\AbstractPluginManager;
use WonderWp\DI\Container;

class FaqManager extends AbstractPluginManager{

    /**
     *
     * Register AutoLoad dependencies for this plugin.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     */
    public function autoLoad(AutoLoader $loader){

        $pluginDir = plugin_dir_path( dirname( __FILE__ ) );
        $loader->addPsr4('WonderWp\\Plugin\\Faq\\',array(
            $pluginDir . 'includes',
            $pluginDir . 'admin',
            $pluginDir . 'public',
        ));

    }

    public function register(PContainer $container)
    {
        parent::register($container);

        $container[$this->plugin_name.'.adminController'] = function(){
            return new FaqAdminController( $this->get_plugin_name(), $this->get_version() );
        };
        /*$container[$this->plugin_name.'.publicController'] = function() {
            return $plugin_public = new PublicController($this->get_plugin_name(), $this->get_version());
        };*/

        $container[$this->plugin_name.'.wwp.entityName'] = FaqEntity::class;
        $container[$this->plugin_name.'wwp.forms.modelForm'] = $container->factory(function($c){
            return new FaqForm();
        });
        $container[$this->plugin_name.'.wwp.listTable.class'] = function($container){
            return new FaqListTable(array(
                'entityName'=>FaqEntity::class,
                'textdomain'=>WWP_FAQ_TEXTDOMAIN
            ));
        };
        $container[$this->plugin_name.'.assetsService'] = function(){
            return new FaqAssetsService();
        };

        $container[$this->plugin_name.'.path.root'] = plugin_dir_path( dirname( __FILE__ ) );
        $container[$this->plugin_name.'.path.url'] = plugin_dir_url( dirname( __FILE__ ) );
    }

    public function getRouter()
    {
        return null;
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     */
    protected function define_admin_hooks($adminController) {
        //Admin pages
        $this->loader->add_action( 'admin_menu', $adminController, 'customizeMenus' );
    }

    public function loadTextdomain()
    {
        load_plugin_textdomain(WWP_FAQ_TEXTDOMAIN,false,dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/');
    }

    public function registerAssets(){
        $container = Container::getInstance();
        $assetsService = $container->offsetGet($this->plugin_name.'.assetsService');
        $assetsManager = $container->offsetGet('wwp.assets.manager');
        $assetClass = $container->offsetGet('wwp.assets.assetClass');
        $assetsService->registerAssets($assetsManager,$assetClass);
    }

}