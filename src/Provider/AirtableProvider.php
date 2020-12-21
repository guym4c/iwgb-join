<?php

namespace Iwgb\Join\Provider;

use Doctrine\Common\Cache\FilesystemCache;
use Guym4c\Airtable\Airtable;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class AirtableProvider implements ServiceProviderInterface {

    /**
     * {@inheritdoc}
     */
    public function register(Container $c) {
        $c[Provider::AIRTABLE] = fn (): Airtable =>
            new Airtable($c[Provider::SETTINGS]['airtable']['key'], $c[Provider::SETTINGS]['airtable']['base'],
                new FilesystemCache(APP_ROOT . '/var/cache/airtable'),
                ['Branches', 'Plans', 'Job types'],
                'https://outbound.iwgb.org.uk/v0',
                [
                    'X-Proxy-Auth' => $c[Provider::SETTINGS]['airtable']['proxyKey'],
                    'X-Proxy-Destination-Key' => 'airtable',
                ],
                false
            );
    }
}