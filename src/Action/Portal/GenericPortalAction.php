<?php

namespace IWGB\Join\Action\Portal;

use IWGB\Join\Action\GenericAction;
use IWGB\Join\TypeHinter;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Slim\Http\Response;

abstract class GenericPortalAction extends GenericAction {

    protected $view;

    public function __construct(Container $c) {
        parent::__construct($c);

        /** @var TypeHinter $c */
        $this->view = $c->view;
    }

    protected function render(Response $response, string $template, array $vars): ResponseInterface {

        // TODO

        return $this->view->render($response, $template, array_merge($vars, [

        ]));
    }
}