<?php

namespace IWGB\Join;

use Doctrine\ORM\EntityManager;
use Guym4c\Airtable\Airtable;
use Monolog\Logger;
use Slim\Views\Twig;
use SlimSession\Helper;

class TypeHinter {

    /** @var $settings array */
    public $settings;

    /** @var $log Logger */
    public $log;

    /** @var $em EntityManager */
    public $em;

    /** @var $airtable Airtable */
    public $airtable;

    /** @var Helper */
    public $session;

    /** @var Twig */
    public $view;

}