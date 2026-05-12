<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\WebLink\GenericLinkProvider;
use Symfony\Component\WebLink\Link;

class LinkProviderFactory
{

    public static function create(array $hrefs = []): GenericLinkProvider
    {
        $links = [];
        foreach ($hrefs as $href => $attrs) {
            $link = new Link('preload', GetUrl($href));
            foreach ($attrs as $key => $value) {
                $link = $link->withAttribute($key, $value);
            }
            $links[] = $link;
        }
        return new GenericLinkProvider($links);
    }
}
