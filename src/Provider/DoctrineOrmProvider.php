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
        $c[Provider::ENTITY_MANAGER] = function (Container $c): EntityManager {
            $settings = $c[Provider::SETTINGS];

            $config = Setup::createAnnotationMetadataConfiguration(
                [$settings['doctrine']['entityDir']],
                $settings['doctrine']['dev_mode']);

            $config->setMetadataDriverImpl(
                new AnnotationDriver(
                    new AnnotationReader,
                    $settings['doctrine']['entityDir']
                )
            );

            $config->setMetadataCacheImpl(
                new FilesystemCache(APP_ROOT . '/var/doctrine',));

            return EntityManager::create(
                $settings['doctrine']['connection'],
                $config);
        };
    }
}