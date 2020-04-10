<?php

namespace Iwgb\Join\Handler;

use Aura\Session\Segment;
use Slim\Container;

abstract class AbstractSessionValidationHandler extends RootHandler {

    private const KEY = 'session';
    private const VALUE = '✓';

    private Segment $session;

    public function __construct(Container $c) {
        parent::__construct($c);

        $this->session = $this->sm->getSegment(self::class);
    }

    public function init(): void {
        $this->session->setFlash(self::KEY, self::VALUE);
    }

    public function validate(): bool {
        return $this->session->getFlash(self::KEY) === self::VALUE;
    }

}