<?php

namespace Iwgb\Join\Handler\Typeform;

use Guym4c\TypeformAPI\Typeform;
use Iwgb\Join\Handler\RootHandler;
use Slim\Container;

abstract class GenericTypeformAction extends RootHandler {

    protected Typeform $typeform;

    public function __construct(Container $c) {
        parent::__construct($c);

        $this->typeform = new Typeform($this->settings['typeform']['api']);
    }
}