<?php

namespace PHPMaker2026\Project1;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Language Load Event
 */
class LanguageLoadEvent extends Event
{

    public function __construct(protected Language $language)
    {
    }

    public function getLanguage(): Language
    {
        return $this->language;
    }

    public function getSubject(): Language
    {
        return $this->language;
    }

    public function setPhrase(string $id, string $value): void
    {
        $this->language->setPhrase($id, $value);
    }
}
