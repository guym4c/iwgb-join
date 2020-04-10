<?php

namespace Iwgb\Join\Provider;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\Setup;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class DoctrineOrmProvider implements ServiceProviderInterface {

    /**
     * {@inheritdoc}
     */
    public function register(Container $c) {

        $c['em'] = function (Container $c): EntityManager {
            $config = Setup::createAnnotationMetadataConfiguration(
                [$c['settings']['doctrine']['entityDir']],
                $c['settings']['doctrine']['dev_mode']);

            /** @noinspection PhpParamsInspection */
            $config->setMetadataDriverImpl(
                new AnnotationDriver(
                    new AnnotationReader,
                    $c['settings']['doctrine']['entityDir']
                )
            );

            $config->setMetadataCacheImpl(
                new FilesystemCache(APP_ROOT . '/var/doctrine',));

            return EntityManager::create(
                $c['settings']['doctrine']['connection'],
                $config);
        };
    }
}