<?php

namespace Iwgb\Join\Provider;

use Aura\Session\Session;
use Aura\Session\SessionFactory;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class AuraSessionProvider implements ServiceProviderInterface {

    /**
     * @inheritDoc
     */
    public function register(Container $c) {
        session_name('IwgbMemberSession');
        $c[Provider::SESSION] = fn(): Session => (new SessionFactory())->newInstance($_COOKIE);
    }
}