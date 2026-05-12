<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use function PHPMaker2026\Project1\Config;

return App::config([
    'exercise_html_purifier' => [
        'default_cache_serializer_path' => '%kernel.project_dir%/var/cache/htmlpurifier',
        'html_profiles' => Config('HTML_PURIFIER'),
    ],
]);
