<?php
/**
 * Created by PhpStorm.
 * User: marclafay
 * Date: 05/02/2018
 * Time: 19:13
 */

namespace WonderWp\Theme\Child\Components\Timeline\TimelineItem;

use WonderWp\Theme\Core\Component\TwigComponent;

class TimelineItem extends TwigComponent
{

    protected $date;
    protected $text;
    protected $img;
    protected $alt;

    public function __construct()
    {
        parent::__construct(__DIR__, 'timeline-item');
    }

}