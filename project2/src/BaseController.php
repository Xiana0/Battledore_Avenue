<?php

namespace PHPMaker2026\Project1;

use Psr\Log\LoggerInterface;
use Psr\Link\EvolvableLinkProviderInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\WebLink\GenericLinkProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Environment;
use DateTime;

/**
 * Controller base class
 */
abstract class BaseController
{
    /**
     * Constructor
     */
    public function __construct(
        protected KernelInterface $kernel,
        protected Language $language,
        protected AdvancedSecurity $security,
        protected RequestStack $requestStack,
        protected PhpRenderer $view,
        protected Environment $twig,
        protected EvolvableLinkProviderInterface $linkProvider,
        protected AppServiceLocator $locator,
        protected Security $symfonySecurity,
        protected ParameterBagInterface $parameters,
        protected HttpKernelInterface $httpKernel,
        protected LoggerInterface $logger,
    ) {
    }

    /**
     * Get container
     *
     * Note: Don't use $container property of AbstractController which is only a service locator.
     *
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->kernel->getContainer();
    }

    /**
     * Write HTTP header
     *
     * @param Response $response
     * @return void
     */
    public function writeHeader(Response $response): void
    {
        $response->setCache([
            'no_cache' => true,
            'no_store' => true,
            'last_modified' => new DateTime()
        ])->setExpires(new DateTime('yesterday')); // Date in the past
        $response->headers->set('X-UA-Compatible', 'IE=edge');
        if (!IsExport() || IsExport('print')) {
            $ct = 'text/html';
            $charset = PROJECT_CHARSET;
            if ($charset != '') {
                $ct .= '; charset=' . $charset;
            }
            $response->headers->set('Content-Type', $ct); // Charset
        }
    }

