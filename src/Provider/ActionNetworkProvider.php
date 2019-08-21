<?php

namespace IWGB\Join\Provider;

use Guym4c\ActionNetwork;
use IWGB\Join\TypeHinter;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ActionNetworkProvider implements ServiceProviderInterface {

    /**
     * {@inheritdoc}
     */
    public function register(Container $c) {
        $c['actionNetwork'] = function (Container $c): ActionNetwork\Client {
            /** @var $c TypeHinter */
            return new ActionNetwork\Client($c->settings['action-network']['token']);
        };
    }
}