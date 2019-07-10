<?php

namespace IWGB\Join\Action\GoCardless;

use GoCardlessPro;
use GoCardlessPro\Environment;
use IWGB\Join\Action\GenericAction;
use IWGB\Join\TypeHinter;
use Slim\Container;

abstract class GenericGoCardlessAction extends GenericAction {

    protected $gocardless;

    public function __construct(Container $c) {
        parent::__construct($c);

        /** @var $c TypeHinter */
        $this->gocardless = new GoCardlessPro\Client([
            'access_token' => $c->settings['gocardless']['accessToken'],
            'environment' => $c->settings['dev']
                ? Environment::SANDBOX
                : Environment::LIVE,
        ]);
    }

}