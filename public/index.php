<?php

use Iwgb\Join\Handler;
use Iwgb\Join\Middleware;
use Iwgb\Join\Route;
use Slim\App;
use Slim\Container;
use Teapot\StatusCode;

/** @var Container $c */
$c = require_once __DIR__ . '/../bootstrap.php';

$app = new App($c);

$app->add(new Middleware\RemoveTrailingSlashes($c));

$app->get('/health', Handler\Health::class);

$app->group('/join', function (App $app) use ($c) {

    $app->group('', function (App $app) {

        $app->get('/data', Handler\Typeform\RedirectToDataForm::class)
            ->setName(Route::CORE_DATA);
        $app->get('/branch', Handler\RecallBranch::class);
        $app->get('/pay', Handler\GoCardless\CreateRedirectFlow::class)
            ->setName(Route::INIT_PAYMENT);
        $app->get('/confirm', Handler\GoCardless\FlowSuccess::class)
            ->setName(Route::COMPLETE_PAYMENT);

    })->add(new Middleware\ApplicantSession($c));

    $app->get('/session', Handler\CreateApplication::class)
        ->setName(Route::CREATE_APPLICATION);

    $app->get('/{slug}', Handler\CreateSession::class);
});

$app->group('/callback', function (App $app) {

    $app->redirect('/gocardless/confirm', '/join/confirm', StatusCode::MOVED_PERMANENTLY);

    $app->post('/typeform/sorter', Handler\Typeform\Sorter::class);
    $app->post('/gocardless/event', Handler\GoCardless\GoCardlessEvent::class);
});

$app->group('/api', function (App $app) {

    $app->group('/onboarding', function (App $app) {

        $app->get('/jobtypes[/{id}]', Handler\Api\Onboarding\JobTypeProxy::class);
        $app->get('/plans', Handler\Api\Onboarding\PlanProxy::class);
        $app->post('/graphql', Handler\Api\Onboarding\GraphQLHandler::class);
    });

})->add(new Middleware\BearerAuthMiddleware($c));

/** @noinspection PhpUnhandledExceptionInspection */
$app->run();