<?php

/**
 * Fired during plugin activation
 *
 * @link       http://digital.wonderful.fr
 * @since      1.0.0
 *
 * @package    Wonderwp
 * @subpackage Wonderwp/includes
 */

namespace WonderWp\Plugin\Translator;

use WonderWp\APlugin\AbstractPluginActivator;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wonderwp
 * @subpackage Wonderwp/includes
 * @author     Wonderful <jeremy.desvaux@wonderful.fr>
 */
class TranslatorActivator extends AbstractPluginActivator
{

    public function activate()
    {
        $this->_createTable('\WonderWp\Plugin\Translator\LangEntity');
        $this->_createTranslatorRole();
    }

    private function _createTranslatorRole(){
        global $wp_roles;

        //Nouveau profil Traducteur
        $translatorCapabilities = array(
            'read' => true
        );

        add_role('translator', 'Traducteur', $translatorCapabilities);
    }

}
