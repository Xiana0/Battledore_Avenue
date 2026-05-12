<?php

namespace PHPMaker2026\Project1;

class ParsedMessage
{

    public function __construct(
        public readonly ?string $subject = null,
        public readonly ?string $sender = null,
        public readonly ?string $recipient = null,
        public readonly ?string $cc = null,
        public readonly ?string $bcc = null,
        public readonly ?string $format = null,
        public readonly ?string $content = null,
    ) {}
}
