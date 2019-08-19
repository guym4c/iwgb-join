<?php

namespace IWGB\Join\Provider;

use Doctrine\Common\Cache\FilesystemCache;
use Guym4c\Airtable\Airtable;
use IWGB\Join\TypeHinter;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class AirtableProvider implements ServiceProviderInterface {

    /**
     * {@inheritdoc}
     */
    public function register(Container $c) {

        $c['airtable'] = function (Container $c): Airtable {

            /** @var $c TypeHinter */
            return new Airtable($c->settings['airtable']['key'], $c->settings['airtable']['base'],
                new FilesystemCache(APP_ROOT . '/var/cache/airtable'),
                ['Branches', 'Plans', 'Job types']);
        };
    }
}