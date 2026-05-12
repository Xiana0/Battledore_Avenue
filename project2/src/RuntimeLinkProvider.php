<?php

namespace PHPMaker2026\Project1;

use Psr\Link\EvolvableLinkProviderInterface;
use Psr\Link\LinkInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\WebLink\Link;

class RuntimeLinkProvider implements EvolvableLinkProviderInterface
{
    protected EvolvableLinkProviderInterface $currentProvider;

    /** @var string[] List of CSS files to convert to RTL */
    protected array $rtlFiles = [];

    /**
     * Constructs a new instance
     *
     * @param RequestStack $requestStack The request stack used to access the current request
     * @param LinkProviderFactory $factory Factory to create the initial link provider
     * @param array $hrefs Optional array of hrefs to initialize the link provider with
     */
    public function __construct(
        protected RequestStack $requestStack,
        protected ParameterBagInterface $params,
        LinkProviderFactory $factory,
        array $hrefs = []
    ) {
        // Use the factory to create the initial provider
        $this->currentProvider = $factory->create($hrefs);

        // Base names of CSS files with RTL variants
        $this->rtlFiles = [
            'adminlte', // Matches *.css or *.min.css
            'tempus-dominus',
            'jquery.timepicker',
            'query-builder',
        ];

        // Add parameter-based CSS files (resolved paths)
        $projectCss = $this->params->get('project.css'); // e.g., 'build/project.css'
        if ($projectCss) {
            $this->rtlFiles[] = basename($projectCss, '.css'); // use base name 'project'
        }
    }
    /**
     * Set a new link provider at runtime
     *
     * @param EvolvableLinkProviderInterface $provider
     */
    public function setProvider(EvolvableLinkProviderInterface $provider): void
    {
        $this->currentProvider = $provider;
    }

    /**
     * Get the current link provider
     *
     * @return EvolvableLinkProviderInterface
     */
    public function getProvider(): EvolvableLinkProviderInterface
    {
        return $this->currentProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getLinks(): iterable
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request?->headers->get("X-Requested-With") === "XMLHttpRequest") {
            return [];
        }
        $links = $this->currentProvider->getLinks();
        $links = $this->currentProvider->getLinks();
        $isRtl = IsRTL();

        // If not RTL, just return the original links
        if (!$isRtl) {
            return $links;
        }

        // Only create new RTL links if RTL is active
        $rtlLinks = [];
        foreach ($links as $link) {
            // $link is a Symfony\Component\WebLink\Link object
            $href = $link->getHref();
            $attributes = $link->getAttributes();
            $rel = $link->getRels()[0] ?? null; // pick first rel if exists

            // Only rewrite style links
            if ($rel === Link::REL_STYLESHEET || ($attributes['as'] ?? null) === 'style') {
                foreach ($this->rtlFiles as $base) {
                    if (preg_match('/\b' . preg_quote($base, '/') . '(\.min)?\.css(\?v=[^&]+)?$/', $href, $matches)) {
                        $suffix = $matches[1] ?? ''; // ".min" if exists
                        $query  = $matches[2] ?? ''; // "?v=..." if exists
                        $href   = preg_replace(
                            '/(\.min)?\.css(\?v=[^&]+)?$/',
                            '.rtl' . $suffix . '.css' . $query,
                            $href
                        );
                        break;
                    }
                }

                // Clone Link with modified href
                $link = $link->withHref($href);
            }
            $rtlLinks[] = $link;
        }
        return $rtlLinks;
    }

    /**
     * {@inheritdoc}
     */
    public function getLinksByRel(string $rel): iterable
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request?->headers->get("X-Requested-With") === "XMLHttpRequest") {
            return [];
        }
        return $this->currentProvider->getLinksByRel($rel);
    }

    /**
     * {@inheritdoc}
     */
    public function withLink(LinkInterface $link): static
    {
        $this->currentProvider = $this->currentProvider->withLink($link);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutLink(LinkInterface $link): static
    {
        $this->currentProvider = $this->currentProvider->withoutLink($link);
        return $this;
    }

    /**
     * Generate HTML for link and script tags based on the current provider's links
     *
     * @return string
     */
    public function getHtml(): string
    {
        $html = '';
        foreach ($this->getLinks() as $link) {
            $href = $link->getHref();
            if (str_contains($href, 'clientscript.js') || str_contains($href, 'startupscript.js')) {
                continue; // Skip clientscript.js and startupscript.js
            }
            $attributes = $link->getAttributes();
            if (isset($attributes['as']) && $attributes['as'] === 'style') {
                // Generate <link> tag for stylesheets
                $html .= sprintf(
                    '<link' . Nonce() . ' rel="stylesheet" href="%s" %s>' . PHP_EOL,
                    htmlspecialchars($href, ENT_QUOTES),
                    $this->formatAttributes($attributes)
                );
            } elseif (isset($attributes['as']) && $attributes['as'] === 'script') {
                // Generate <script> tag for scripts
                $html .= sprintf(
                    '<script' . Nonce() . ' src="%s" %s></script>' . PHP_EOL,
                    htmlspecialchars($href, ENT_QUOTES),
                    $this->formatAttributes($attributes)
                );
            }
        }
        return $html;
    }

    /**
     * Format attributes as a string for HTML tags
     *
     * @param array $attributes
     * @return string
     */
    private function formatAttributes(array $attributes): string
    {
        unset($attributes['as']); // Remove 'as' attribute since it's not needed in the tag
        $formatted = [];
        foreach ($attributes as $key => $value) {
            $formatted[] = sprintf('%s="%s"', htmlspecialchars($key, ENT_QUOTES), htmlspecialchars($value, ENT_QUOTES));
        }
        return implode('', $formatted);
    }
}
