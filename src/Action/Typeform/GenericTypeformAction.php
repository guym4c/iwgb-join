<?php

namespace IWGB\Join\Action\Typeform;

use Guym4c\TypeformAPI\Typeform;
use IWGB\Join\Action\GenericAction;
use Slim\Container;

abstract class GenericTypeformAction extends GenericAction {

    protected $typeform;

    public function __construct(Container $c) {
        parent::__construct($c);

        $this->typeform = new Typeform($this->settings['typeform']['api']);
    }
}