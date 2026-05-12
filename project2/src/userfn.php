<?php

namespace PHPMaker2026\Project1;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\OnClearEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\ObjectRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\EventStreamResponse;
use Symfony\Component\HttpFoundation\ServerEvent;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use League\Flysystem\DirectoryListing;
use League\Flysystem\FilesystemException;
use ParagonIE\CSPBuilder\CSPBuilder;
use InvalidArgumentException;
use Exception;
use Throwable;
use DateTimeInterface;
use DateTimeImmutable;
use DateInterval;
use DateTime;
use Closure;
use Traversable;
use PHPMaker2026\Project1\Entity as BaseEntity;
use PHPMaker2026\Project1\Db;
use PHPMaker2026\Project1\Db\Entity;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Doctrine\DBAL\Driver\Connection as ConnectionInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\DependencyInjection\Loader\Configurator\AbstractConfigurator;
use Symfony\Component\Notifier\Transport as NotifierTransport; // SMS transport
use Symfony\Component\Notifier\Channel\EmailChannel;
use Symfony\Component\Notifier\Channel\SmsChannel;
use Symfony\Component\Notifier\Event\MessageEvent as NotifierMessageEvent;
use Symfony\Component\Notifier\Event\SentMessageEvent as NotifierSentMessageEvent;
use Symfony\Component\Notifier\Event\FailedMessageEvent as NotifierFailedMessageEvent;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\RecipientInterface;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mailer\Transport\Smtp\Auth\XOAuth2Authenticator;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mailer\Event\SentMessageEvent;
use Symfony\Component\Mailer\Event\FailedMessageEvent;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

// Filter for 'Last Month' (example)
function GetLastMonthFilter(string $expression, string $dbid = "DB"): string
{
    $today = getdate();
    $lastmonth = mktime(0, 0, 0, $today['mon'] - 1, 1, $today['year']);
    $val = date("Y|m", $lastmonth);
    $wrk = $expression . " BETWEEN " .
        QuotedValue(DateValue("month", $val, 1, $dbid), DataType::DATE, $dbid) .
        " AND " .
        QuotedValue(DateValue("month", $val, 2, $dbid), DataType::DATE, $dbid);
    return $wrk;
}

// Filter for 'Starts With A' (example)
function GetStartsWithAFilter(string $expression, string $dbid = "DB"): string
{
    return $expression . Like("A%", $dbid);
}

// Global user functions

// Database Connecting event
function Database_Connecting(array &$info): void
{
    // Example:
    //var_dump($info);
    //if ($info["dbname"] == "myDbName" && IsLocal()) { // Testing on local PC
    //    $info["host"] = "localhost";
    //    $info["user"] = "root";
    //    $info["password"] = "";
    //}
}

// Database Connected event
function Database_Connected(ConnectionInterface $conn, array $info): void
{
    // Example:
    //if ($info["dbname"] == "myDbName") {
    //    $conn->exec("Your SQL");
    //}
}

// Language Load event
function Language_Load(): void
{
    // Example:
    //$this->setPhrase("MyID", "MyValue"); // Refer to language file for the actual phrase id
    //$this->setPhraseClass("MyID", "fa-solid fa-xxx ew-icon"); // Refer to https://fontawesome.com/search?ic=free&o=r for icon name
}

function MenuItem_Adding(MenuItem $item): void
{
    //var_dump($item);
    //$item->Allowed = false; // Set to false if menu item not allowed
}

function Menu_Rendering(): void
{
    // Change menu items here
}

function Menu_Rendered(): void
{
    // Clean up here
}

// Page Loading event
function Page_Loading(): void
{
    //Log("Page Loading");
}

// Page Rendering event
function Page_Rendering(): void
{
    //Log("Page Rendering");
}

// Page Unloaded event
function Page_Unloaded(): void
{
    //Log("Page Unloaded");
}

// AuditTrail Inserting event
function AuditTrail_Inserting(array &$row): bool
{
    //var_dump($row);
    return true;
}

// Personal Data Downloading event
function PersonalData_Downloading(UserInterface $user): void
{
    //Log("PersonalData Downloading");
}

