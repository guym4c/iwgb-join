<?php

namespace IWGB\Join;

use Doctrine\ORM\EntityManager;
use GraphQL\Doctrine\Helper\EntitySchemaBuilder;
use Guym4c\Airtable\Airtable;
use GuzzleHttp\Client;
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

    /** @var Twig */
    public $view;

    /** @var Client */
    public $http;

    /** @var Helper */
    public $session;

    /** @var EntitySchemaBuilder */
    public $graphql;

}