<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use function PHPMaker2026\Project1\Config;
$config = [];
foreach (['dev', 'prod'] as $env) {
    $handlers = Config('MONOLOG')[$env]['handlers'] ?? [];
    $config["when@{$env}"] = [
        'monolog' => [
            'channels' => ['deprecation', 'doctrine'], // Ensure doctrine channel registered
            'handlers' => $handlers,
        ],
    ];
}

return App::config($config);
