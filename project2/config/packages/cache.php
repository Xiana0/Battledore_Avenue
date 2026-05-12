<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use function PHPMaker2026\Project1\Config;

return App::config([
    'when@dev' => [
        'framework' => Config('CACHE.dev'),
    ],
    'when@prod' => [
        'framework' => Config('CACHE.prod'),
    ],
]);
