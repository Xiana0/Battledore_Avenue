<?php
declare(strict_types=1);

namespace PHPMaker2026\Project1;

use Psr\Log\LoggerInterface;
use Psr\Link\EvolvableLinkProviderInterface;
use Monolog\Logger;
use Monolog\Level;
use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Events;
use Doctrine\ORM\Configuration as OrmConfiguration;
use Doctrine\DBAL\Configuration as DbalConfiguration;
use Doctrine\DBAL\Connection;
use Gedmo\Timestampable\TimestampableListener;
use Illuminate\Encryption\Encrypter;
use Detection\MobileDetect;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\Argument\ServiceLocatorArgument;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Runtime\SymfonyRuntime;
use Symfony\Component\WebLink\GenericLinkProvider;
use Symfony\Component\Notifier\Channel\EmailChannel;
use Symfony\Component\Notifier\FlashMessage\BootstrapFlashMessageImportanceMapper;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mailer\Transport\TransportFactoryInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mailer\Transport\Smtp\Auth\CramMd5Authenticator;
use Symfony\Component\Mailer\Transport\Smtp\Auth\LoginAuthenticator;
use Symfony\Component\Mailer\Transport\Smtp\Auth\PlainAuthenticator;
use Symfony\Component\Mailer\Transport\Smtp\Auth\XOAuth2Authenticator;
use Symfony\Component\Filesystem\Path;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Bridge\PsrHttpMessage\ArgumentValueResolver\PsrServerRequestResolver;
use Symfony\Bridge\PsrHttpMessage\EventListener\PsrResponseListener;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use ReflectionEnum;
use League\Flysystem\Filesystem;
use League\Flysystem\PathPrefixer;
use League\Flysystem\Local\LocalFilesystemAdapter;
use ParagonIE\CSPBuilder\CSPBuilder;
use Jose\Component\Checker\IssuedAtChecker;
use Jose\Component\Checker\ExpirationTimeChecker;
use Jose\Component\Checker\NotBeforeChecker;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service_locator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\abstract_arg;
use PHPMaker2026\Project1\Db;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Parameters
    $parameters = $containerConfigurator->parameters();

    // User level settings
    $file = __DIR__ . '/../src/userlevelsettings.php';
    if (file_exists($file)) {
        $config = require $file;
        foreach ($config as $key => $value) {
            $parameters->set($key, $value);
        }
    }

    // Email sender
    $parameters->set('app.notifications.email_sender', Config('SENDER_EMAIL'));

    // Other parameters
    $parameters->set('font.awesome.css', Config("FONT_AWESOME_STYLESHEET"));
    $parameters->set('adminlte.css', CssFile("adminlte.css"));
    $parameters->set('project.css', CssFile(Config("PROJECT_STYLESHEET_FILENAME")));
    $parameters->set('datetime.picker.css', CssFile("tempus-dominus.css"));

    // Links
    $parameters->set('app.links', [
        'css/select2.min.css?v=26.10.0' => ['as' => 'style'],
        'css/select2-bootstrap5.min.css?v=26.10.0' => ['as' => 'style'],
        '%font.awesome.css%?v=26.10.0' => ['as' => 'style'],
        'adminlte3/css/%adminlte.css%?v=fz2dcoaw' => ['as' => 'style'],
        'css/%project.css%?v=fz2dcoaw' => ['as' => 'style'],
        'js/jsrender.min.js?v=26.10.0' => ['as' => 'script'],
        'jquery/jquery.min.js?v=26.10.0' => ['as' => 'script'],
        'js/popper.min.js?v=26.10.0' => ['as' => 'script'],
        'js/luxon.min.js?v=26.10.0' => ['as' => 'script'],
        'js/ua-parser.min.js?v=26.10.0' => ['as' => 'script'],
        'js/purify.min.js?v=26.10.0' => ['as' => 'script'],
        'js/cropper.min.js?v=26.10.0' => ['as' => 'script'],
        'jquery/load-image.all.min.js?v=26.10.0' => ['as' => 'script'],
        'js/sweetalert2.min.js?v=26.10.0' => ['as' => 'script'],
        'css/sweetalert2.min.css?v=26.10.0' => ['as' => 'style'],
        'jquery/jquery-ui.min.js?v=26.10.0' => ['as' => 'script'],
        'css/jquery.fileupload.css?v=26.10.0' => ['as' => 'style'],
        'css/jquery.fileupload-ui.css?v=26.10.0' => ['as' => 'style'],
        'css/cropper.min.css?v=26.10.0' => ['as' => 'style'],
        'colorbox/jquery.colorbox.min.js?v=26.10.0' => ['as' => 'script'],
        'colorbox/colorbox.css?v=26.10.0' => ['as' => 'style'],
        'jquery/select2.full.min.js?v=26.10.0' => ['as' => 'script'],
        'jquery/jqueryfileupload.min.js?v=26.10.0' => ['as' => 'script'],
        'jquery/typeahead.jquery.min.js?v=26.10.0' => ['as' => 'script'],
        'js/pdfobject.min.js?v=26.10.0' => ['as' => 'script'],
        'bootstrap5/js/bootstrap.min.js?v=26.10.0' => ['as' => 'script'],
        'js/tippy.umd.min.js?v=26.10.0' => ['as' => 'script'],
        'css/tippy.css?v=26.10.0' => ['as' => 'style'],
        'adminlte3/js/adminlte.min.js?v=26.10.0' => ['as' => 'script'],
        'js/ew.min.js?v=26.10.0' => ['as' => 'script'],
        'js/userfn.js?v=26.10.0' => ['as' => 'script'],
        'js/userevent.js?v=26.10.0' => ['as' => 'script'],
        'js/clientscript.js?v=26.10.0' => ['as' => 'script'],
        'js/startupscript.js?v=26.10.0' => ['as' => 'script'],
        'css/%datetime.picker.css%' => ['as' => 'style'],
        'js/tempus-dominus.min.js?v=26.10.0' => ['as' => 'script'],
        'js/ewdatetimepicker.min.js?v=26.10.0' => ['as' => 'script'],
    ]);

    // Master/Detail
    $parameters->set('app.relations_config', Config('MASTER_DETAIL'));

    // Services
    $services = $containerConfigurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    // Core Container Alias
    $services->alias(ContainerInterface::class, 'service_container');

    // LoggingAuthenticatorManager as a decorator
    $services->set(LoggingAuthenticatorManager::class)
        ->arg('$inner', service('.inner'))
        ->decorate('security.authenticator.manager.main'); // Decorate the original service
    $services->set(ChartRendererInterface::class, ChartJsRenderer::class);
    $services->set(JwtRefresher::class)
        ->tag('app.locatable');
    $services->set(FileViewer::class)
        ->tag('app.locatable');

    // Export classes
    foreach (Config('EXPORT_CLASSES') as $key => $class) {
        $services->set($class);
        $services->alias("export.$key", $class);
    }

    // Report export classes
    foreach (Config('REPORT_EXPORT_CLASSES') as $key => $class) {
        $services->set($class);
        $services->alias("report.export.$key", $class);
    }

    // Export handler
    $services->set(ExportHandler::class)
        ->tag('app.locatable');

    // Chart exporter
    $services->set(ChartExporter::class)
        ->tag('app.locatable');

    // Maintenance listener
    $services->set(MaintenanceListener::class)
        ->arg('$options', Config('MAINTENANCE'))
        ->tag('kernel.event_listener', [
            'event' => 'kernel.request',
            'priority' => 10,
        ]);
    $services->set(CsrfListener::class)
        ->tag('kernel.event_listener', ['event' => 'kernel.controller']);
    $services->set(Language::class)
        ->arg('$langFolder', PathJoin('%kernel.project_dir%', Config('LANGUAGE_FOLDER')))
        ->arg('$cacheFolder', Config('LANGUAGE_CACHE_FOLDER'))
        ->arg('$cacheFile', Config('LANGUAGE_CACHE_FILE'))
        ->arg('$hashFolder', Config('LANGUAGE_HASH_FOLDER'));
    $services->alias('app.language', Language::class);
    $services->set(LanguageCacheWarmer::class)
        ->arg('$langFolder', PathJoin('%kernel.project_dir%', Config('LANGUAGE_FOLDER')))
        ->tag('kernel.cache_warmer');
    $services->set(UserProfile::class)
        ->arg('$cache', service('cache.userprofile'));
    $services->alias('user.profile', UserProfile::class);
    $services->set(UserProfileFactory::class);
    $services->alias('user.profile.factory', UserProfileFactory::class);
    $services->set(AdvancedSecurity::class);
    $services->alias('app.security', AdvancedSecurity::class);
    $services->set(PhpRenderer::class)
        ->arg('$templatePath', 'views/');
    $services->alias('app.view', PhpRenderer::class);
    $services->set(AppEventSubscriber::class)
        ->tag('kernel.event_subscriber');
    $services->set(AccessControlRequestMatcher::class);
    $services->set(AuthenticationSuccessHandler::class);
    $services->alias(AuthenticationSuccessHandlerInterface::class, AuthenticationSuccessHandler::class);
    $services->set(AuthenticationFailureHandler::class);
    $services->alias(AuthenticationFailureHandlerInterface::class, AuthenticationFailureHandler::class);
    $services->set(LegacyPasswordHasher::class);
    $services->set(WindowsUserProvider::class);
    $services->set(AuthenticationEntryPoint::class);
    $services->set(UserChecker::class)
        ->tag('security.user_checker.main');
    $services->set('cache.userprofile', FilesystemAdapter::class)
        ->arg('$defaultLifetime', Config('USER_PROFILE.CACHE_LIFETIME'))
        ->arg('$directory', Config('USER_PROFILE.CACHE_DIR'));
    $services->set('lexik_jwt_authentication.web_token.iat_validator', IssuedAtChecker::class)
        ->args([
            service('clock'), // Symfony ClockInterface service
            Config('JWT.CLOCK_SKEW') // leeway / clock skew in seconds
        ])
        ->public(false)
        ->tag('jose.checker.claim', ['alias' => 'iat_with_clock_skew'])
        ->tag('jose.checker.header', ['alias' => 'iat_with_clock_skew']);
    $services->set('lexik_jwt_authentication.web_token.exp_validator', ExpirationTimeChecker::class)
        ->args([
            service('clock'),
            Config('JWT.CLOCK_SKEW')
        ])
        ->public(false)
        ->tag('jose.checker.claim', ['alias' => 'exp_with_clock_skew'])
        ->tag('jose.checker.header', ['alias' => 'exp_with_clock_skew']);
    $services->set('lexik_jwt_authentication.web_token.nbf_validator', NotBeforeChecker::class)
        ->args([
            service('clock'),
            Config('JWT.CLOCK_SKEW')
        ])
        ->public(false)
        ->tag('jose.checker.claim', ['alias' => 'nbf_with_clock_skew'])
        ->tag('jose.checker.header', ['alias' => 'nbf_with_clock_skew']);
    $services->set(PermissionVoter::class);
    $services->set(ApiPermissionVoter::class);

    // Link Provider
    $services->set(LinkProviderFactory::class);
    $services->set(RuntimeLinkProvider::class)
        ->arg('$hrefs', param('app.links'));
    $services->alias('link.provider', RuntimeLinkProvider::class);
    $services->alias(EvolvableLinkProviderInterface::class, RuntimeLinkProvider::class);
    $services->set('notification.view', PhpRenderer::class)
        ->args(['lang/']);
    $services->set(AuditTrailHandler::class)
        ->arg('$filename', '%kernel.logs_dir%/audit.log');
    $services->set('app.audit', Logger::class)
        ->args([
            'audit',
            [service(AuditTrailHandler::class)],
        ]);
    $services->set('notifier.flash_message_importance_mapper', BootstrapFlashMessageImportanceMapper::class);
    $services->set(EventManager::class); // For entity managers
    $services->set(\Firehed\DbalLogger\Middleware::class)
        ->args([
            service('debug.stack')
        ])
        ->tag('doctrine.middleware');
    $services->set(ConnectionMiddleware::class)
        ->tag('doctrine.middleware', ['priority' => 20]);
    $services->set('default.storage.prefixer', PathPrefixer::class)
        ->args([Config('FLYSYSTEM.storages')['default.storage']['options']['directory'], DIRECTORY_SEPARATOR]);
    $services->set(DynamicPublicUrlGenerator::class);
    $services->set(DebugStack::class)
        ->arg('$enabled', IsDebug() || Config('LOG_TO_FILE'));
    $services->alias('debug.stack', DebugStack::class);
    $services->set('reflection.enum.allow', ReflectionEnum::class)
        ->args([Allow::class]);
    $services->set(Breadcrumb::class);
    $services->set(FileUploadHandler::class)
        ->tag('app.locatable');
    $services->set(FieldFactory::class)
        ->arg('$requestStack', service('request_stack'))
        ->arg('$language', service(Language::class))
        ->arg('$projectDir', '%kernel.project_dir%');
    $services->set(CSPBuilder::class)
        ->args([Config('CSP')]);
    $services->set(CspListener::class)
        ->args([service(CSPBuilder::class)])
        ->tag('kernel.event_subscriber');
    $parameters->set('encryption.key', AesEncryptionKey(base64_decode(ServerVar('AES_ENCRYPTION_KEY'))));
    $services->set('mobile.detect', MobileDetect::class);
    $services->set(LoginStatusEvent::class);
    $services->set(Encrypter::class)
        ->args([param('encryption.key'), Config('AES_ENCRYPTION_CIPHER')]);
    $services->set(EmailTwoFactorAuthentication::class)
        ->tag('app.locatable');
    $services->set(SmsTwoFactorAuthentication::class)
        ->tag('app.locatable');
    $services->set(PragmaRxTwoFactorAuthentication::class)
        ->tag('app.locatable');
    $services->set(TwoFactorAuthenticationInterface::class, Config('TWO_FACTOR_AUTHENTICATION_CLASS'))
        ->tag('app.locatable');
    $services->set(MimeTypes::class);
    $services->set(ImageManager::class)
        ->arg('$driver', Config('RESIZE_OPTIONS.driver'))
        ->arg('$keepAspectRatio', Config('RESIZE_OPTIONS.keepAspectRatio'))
        ->arg('$resizeUp', Config('RESIZE_OPTIONS.resizeUp'))
        ->arg('$quality', Config('RESIZE_OPTIONS.jpegQuality'));
    $services->set(BodyParsingListener::class)
        ->tag('kernel.event_listener', [
            'event' => 'kernel.request',
            'method' => 'onKernelRequest'
        ]);

    // Mailer
    if (Config('USE_PHPMAILER')) { // PHPMailer
        $services->set(PhpMailerTransportFactory::class)
            ->arg('$dispatcher', service('event_dispatcher'))
            ->arg('$logger', service('logger'))
            ->arg('$settings', Config('SMTP'))
            ->tag('mailer.transport_factory');
    }

    // Not 3rd party mailer => Symfony built-in SMTP mailer
    if (!Config('MAILER_TRANSPORT')) {
        $services->set(EsmtpTransportFactoryDecorator::class) // Use decorated SMTP transport factory
            ->decorate('mailer.transport_factory.smtp')
            ->args([
                service('.inner'), // Original EsmtpTransportFactory
                Config('SMTP'), // SMTP settings
                Config('MAILER_AUTHENTICATORS'), // Redefine the supported authenticators, e.g. [new XOAuth2Authenticator()]
            ]);
    }

    // Controllers
    $services->load(PROJECT_NAMESPACE, '../controllers/')
        ->tag('controller.service_arguments'); // Make classes public in container

    // Load models and tag them
    $services->load(PROJECT_NAMESPACE, '../models/')
        ->tag('app.model');

    // Load all classes in src/EventSubscriber as services
    $services->load(PROJECT_NAMESPACE . 'EventSubscriber\\', '../src/EventSubscriber/');

    // Load all classes in src/EventListener as services
    $services->load(PROJECT_NAMESPACE . 'EventListener\\', '../src/EventListener/');

    // Load all classes in src/Service as services
    if (is_dir(__DIR__ . '/../src/Service')) {
        $services->load(PROJECT_NAMESPACE . 'Service\\', '../src/Service/');
    }

    // Load all classes in src/Twig/Components as services
    if (is_dir(__DIR__ . '/../src/Twig/Components')) {
        $services->load(PROJECT_NAMESPACE . 'Twig\\Components\\', '../src/Twig/Components');
    }

    // PSR-17 Factory for creating PSR-7 objects
    $services->set(Psr17Factory::class);

    // PsrHttpFactory for converting Symfony responses to PSR-7 response
    $services->set(PsrHttpFactory::class)
        ->args([
            service(Psr17Factory::class), // PSR-17 request factory
            service(Psr17Factory::class), // PSR-17 stream factory
            service(Psr17Factory::class), // PSR-17 uploaded file factory
            service(Psr17Factory::class), // PSR-17 response factory
        ]);
    $services->alias(HttpMessageFactoryInterface::class, PsrHttpFactory::class);

    // Controller value resolvers
    $services->set(RouteArgsValueResolver::class)
        ->args([service('request_stack')])
        ->tag('controller.argument_value_resolver');

    // Register PSR-7 argument resolver (Psr\Http\Message\ResponseInterface)
    $services->set(PsrResponseValueResolver::class)
        ->args([
            service(Psr17Factory::class), // PSR-17 response factory
            service(Psr17Factory::class) // PSR-17 stream factory
        ])
        ->tag('controller.argument_value_resolver');

    // Register PSR-7 argument resolver (Psr\Http\Message\ServerRequestInterface)
    $services->set(PsrServerRequestResolver::class)
        ->tag('controller.argument_value_resolver');

    // Register PSR-7 response listener
    $services->set(PsrResponseListener::class);
    $services->set(EntityCollectionResolver::class)
        ->args([
            service('doctrine'),
            service('app.service_locator'),
        ])
        ->tag('controller.argument_value_resolver', ['priority' => 100]);
    $services->set(OriginalEntitiesResolver::class)
        ->args([
            service('doctrine'),
            service('app.service_locator'),
        ])
        ->tag('controller.argument_value_resolver', ['priority' => 50]);
    $services->set(ApiEntityValueResolver::class)
        ->args([
            service('doctrine'),
            service('app.service_locator'),
        ])
        ->tag('controller.argument_value_resolver');

    // Page entity resolver (priority must be higher than 110)
    $services->set(PageEntityValueResolver::class)
        ->args([
            service('doctrine'),
            service('app.service_locator'),
        ])
        ->tag('controller.argument_value_resolver', ['priority' => 200]);

    // Custom entity resolver (override EntityValueResolver)
    $services->set('doctrine.orm.entity_value_resolver', CustomEntityValueResolver::class)
        ->args([
            service('doctrine'),
            service('app.service_locator'),
        ])
        ->tag('controller.argument_value_resolver', ['priority' => 200]); // EntityValueResolver has priority 110

    // Timestampable
    $services->set(TimestampableListener::class)
        ->tag('doctrine.event_subscriber')
        ->call('setClock', [service('clock')]);

    // Safe entity normalizer
    $services->set(SafeEntityNormalizer::class)
        ->args([
            service('serializer.mapping.class_metadata_factory'),
            null, // No $nameConverter
            service('property_accessor'),
            service('property_info'),
        ])
        ->tag('serializer.normalizer');

    // Twig extension
    $services->set(TwigExtension::class)
        ->args([service('app.language')])
        ->tag('twig.extension');

    // Import services_*.php for extensions
    foreach (glob(__DIR__ . '/extensions/*.php') as $extensionFile) {
        $containerConfigurator->import($extensionFile);
    }

    // Commands
    $services->load('PHPMaker2026\Project1\\Command\\', dirname(__DIR__) . '/src/Command/')
        ->tag('console.command');

    // Dispatch Services Configuration Event
    DispatchEvent(new ServicesConfigurationEvent($services), ServicesConfigurationEvent::class);

    // Build locator from Config
    $serviceMap = Config('SERVICE_LOCATOR');
    $locatorServices = [];
    foreach ($serviceMap as $key => $serviceId) {
        $locatorServices[$key] = new Reference($serviceId, ContainerInterface::IGNORE_ON_INVALID_REFERENCE);
    }

    // Register internal locator
    $services->set('app.internal_locator', ServiceLocator::class)
        ->args([$locatorServices])
        ->tag('container.service_locator');

    // Register tagged service locator
    $services->set('app.tagged_locator', ServiceLocator::class)
        ->args([new TaggedIteratorArgument(
            'app.locatable',
            indexAttribute: 'class', // Use class name as key
            needsIndexes: true // Generate keys automatically
        )])
        ->tag('container.service_locator');

    // Wrap both locators into AppServiceLocator
    $services->set(AppServiceLocator::class)
        ->args([
            service('app.internal_locator'),
            service('app.tagged_locator'),
        ]);
    $services->alias('app.service_locator', AppServiceLocator::class);
};
