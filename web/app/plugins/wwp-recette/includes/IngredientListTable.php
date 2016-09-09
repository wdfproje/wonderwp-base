<?php
/**
 * Created by PhpStorm.
 * User: jeremydesvaux
 * Date: 08/09/2016
 * Time: 15:24
 */

namespace WonderWp\Plugin\Recette;

use WonderWp\APlugin\ListTable as WwpListTable;

class IngredientListTable extends WwpListTable{

    public function column_action($item, $allowedActions = array('edit', 'delete'), $givenEditParams = array(), $givenDeleteParams = array())
    {
        $givenEditParams['action'] = 'editIngredient';
        $givenEditParams['tab'] = 2;
        $givenDeleteParams['action'] = 'deleteIngredient';
        parent::column_action($item, $allowedActions, $givenEditParams, $givenDeleteParams); // TODO: Change the autogenerated stub
    }

}