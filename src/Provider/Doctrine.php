<?php

namespace IWGB\Join\Provider;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\Setup;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class Doctrine implements ServiceProviderInterface {

    /**
     * {@inheritdoc}
     */
    public function register(Container $c) {

        $c['em'] = function (Container $c): EntityManager {
            $config = Setup::createAnnotationMetadataConfiguration(
                $c['settings']['doctrine']['metadata_dirs'],
                $c['settings']['doctrine']['dev_mode']);

            $config->setMetadataDriverImpl(
                new AnnotationDriver(
                    new AnnotationReader,
                    $c['settings']['doctrine']['metadata_dirs']));

            $config->setMetadataCacheImpl(
                new FilesystemCache($c['settings']['doctrine']['cache_dir']));

            return EntityManager::create(
                $c['settings']['doctrine']['connection'],
                $config);
        };
    }
}