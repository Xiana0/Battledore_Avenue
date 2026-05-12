<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use function PHPMaker2026\Project1\Config;
$sessionConfig = [
    'cookie_path' => Config('COOKIE_PATH'),
    'cookie_lifetime' => Config('COOKIE_LIFETIME'),
    'cookie_samesite' => strtolower(Config('COOKIE_SAMESITE')), // Symfony expects lowercase
    'cookie_httponly' => Config('COOKIE_HTTP_ONLY'),
    'cookie_secure' => Config('COOKIE_SECURE'),
];
if (Config('SESSION_TIMEOUT') > 0) {
    $sessionConfig['gc_maxlifetime'] = Config('SESSION_TIMEOUT') * 60;
}

return App::config([
    'framework' => array_replace_recursive([
        'secret' => '%env(APP_SECRET)%',
        'session' => $sessionConfig,
        'property_access' => [
            'magic_call' => false,
            'magic_get' => true,
            'magic_set' => true,
            'throw_exception_on_invalid_index' => false,
            'throw_exception_on_invalid_property_path' => false,
        ],
        'csrf_protection' => Config('CSRF_PROTECTION'),
        'esi' => true,
        'fragments' => true,
        'default_locale' => str_replace('-', '_', Config('DEFAULT_LANGUAGE_ID')),
        'translator' => [
            'default_path' => '%kernel.project_dir%/translations',
            'fallbacks' => ['en'],
        ],
    ], Config('FRAMEWORK')),
]);
