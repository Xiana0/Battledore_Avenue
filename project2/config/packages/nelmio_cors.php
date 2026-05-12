<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use function PHPMaker2026\Project1\Config;

return App::config([
    'nelmio_cors' => [
        // The options defined under defaults are the default values applied to all the paths that match,
        // unless overridden in a specific URL configuration. If you want them to apply to everything,
        // you must define a path with ^/.
        // See https://symfony.com/bundles/NelmioCorsBundle/current/index.html#configuration
        'defaults' => Config('CORS'),

        // Example:
        // 'paths' => [
        //     '^/api/' => [
        //         'allow_origin' => ['*'],
        //         'allow_headers' => ['X-Custom-Auth'],
        //         'allow_methods' => ['POST', 'PUT', 'GET', 'DELETE'],
        //         'max_age' => 3600,
        //     ],
        //     '^/' => [
        //         'origin_regex' => true,
        //         'allow_origin' => ['^http://localhost:[0-9]+'],
        //         'allow_headers' => ['X-Custom-Auth'],
        //         'allow_methods' => ['POST', 'PUT', 'GET', 'DELETE'],
        //         'max_age' => 3600,
        //         'hosts' => ['^api\\.'],
        //     ],
        // ],
    ],
]);
