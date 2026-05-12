<?php

namespace PHPMaker2026\Project1;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{

    public function __construct(protected Language $language)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('cssfile', fn(string $f, ?bool $rtl = null, ?bool $min = null) => CssFile($f, $rtl, $min)),
            new TwigFunction('config', fn(string $name) => Config($name)),
            new TwigFunction('phrase', [$this, 'getPhrase']),
        ];
    }

    /**
     * Wrapper for Language::phrase()
     *
     * @param string    $id       Phrase identifier
     * @param bool|null $useText  If true, return raw text even if not translated
     *
     * @return string|array
     */
    public function getPhrase(string $id, ?bool $useText = false): string|array
    {
        return $this->language->phrase($id, $useText);
    }
}
