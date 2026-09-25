<?php

/**
 * This file is part of Galette Maps plugin (https://galette.eu).
 * SPDX-FileCopyrightText: Copyright © 2012-2026 The Galette Team
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

declare(strict_types=1);

use Galette\Middleware\Authenticate;
use GaletteMaps\Controllers\MapsController;

//Constants and classes from plugin
require_once $module['root'] . '/_config.inc.php';

$check_js_middleware = function (\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Server\RequestHandlerInterface $handler) use ($container) {
    //check if JS has been generated
    if (!file_exists(__DIR__ . '/webroot/maps-main.bundle.min.js')) {
        $container->get(\Slim\Flash\Messages::class)->addMessageNow(
            'error_detected',
            _T('Javascript libraries has not been built!', 'maps')
        );
    }
    return $handler->handle($request);
};

/*$app->addMiddleware(function (\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Server\RequestHandlerInterface $handler) use ($container) {
    //check if JS has been generated
    if (!file_exists(__DIR__ . '/webroot/maps-main.bundle.min.js')) {
        $container->get('flash')->addMessageNow(
            'error_detected',
            _T('Javascript libraries has not been built!', 'maps')
        );
    }
    return $handler->handle($request);
});*/

$app->get(
    '/localize-member/{id:\d+}',
    [MapsController::class, 'localizeMember']
)->setName('maps_localize_member')->add(Authenticate::class)->add($check_js_middleware);

//member self localization
$app->get(
    '/localize-me',
    [MapsController::class, 'localizeMember']
)->setName('maps_mymap')->add(Authenticate::class)->add($check_js_middleware);

//global map page
$app->get(
    '/map',
    [MapsController::class, 'map']
)->setName('maps_map')->add($check_js_middleware)->add(\Galette\Middleware\PublicPages::class);

$app->get(
    '/preferences',
    [MapsController::class, 'preferences']
)->setName('maps_preferences')->add(Authenticate::class);

$app->post(
    '/preferences',
    [MapsController::class, 'storePreferences']
)->setName('maps_store_preferences')->add(Authenticate::class);

$app->post(
    '/i-live-here[/{id:\d+}]',
    [MapsController::class, 'ILiveHere']
)->setName('maps_ilivehere')->add(Authenticate::class)->add($check_js_middleware);
