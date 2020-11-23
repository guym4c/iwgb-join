<?php

namespace Iwgb\Join\Provider;

use Handlebars\Handlebars;
use Handlebars\Loader\FilesystemLoader;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class HandlebarsViewProvider implements ServiceProviderInterface {

    /**
     * {@inheritdoc}
     */
    public function register(Container $c) {
        $loader = new FilesystemLoader(APP_ROOT . '/view', ['extension' => 'hbs']);
        $c[Provider::VIEW] = fn (): Handlebars => new Handlebars([
            'loader' => $loader,
            'partials_loader' => $loader,
        ]);
    }
}