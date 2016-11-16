<?php
/**
 * Created by PhpStorm.
 * User: jeremydesvaux
 * Date: 25/08/2016
 * Time: 17:02
 */

namespace WonderWp\Plugin\Jeux;

use Symfony\Component\HttpFoundation\Cookie;
use WonderWp\APlugin\AbstractManager;
use WonderWp\APlugin\AbstractPluginManager;
use WonderWp\DI\Container;
use WonderWp\Hooks\AbstractHookService;
use WonderWp\HttpFoundation\Request;

/**
 * Class JeuxHookService
 * @package WonderWp\Plugin\Jeux
 * Defines the different hooks that are going to be used by your plugin
 */
class JeuxHookService extends AbstractHookService
{

    /**
     * Run
     * @return $this
     */
    public function run()
    {

        //Get Manager
        $container = Container::getInstance();
        $this->_manager = $container->offsetGet('wwp-jeux.Manager');

        /*
         * Admin Hooks
         */
        //Menus
        add_action('admin_menu', array($this, 'customizeMenus'));

        //Translate
        add_action('plugins_loaded', array($this, 'loadTextdomain'));
        add_action('plugins_loaded', array($this, 'setDejaJoue'));

        return $this;
    }

    /**
     * Add entry under top-level functionalities menu
     */
    public function customizeMenus()
    {

        //Get admin controller
        $adminController = $this->_manager->getController(AbstractManager::$ADMINCONTROLLERTYPE);
        $callable = array($adminController, 'route');

        //Add entry under top-level functionalities menu
        add_submenu_page('wonderwp-modules', 'Jeux', 'Jeux', 'read', WWP_PLUGIN_JEUX_NAME, $callable);

    }

    /**
     * Load Textdomain
     */
    public function loadTextdomain()
    {
        $languageDir = $this->_manager->getConfig('path.base') . '/languages/';
        load_plugin_textdomain($this->_manager->getConfig('textDomain'), false, $languageDir);
    }

    public function setDejaJoue()
    {
        $request = Request::getInstance();
        $dejaJoueSession = $request->getSession()->get('wwp-jeux-deja-joue');

        if (!empty($dejaJoueSession)) {
            foreach ($dejaJoueSession as $i=>$dejaJoue) {
                $cookie = new Cookie($dejaJoue[0], true, $dejaJoue[1]);
                setcookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime());
            }
            $request->getSession()->set('wwp-jeux-deja-joue',[]);
        }
    }

}