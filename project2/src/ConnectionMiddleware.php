<?php
declare(strict_types=1);

namespace PHPMaker2026\Project1;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware;

class ConnectionMiddleware implements Middleware
{

    public function wrap(Driver $driver): Driver
    {
        return new ConnectionDriverMiddleware($driver);
    }
}
