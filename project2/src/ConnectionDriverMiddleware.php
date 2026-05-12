<?php

namespace PHPMaker2026\Project1;

use Doctrine\DBAL\Driver\Connection as ConnectionInterface;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SQLServerPlatform;
use Doctrine\DBAL\Platforms\OraclePlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use SensitiveParameter;

class ConnectionDriverMiddleware extends AbstractDriverMiddleware
{

    protected function getConnectionType(array $params): ?string
    {
        $driver = $params['driver'] ?? '';
        return match ($driver) {
            'mysqli', 'pdo_mysql' => 'MYSQL',
            'sqlsrv', 'pdo_sqlsrv' => 'MSSQL',
            'oci8' => 'ORACLE',
            'pgsql', 'pdo_pgsql' => 'POSTGRESQL',
            'sqlite3', 'pdo_sqlite' => 'SQLITE',
            default => null
        };
    }

    protected function quotedName(string $name, string $dbtype): string
    {
        $qs = match ($dbtype) {
            "MYSQL" => '`',
            "MSSQL" => '[',
            default => '"'
        };
        $qe = $qs === '[' ? ']' : $qs;
        return $qs . str_replace($qe, $qe . $qe, $name) . $qe;
    }

    public function connect(#[SensitiveParameter] array $params): ConnectionInterface
    {
        // Dispatch pre-connection event
        $event = new DatabaseConnectingEvent(arguments: $params);
        DispatchEvent($event, DatabaseConnectingEvent::class);
        $params = $event->getArguments();

        // Extract schema from defaultTableOptions
        $schema = $params['defaultTableOptions']['schema'] ?? null;

        // Remove schema so DoctrineBundle does not see it
        if (isset($params['defaultTableOptions']['schema'])) {
            unset($params['defaultTableOptions']['schema']);
        }

        // Handle MySQL-specific configurations
        if ($this->getConnectionType($params) === 'MYSQL') {
            $params = $this->configureMySQL($params);
        }

        // Make sure that port is int for mysqli
        if (isset($params['port']) && ($params['driver'] ?? '') === 'mysqli') {
            $params['port'] = intval($params['port']);
        }

        // Create the connection
        $connection = parent::connect($params);

        // Apply database-specific settings, pass schema explicitly
        $this->applyDatabaseSettings($connection, $params, $schema);

        // Dispatch post-connection event
        $event = new DatabaseConnectedEvent($connection, $params);
        DispatchEvent($event, DatabaseConnectedEvent::class);
        return $connection;
    }

    protected function configureMySQL(array $params): array
    {
        // Handle SSL options for `pdo_mysql`
        if (($params['driver'] ?? '') === 'pdo_mysql') {
            $sslKeys = [
                \PDO::MYSQL_ATTR_SSL_CA,
                \PDO::MYSQL_ATTR_SSL_CAPATH,
                \PDO::MYSQL_ATTR_SSL_CERT,
                \PDO::MYSQL_ATTR_SSL_CIPHER,
                \PDO::MYSQL_ATTR_SSL_KEY,
            ];
            $driverOptions = $params['driverOptions'] ?? [];
            foreach ($driverOptions as $key => $value) {
                if (in_array($key, $sslKeys, true)) {
                    if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
                        $params['options'][\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] ??= false;
                    }
                    break;
                }
            }
        }

        // Handle SSL options for `mysqli`
        if (($params['driver'] ?? '') === 'mysqli') {
            foreach ($params as $key => $value) {
                if (str_starts_with($key, 'ssl_')) {
                    $params['options']['flags'] = ($params['options']['flags'] ?? 0) | MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT;
                    break;
                }
            }
        }
        return $params;
    }

    protected function applyDatabaseSettings(ConnectionInterface $connection, array $params, ?string $schema = null): void
    {
        $dbtype = $this->getConnectionType($params);
        $dbtimezone = Config('DB_TIME_ZONE');
        if ($dbtype === 'MYSQL' && $dbtimezone) {
            $connection->exec('SET time_zone = \'' . $dbtimezone . '\'');
        } elseif ($dbtype === 'POSTGRESQL') {
            if ($dbtimezone) {
                $connection->exec('SET TIME ZONE \'' . $dbtimezone . '\'');
            }
            if (!empty($schema) && $schema !== 'public') {
                $schema = str_contains($schema, ',') ? $schema : $this->quotedName($schema, $dbtype);
                $connection->exec('SET search_path TO ' . $schema);
            }
        } elseif ($dbtype === 'SQLITE') {
            $connection->getNativeConnection()->sqliteCreateFunction('regexp', 'preg_match', 2);
        } elseif ($dbtype === 'ORACLE') {
            $oraVars = [
                'NLS_TIME_FORMAT' => 'HH24:MI:SS',
                'NLS_DATE_FORMAT' => 'YYYY-MM-DD HH24:MI:SS',
                'NLS_TIMESTAMP_FORMAT' => 'YYYY-MM-DD HH24:MI:SS',
                'NLS_TIMESTAMP_TZ_FORMAT' => 'YYYY-MM-DD HH24:MI:SS TZH:TZM',
                'NLS_NUMERIC_CHARACTERS' => '.,',
            ];
            if ($dbtimezone) {
                $oraVars['TIME_ZONE'] = $dbtimezone;
            }
            if ($schema) {
                $oraVars['CURRENT_SCHEMA'] = $this->quotedName($schema, $dbtype);
            }
            $statements = [];
            foreach ($oraVars as $key => $value) {
                if ($key === 'CURRENT_SCHEMA') {
                    $statements[] = $key . ' = ' . $value;
                } else {
                    $statements[] = $key . ' = \'' . $value . '\'';
                }
            }
            $connection->exec('ALTER SESSION SET ' . implode(' ', $statements));
        }
    }
}
