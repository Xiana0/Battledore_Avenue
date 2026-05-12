<?php

namespace PHPMaker2026\Project1;

use Monolog\Handler\ChromePHPHandler;
use Monolog\LogRecord;
use Symfony\Component\HttpFoundation\RequestStack;
use Monolog\Level;

class CustomChromePHPHandler extends ChromePHPHandler
{

    public function __construct(
        private RequestStack $requestStack,
        int|string|Level $level = Level::Debug,
        bool $bubble = true,
        private int $maxHeaderLength = 8192,
        private bool $onlyAjax = true
    ) {
        parent::__construct($level, $bubble);
    }

    public function isHandling(LogRecord $record): bool
    {
        // Level check
        $levelOk = $record->level->value >= $this->level->value;
        if (!$this->onlyAjax) {
            return $levelOk;
        }

        // Only handle AJAX/fetch requests if flag is true
        $request = $this->requestStack->getCurrentRequest();
        $ajax = $request && $request->isXmlHttpRequest();
        return $levelOk && $ajax;
    }

    protected function send(): void
    {
        if (empty(self::$json['rows'])) {
            return;
        }
        $json = \json_encode(self::$json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $data = base64_encode($json);
        if (\strlen($data) > $this->maxHeaderLength) {
            $record = new LogRecord(
                message: 'Incomplete logs, chrome header size limit reached',
                level: Level::Warning,
                channel: 'ajax',
                datetime: new \DateTimeImmutable(),
            );
            self::$json['rows'][\count(self::$json['rows']) - 1] = $this->getFormatter()->format($record);
            $json = \json_encode(self::$json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $data = base64_encode($json);
        }
        if (trim($data) !== '') {
            $this->sendHeader(static::HEADER_NAME, $data);
        }
    }
}
