<?php

namespace IWGB\Join\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Slim\Views\TwigExtension;
use Twig\Extension\DebugExtension;
use Twig\TwigFilter;

class Twig implements ServiceProviderInterface {

    /**
     * {@inheritdoc}
     */
    public function register(Container $c) {

        $c['view'] = function (Container $c): \Slim\Views\Twig {
            /* @var $c \Slim\Container */
            $templates = $c['settings']['twig']['templates_dir'];
            $cache = $c['settings']['twig']['cache_dir'];
            $debug = $c['settings']['twig']['debug'];

            $view = new \Slim\Views\Twig($templates, compact('cache', 'debug'));

            // Filters

            $env = $view->getEnvironment();

            $env->addFilter(new TwigFilter('htmlentities', function (string $s): string {
                return htmlentities($s, ENT_QUOTES, "UTF-8");
            }, ['is_safe' => ['html']]));

            if ($debug) {
                $view->addExtension(new TwigExtension(
                    $c['router'],
                    $c['request']->getUri()
                ));
                $view->addExtension(new DebugExtension());
            }
            return $view;
        };
    }
}