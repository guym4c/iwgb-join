<?php

namespace IWGB\Join;

use Buzz\Browser;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;

class TypeHinter {

    /** @var $http Browser */
    public $http;

    /** @var $settings array */
    public $settings;

    /** @var $log Logger */
    public $log;

    /** @var $em EntityManager */
    public $em;

}