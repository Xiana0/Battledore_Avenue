<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\Dotenv\Dotenv;

// Autoload
require_once 'vendor/autoload_runtime.php';

// Require files
require_once 'src/constants.php';
require_once 'src/phpfn.php';

// Load .env first
if (!isset($_ENV['APP_ENV']) && class_exists(Dotenv::class)) {
    (new Dotenv())->loadEnv(__DIR__ . '/.env');
}

// HTTP context
$httpContext = new HttpContext();

// Global code
require_once 'src/userfn.php';

// Display all errors (after Global code)
if (Config('REPORT_ALL_ERRORS')) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// Return callable for runtime component
return static function (array $context) use ($httpContext) {
    $kernel = new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
    $kernel->setHttpContext($httpContext);
    return $kernel;
};
