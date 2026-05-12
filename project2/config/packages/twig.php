<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'twig' => [
        // 'default_path' => '%kernel.project_dir%/templates', // Does not support %kernel.project_dir% //*** to be tested again
        'default_path' => __DIR__ . '/../../templates',
        'file_name_pattern' => '*.twig',
    ],
]);
