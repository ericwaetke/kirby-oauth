<?php

@include_once __DIR__ . '/vendor/autoload.php';

load([
     'Thathoff\\Oauth\\Controller' => 'lib/Controller.php',
     'Thathoff\\Oauth\\ProvidersManager' => 'lib/ProvidersManager.php',
     'Thathoff\\Oauth\\Provider' => 'lib/Provider.php',
     'Thathoff\\Oauth\\Auth\\OauthMethod' => 'lib/Auth/OauthMethod.php',
], __DIR__);

Kirby::plugin('thathoff/oauth', [
    'authMethods' => [
        'oauth' => \Thathoff\Oauth\Auth\OauthMethod::class,
    ],
    'hooks' => [
        // Enable the oauth login method by default, unless the
        // site has configured the `auth.methods` option itself.
        'route:before' => function (): void {
            \Thathoff\Oauth\Auth\OauthMethod::applyDefaultOptions();
        },
    ],
    'routes' => include(__DIR__ . '/plugin/routes.php'),
    'options' => include(__DIR__ . '/plugin/options.php'),
    'api' => include(__DIR__ . '/plugin/api.php'),
    'translations' => include(__DIR__ . '/plugin/translations.php'),
]);