// Personal Data Deleted event
function PersonalData_Deleted(UserInterface $user): void
{
    //Log("PersonalData Deleted");
}

// One Time Password Sending event
function Otp_Sending(Notification $notification, RecipientInterface $recipient): bool
{
    // Example:
    // var_dump($notification, $recipient); // View notification and recipient
    // if (in_array("email", $notification->getChannels())) { // Possible values, "email" or "sms"
    //     $notification->content("..."); // Change content
    //     $recipient->email("..."); // Change email
    //     // return false; // Return false to cancel
    // }
    return true;
}

// Route Config event
function Route_Config(RoutingConfigurator $routes): void
{
    // Example: See https://symfony.com/doc/current/routing.html#creating-routes-in-yaml-xml-or-php-files
    // Route using a callback
    // $routes->add('hello_user', '/hello/{name}')
    //     ->controller(function (Request $request, string $name): Response {
    //         return new Response("Hello, $name!");
    //     })
    //     ->methods(['GET']);

    // Route using a controller method
    // $routes->add('welcome_page', '/welcome')
    //     ->controller([MyController::class, 'welcome'])
    //     ->methods(['GET']);
}

// Services Config event
function Services_Config(AbstractConfigurator $services): void
{
    // Example:
    // $services->set(MyListener::class)->tag("kernel.event_listener"); // Make sure you tag your listener as "kernel.event_listener"
}

// Add listeners
AddListener(DatabaseConnectingEvent::class, function(DatabaseConnectingEvent $event) {
    $args = $event->getArguments();
    Database_Connecting($args);
    foreach ($args as $key => $value) {
        if (!$event->hasArgument($key) || $event->getArgument($key) !== $value) {
            $event->setArgument($key, $value);
        }
    }
});
AddListener(DatabaseConnectedEvent::class, fn(DatabaseConnectedEvent $event) => Database_Connected($event->getConnection(), $event->getArguments()));
AddListener(LanguageLoadEvent::class, fn(LanguageLoadEvent $event) => Language_Load(...)->bindTo($event->getLanguage())());
AddListener(MenuItemAddingEvent::class, fn(MenuItemAddingEvent $event) => MenuItem_Adding(...)->bindTo($event->getMenu())($event->getMenuItem()));
AddListener(MenuRenderingEvent::class, fn(MenuRenderingEvent $event) => Menu_Rendering(...)->bindTo($event->getMenu())($event->getMenu()));
AddListener(MenuRenderedEvent::class, fn(MenuRenderedEvent $event) => Menu_Rendered(...)->bindTo($event->getMenu())($event->getMenu()));
AddListener(PageLoadingEvent::class, fn(PageLoadingEvent $event) => Page_Loading(...)->bindTo($event->getPage())());
AddListener(PageRenderingEvent::class, fn(PageRenderingEvent $event) => Page_Rendering(...)->bindTo($event->getPage())());
AddListener(PageUnloadedEvent::class, fn(PageUnloadedEvent $event) => Page_Unloaded(...)->bindTo($event->getPage())());
AddListener(RouteConfigurationEvent::class, fn(RouteConfigurationEvent $event) => Route_Config($event->getRoutingConfigurator()));
AddListener(ServicesConfigurationEvent::class, fn(ServicesConfigurationEvent $event) => Services_Config($event->getServices()));

// Dompdf
AddListener(ConfigurationEvent::class, function (ConfigurationEvent $event) {
    $event->import([
        "PDF_BACKEND" => "CPDF",
        "PDF_STYLESHEET_FILENAME" => "css/ewpdf.css", // Export PDF CSS styles
        "PDF_TIME_LIMIT" => 120, // Time limit
        "PDF_MAX_IMAGE_WIDTH" => 650, // Make sure image width not larger than page width or "infinite table loop" error
        "PDF_MAX_IMAGE_HEIGHT" => 900, // Make sure image height not larger than page height or "infinite table loop" error
        "PDF_IMAGE_SCALE_FACTOR" => 1.53, // Scale factor
    ]);
});
