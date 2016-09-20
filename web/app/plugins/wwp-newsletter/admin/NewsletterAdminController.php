<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://digital.wonderful.fr
 * @since      1.0.0
 *
 * @package    Wonderwp
 * @subpackage Wonderwp/admin
 */

namespace WonderWp\Plugin\Newsletter;

use WonderWp\APlugin\AbstractPluginBackendController;
use WonderWp\APlugin\ListTable;
use WonderWp\DI\Container;
use WonderWp\HttpFoundation\Request;
use WonderWp\Notification\AdminNotification;
use WonderWp\Templates\Views\VueFrag;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wonderwp
 * @subpackage Wonderwp/admin
 * @author     Wonderful <jeremy.desvaux@wonderful.fr>
 */
class NewsletterAdminController extends AbstractPluginBackendController
{

    /**
     * Create here the method to call for you different routes
     */

    public function getTabs()
    {
        $tabs = array(
            1 => array('action' => 'list', 'libelle' => 'Listes de diffusions'),
            2 => array('action' => 'options', 'libelle' => 'Paramètres')
        );
        return $tabs;
    }

    public function listAction(ListTable $listTableInstance = null)
    {
        $passerelle = get_option("wwp-newsletter_passerelle");
        if (empty($passerelle)) {
            $request = Request::getInstance();
            $params = array(
                'page' => $request->get('page'),
                'action' => 'options',
                'tab'=>2
            );
            $optPage = admin_url('/admin.php?' . http_build_query($params));
            \WonderWp\redirect($optPage);
        } else {
            parent::listAction($listTableInstance); // TODO: Change the autogenerated stub
        }
    }

    public function optionsAction()
    {
        $container = Container::getInstance();

        $notification = null;
        $request = Request::getInstance();
        if ($request->getMethod() == 'POST') {
            $notification = new AdminNotification('success', 'Options mises à jour');
        }

        $shortname = 'wwp-newsletter';

        /** @var NewsletterPasserelleService $passerelleService */
        $passerelleService = $this->_manager->getService('passerelle');
        $passerelles = $passerelleService->getPasserelles();
        $passerellesChoice = array();
        $passerellesOpts = array();

        if (!empty($passerelles)) {
            foreach ($passerelles as $passerelleClass) {
                /** @var PasserelleInterface $passerelle */
                $shortClassName = (new \ReflectionClass($passerelleClass))->getShortName();
                $escapedClassName = str_replace('\\','\\\\',$passerelleClass);
                $passerellesChoice[$escapedClassName] = str_replace('Passerelle','',$shortClassName);
                $passerelle = new $passerelleClass();
                $passerellesOpts = array_merge($passerellesOpts,$passerelle->getOptions());
            }
        }
        $std = array_keys($passerellesChoice);
        $std = reset($std);

        $defaultOptions = array(

            array(
                "name" => __('Choix de la passerelle'),
                "id" => $shortname . "_passerelle",
                "type" => "radio",
                "options" => $passerellesChoice,
                'std' => $std
            )

        ) + $passerellesOpts;

        $entityManager = Container::getInstance()->offsetGet('entityManager');
        $repo = $entityManager->getRepository(NewsletterEntity::class);
        $lists = $repo->findAll();
        $listOpts = array(''=>'Choisissez la liste dont on affichera le formulaire d\'inscription');
        if(!empty($lists)){ foreach ($lists as $list){
            /** @var $list NewsletterEntity */
            $listOpts[$list->getId()] = $list->getTitle();
        } }

        $defaultOptions[] = array(
            "name" => __("Inscription globale"),
            "id" => $shortname . "_autoload_listform",
            "type" => "select",
            "options" => $listOpts,
            'std' => reset($listOpts),
            "desc"=>"Vous pouvez choisir une liste dont le formulaire d'inscription sera présent sur toutes les pages"
        );

        $prefix = $this->_manager->getConfig('prefix');
        $vue = $container->offsetGet('wwp.views.optionsAdmin')
            ->registerFrags($prefix)
            ->render([
                'title' => 'Options du module ' . get_admin_page_title(),
                'tabs' => $this->getTabs(),
                'options' => $defaultOptions,
                'notification' => $notification
            ]);
    }

}
