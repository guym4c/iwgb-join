<?php

namespace Iwgb\Join\Provider;

use Mustache_Engine;
use Mustache_Loader_FilesystemLoader as FilesystemLoader;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class MustacheTemplateProvider implements ServiceProviderInterface {

    /**
     * {@inheritdoc}
     */
    public function register(Container $c) {
        $c[Provider::VIEW] = fn (): Mustache_Engine => new Mustache_Engine([
            'loader' => new FilesystemLoader(APP_ROOT . '/view'),
            'entity_flags' => ENT_QUOTES,
        ]);
    }
}