    /**
     * Forwards the request to another controller
     *
     * @param string $controller The controller name (a string like "App\Controller\PostController::index" or "App\Controller\PostController" if it is invokable)
     */
    protected function forward(string $controller, array $path = [], array $query = []): Response
    {
        $request = $this->requestStack->getCurrentRequest();
        $path['_controller'] = $controller;
        $subRequest = $request->duplicate($query, null, $path);
        return $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * Safely renders Twig syntax within a Response content string,
     * handling outermost <twig:XXX> blocks via temporary Twig files.
     *
     * Performance: Temporary template files are content-hashed and cached. Identical
     * component structures reuse the same template file, allowing Twig's compilation
     * cache to work efficiently.
     *
     * Note: Component tags are extracted and rendered in isolation via a temporary
     * .twig file. This means they only have access to the $context array passed to
     * this method, not variables defined within the PHP template itself. This is
     * by design to support hierarchical component syntax (e.g., <twig:Parent:Child>)
     * which requires Symfony UX TwigComponent's native handling.
     *
     * @param Response $response The response containing Twig syntax
     * @param array $context The context variables to pass to Twig rendering
     * @return Response The response with rendered Twig content
     */
    protected function renderTwig(Response $response, array $context): Response
    {
        $content = $response->getContent();

        // Quick check for Twig syntax
        if (!preg_match('/{%|{{|{#|<twig:/', $content)) {
            return $response;
        }
        $placeholderPrefix = "__TWIG_TAG_";
        $outerBlocks = [];

        // 1. Extract outermost <twig:XXX> blocks (normal or self-closing)
        $pattern = '/
            (                                   # group 1: full outer block
            <twig:([a-zA-Z0-9:]+)            # tag name
                (?:\s[^>]*)?                    # optional attributes
            >                                 # opening tag close
                (                               # inner content
                    (?:                         # non-capturing group
                        (?!<twig:\2\b)          # not a nested opening of same tag
                        .                       # any char
                        |                       # OR recursive same tag
                        (?R)
                    )*?
                )
            <\/twig:\2>                       # closing tag
            )
            |
            (                                   # group 3: self-closing tag
            <twig:([a-zA-Z0-9:]+)            # tag name
                (?:\s[^>]*)?                    # optional attributes
            \s*\/>                             # self-closing
            )
        /xs';
        preg_match_all($pattern, $content, $matches);
        $blocks = $matches[0] ?? [];

        // 2. Replace outer blocks with placeholders and wrap in HTML comments
        $tempTemplateContent = "";
        foreach ($blocks as $i => $block) {
            $placeholder = $placeholderPrefix . $i . "__";
            $outerBlocks[$placeholder] = $block;

            // Replace in original content with placeholder
            $content = str_replace($block, $placeholder, $content);

            // Add to temp template for rendering
            $tempTemplateContent .= "<!--{$placeholder}-->\n{$block}\n<!--{$placeholder}-->\n";
        }
        if ($outerBlocks) {
            // 3. Save temporary Twig file for outer blocks
            // Use hash to avoid recreating identical templates (Twig caches compiled templates)
            $contentHash = md5($tempTemplateContent);
            $tempTemplateName = 'temp_twig_components_' . $contentHash;
            $tempTemplatePath = __DIR__ . "/../templates/{$tempTemplateName}.twig";

            // Only create file if it doesn't exist
            if (!file_exists($tempTemplatePath)) {
                file_put_contents($tempTemplatePath, $tempTemplateContent);
            }
            try {
                // 4. Render outer blocks
                $renderedTemp = $this->twig->render($tempTemplateName . '.twig', $context);

                // 5. Extract rendered content for each outer block
                $renderedMap = [];
                foreach (array_keys($outerBlocks) as $placeholder) {
                    if (preg_match("/<!--{$placeholder}-->(.*?)<!--{$placeholder}-->/s", $renderedTemp, $m)) {
                        $renderedMap[$placeholder] = $m[1];
                    } else {
                        $renderedMap[$placeholder] = '';
                    }
                }

                // Replace placeholders in original content
                $content = strtr($content, $renderedMap);
            } catch (\Throwable $e) {
                if (isset($this->logger) && $this->logger instanceof LoggerInterface) {
                    $this->logger->warning('Twig render failed for outer <twig:XXX> blocks: ' . $e->getMessage());
                }
                // Fallback: restore original blocks if rendering failed
                $content = strtr($content, array_flip($outerBlocks));
            }
            // Note: We intentionally keep the temp file for Twig's cache to work efficiently
        }

        // 6. Tokenize remaining Twig tags outside outer blocks
        $patternSafe = '/({%[\s\S]*?%}|{{[\s\S]*?}}|{#[\s\S]*?#})/m';
        preg_match_all($patternSafe, $content, $safeMatches, PREG_OFFSET_CAPTURE);
        $out = [];
        $safeMap = [];
        $safeCounter = 0;
        $pos = 0;
        foreach ($safeMatches[0] as $m) {
            [$tag, $start] = $m;

            // Skip JsRender-like tags
            if (preg_match('/^\{\{\s*(?:[:\/>\*!]|!--|if\b|for\b|props\b|include\b|else\b|elseif\b|attr\b|url\b)/', $tag)) {
                continue;
            }

            // Add preceding text as safe block
            if ($start > $pos) {
                $raw = substr($content, $pos, $start - $pos);
                $token = "__SAFE_BLOCK_{$safeCounter}__";
                $safeMap[$token] = $raw;
                $out[] = "{% verbatim %}{$token}{% endverbatim %}";
                $safeCounter++;
            }

            // Keep Twig tag
            $out[] = $tag;
            $pos = $start + strlen($tag);
        }

        // Add trailing text
        if ($pos < strlen($content)) {
            $raw = substr($content, $pos);
            $token = "__SAFE_BLOCK_{$safeCounter}__";
            $safeMap[$token] = $raw;
            $out[] = "{% verbatim %}{$token}{% endverbatim %}";
        }

        // 7. Render remaining Twig tags
        $safeTemplate = implode('', $out);
        if (!empty($safeTemplate)) {
            try {
                $template = $this->twig->createTemplate($safeTemplate);
                $rendered = $template->render($context);
                $response->setContent(strtr($rendered, $safeMap));
            } catch (\Throwable $e) {
                if (isset($this->logger) && $this->logger instanceof LoggerInterface) {
                    $this->logger->warning('Twig render failed for remaining content: ' . $e->getMessage());
                }
                // Fallback: use content as-is (with component blocks already rendered)
                $response->setContent($content);
            }
        } else {
            // No Twig syntax to process, use content as-is
            $response->setContent($content);
        }
        return $response;
    }

    /**
     * Renders a Twig view
     */
    protected function render(string $view, array $parameters = [], ?Response $response = null): Response
    {
        $response ??= new Response();
        $content = $this->twig->render($view, $parameters);
        $response->setContent($content);
        return $response;
    }

    /**
     * Get value from request (query or request)
     *
     * @param Request $request
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function get(Request $request, string $key, mixed $default = null): mixed
    {
        if ($request->query->has($key)) {
            return $request->query->all()[$key];
        }
        if ($request->request->has($key)) {
            return $request->request->all()[$key];
        }
        return $default;
    }

    /**
     * Run page
     *
     * @param PageInterface|string $page
     * @param ?string $viewName
     * @param bool $useLayout
     * @return Response
     */
    protected function runPage(PageInterface|string $page, ?string $viewName = null, bool $useLayout = true): Response
    {
        global $httpContext;

        // Request
        $request = $this->requestStack->getCurrentRequest();

        // Route values
        $args = $request->attributes->get('_route_params', []);

        // Get page class from container if $page is a string
        if (is_string($page)) {
            $page = $this->locator->get(PROJECT_NAMESPACE . $page);
        }

        // Run page
        if ($page instanceof PageInterface) {
            // If not terminated by Page_Load event
            if (!$page->isTerminated()) {
                // Run the page
                $page->run();

                // Run detail grid pages
                if (property_exists($page, 'DetailGrids')) {
                    foreach ($page->DetailGrids as $grid) {
                        $grid->run();
                    }
                }
            }

            // Get page response if set
            if (($response = $page->Response) instanceof Response) {
                // Write custom HTTP headers
                if ($page->Headers->count()) {
                    $response->headers->add($page->Headers->all());
                }
                // Terminate page and clean up
                $page->terminate();
            }

            // Render page if not terminated
            if (!$response && !$page->isTerminated()) {
                if (
                    !$page->UseLayout // No layout
                    || property_exists($page, 'IsModal') && $page->IsModal // Modal
                    || $this->get($request, Config('PAGE_LAYOUT')) !== null // Multi-Column List page
                    || $request->headers->get("X-Requested-With") === "XMLHttpRequest" // Ajax request
                ) { // Partial view
                    $useLayout = false;
                }
                if ($this->get($request, 'export') !== null && $this->get($request, 'custom') !== null) { // Export custom template
                    $useLayout = true; // Require scripts
                }
                if ($useLayout) {
                    // Set the current link provider to the request
                    $request->attributes->set('_links', $this->linkProvider);
                    $this->view->setLayout('layout.php');
                }

                // Render view
                $httpContext['RenderingView'] = true;
                $routeName = $request->attributes->get('_route', ''); // Route name
                $TokenName = $routeName == 'login' ? 'authenticate' : Config('CSRF_TOKEN.id'); // Token id/name, e.g. 'submit', 'authenticate'
                $TokenValue = CsrfToken($TokenName); // Cookie name, e.g. 'csrf-token'
                $template = $page->View ?? $viewName ?? GetClassShortName($page); // View (without extension)
                $viewData = array_merge([
                    'Page' => $page,
                    'Title' => $page->Title, // Title
                    'Language' => $this->language,
                    'Security' => $this->security,
                    'TokenNameKey' => Config('CSRF_TOKEN.id_key'), // '_csrf_id', reuse $TokenNameKey for backward compatibility
                    'TokenName' => $TokenName, // 'submit' or 'authenticate', reuse $TokenName for backward compatibility
                    'TokenValueKey' => Config('CSRF_TOKEN.value_key'), // '_csrf_token'
                    'TokenValue' => $TokenValue, // Cookie name, e.g. 'csrf-token'
                    'DashboardReport' => $httpContext['DashboardReport'], // Dashboard report
                    'SkipHeaderFooter' => $httpContext['SkipHeaderFooter'],
                    'Nonce' => Nonce() ? $httpContext['Nonce'] : '',
                ], $httpContext['ViewData'] ?? []);
                try {
                    // Create response
                    $response = new Response();

                    // Write HTTP headers
                    $this->writeHeader($response);

                    // Write custom HTTP headers
                    if ($page->Headers->count()) {
                        $response->headers->add($page->Headers->all());
                    }

                    // Render view
                    if ($this->view->templateExists($template . '.php')) { // PHP
                        $this->view->setAttributes($viewData);
                        $response = $this->view->render($response, $template . '.php');
                        if (Config('USE_TWIG')) {
                            $response = $this->renderTwig($response, $viewData);
                        }
                    } elseif ($this->twig->getLoader()->exists($template . '.html.twig')) { // Twig
                        $response = $this->render($template . '.html.twig', $viewData, $response);
                    }
                } finally {
                    $httpContext['RenderingView'] = false;
                    $page->terminate(); // Terminate page and clean up
                }
            }

            // Set X-Refresh-Url in header if necessary (only for non-redirect responses)
            if (!$response->isRedirection() && ($refreshUrl = (FlashBag()->get('X-Refresh-Url')[0] ?? ''))) {
                $response->headers->set('X-Refresh-Url', $refreshUrl);
            }

            // Clean up temp folder if not add/edit/export
            if (
                property_exists($page, 'TableName') // Table/Report class
                && !in_array($page->PageID, ['add', 'register', 'edit', 'update']) // Not add/register/edit/update page
                && !($page->PageID == 'list' && $page->isAddOrEdit()) // Not list page add/edit
                && !(property_exists($page, 'Export') && $page->Export != '' && $page->Export != 'print' && $page->UseCustomTemplate) // Not export custom template
            ) {
                CleanUploadTempPaths($request->getSession()->getId(), '< now - ' . Config('CURRENT_UPLOAD_TEMP_FOLDER_TIME_LIMIT') . ' minutes');
            }
            return $response ?? new Response();
        }

        // Page not found
        throw new NotFoundHttpException();
    }

    /**
     * Run chart
     *
     * @param ?PageInterface $page Page object
     * @param string $chartName Chart variable name
     * @return Response
     */
    protected function runChart(?PageInterface $page, string $chartName): Response
    {
        // Request
        $request = $this->requestStack->getCurrentRequest();

        // Get page class from container if $page is a string
        if (is_string($page)) {
            $page = $this->locator->get(PROJECT_NAMESPACE . $page);
        }

        // Run page
        if (is_object($page)) {
            $page->run();

            // Render chart
            if (property_exists($page, $chartName)) {
                $chart = $page->$chartName;

                // Output chart
                try {
                    $chartClass = ($chart->PageBreakType == 'before') ? 'ew-chart-bottom' : 'ew-chart-top';
                    $chartWidth = $request->query->get('width');
                    $chartHeight = $request->query->get('height');
                    $html = $chart->render($page->ChartData, $chartClass, $chartWidth, $chartHeight);
                    $response = new Response($html);

                    // Write HTTP headers
                    $this->writeHeader($response);
                    return $response;
                } finally {
                    $page->terminate(); // Terminate page and clean up
                }
            }
            return new Response();
        }

        // Page not found
        throw new NotFoundHttpException();
    }
}
