<?php

namespace PHPMaker2026\Project1;

use Psr\Log\LoggerInterface;
use Firehed\DbalLogger\QueryLogger;
use function microtime;

/**
 * Includes executed SQLs in a Debug Stack
 */
class DebugStack implements QueryLogger
{
    /**
     * Executed SQL queries
     *
     * @var array<int, array<string, mixed>>
     */
    public array $queries = [];

    /** @var float|null */
    public ?float $start = null;

    /** @var int */
    public int $currentQuery = 0;

    /**
     * Constructor
     */
    public function __construct(
        protected LoggerInterface $logger,
        public bool $enabled = true,
    ) {
    }

    /**
     * Logs a SQL statement somewhere
     *
     * @param string $sql SQL statement
     * @param list<mixed>|array<string, mixed>|null $params Statement parameters
     * @param ParameterType[] $types
     *
     * @return void
     */
    public function startQuery($sql, ?array $params = null, ?array $types = null)
    {
        if (!$this->enabled) {
            return;
        }
        $this->start = microtime(true);
        $this->queries[++$this->currentQuery] = [
            'sql' => $sql,
            'params' => $params,
            'types' => $types,
            'executionMS' => 0,
        ];
        if (Config('LOG_TO_FILE')) {
            $this->logger->debug('Executing query: ' . $sql, ['params' => $params, 'types' => $types]);
        }
    }

    /**
     * Marks the last started query as stopped. This can be used for timing of queries.
     *
     * @return void
     */
    public function stopQuery()
    {
        if (!$this->enabled) {
            return;
        }
        $this->queries[$this->currentQuery]['executionMS'] = microtime(true) - $this->start;
        if (Config('LOG_TO_FILE')) {
            $query = $this->queries[$this->currentQuery];
            $this->logger->debug($query['sql'], array_slice($query, 1, 3, true));
        }
    }
}
