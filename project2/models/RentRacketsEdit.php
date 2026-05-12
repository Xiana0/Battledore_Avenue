<?php

namespace PHPMaker2026\Project1;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\OnClearEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\ObjectRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\EventStreamResponse;
use Symfony\Component\HttpFoundation\ServerEvent;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use League\Flysystem\DirectoryListing;
use League\Flysystem\FilesystemException;
use ParagonIE\CSPBuilder\CSPBuilder;
use InvalidArgumentException;
use Exception;
use Throwable;
use DateTimeInterface;
use DateTimeImmutable;
use DateInterval;
use DateTime;
use Closure;
use Traversable;
use PHPMaker2026\Project1\Entity as BaseEntity;
use PHPMaker2026\Project1\Db;
use PHPMaker2026\Project1\Db\Entity;

/**
 * Page class
 */
#[AsAlias("RentRacketsEdit", true)]
class RentRacketsEdit extends RentRackets implements PageInterface
{
    use MessagesTrait;
    use FormTrait;

    // Page result
    public ?Response $Response = null;

    // Headers
    public HeaderBag $Headers;

    // Page ID
    public string $PageID = "edit";

    // Project ID
    public string $ProjectID = PROJECT_ID;

    // View file path
    public ?string $View = null;

    // Title
    public ?string $Title = null; // Title for <title> tag

    // CSS class/style
    public string $CurrentPageName = "RentRacketsEdit"; // Route action

    // Page headings
    public string $Heading = "";
    public string $Subheading = "";
    public string $PageHeader = "";
    public string $PageFooter = "";

    // Page layout
    public bool $UseLayout = true;

    // Page terminated
    private bool $terminated = false;

    // Properties
    public string $FormClassName = "ew-form ew-edit-form overlay-wrapper";
    public bool $IsModal = false;
    public bool $IsMobileOrModal = false;
    public ?string $HashValue = null; // Hash Value
    public int $DisplayRecords = 1;
    public bool $EditPaging = false; // Allow edit paging
    public ?int $RecordOffset = null; // Record offset (for Edit paging)
    public array $PagerOptions = ["proximity" => 2, "show_dots" => true];
    public int $RecordCount = 0;
    public array $DetailGrids = [];

    // Constructor
    public function __construct(
        Language $language,
        AdvancedSecurity $security,
        CSPBuilder $cspBuilder,
        CacheInterface $cache,
        FieldFactory $fieldFactory,
        EventDispatcherInterface $dispatcher,
    ) {
        parent::__construct($language, $security, $cspBuilder, $cache, $fieldFactory, $dispatcher);
        global $httpContext;
        $this->Headers = new HeaderBag();
        $this->TableVar = 'rent_rackets';
        $this->TableName = 'rent_rackets';

        // Table CSS class
        $this->TableClass = "table table-striped table-bordered table-hover table-sm ew-desktop-table ew-edit-table";

        // Initialize
        $httpContext["Page"] = $this;

        // Open connection
        $httpContext["Conn"] ??= $this->getConnection();

        // Pager options
        if (IsEmpty($this->PagerOptions)) {
            $this->PagerOptions = Config("PAGER_OPTIONS");
        }
    }

    // Page heading
    public function pageHeading(): string
    {
        if ($this->Heading != "") {
            return $this->Heading;
        }
        if (method_exists($this, "tableCaption")) {
            return $this->tableCaption();
        }
        return "";
    }

    // Page subheading
    public function pageSubheading(): string
    {
        if ($this->Subheading != "") {
            return $this->Subheading;
        }
        if ($this->TableName) {
            return Language()->phrase($this->PageID);
        }
        return "";
    }

    // Page name
    public function pageName(): string
    {
        return CurrentPageName();
    }

    // Page URL
    public function pageUrl(bool $withArgs = true): string
    {
        if ($withArgs) {
            return CurrentPageUrl();
        } else {
            $route = GetRoute();
            $path = $route?->getPath() ?? "";
            // Remove all placeholders like `{id}`
            $stripped = preg_replace('/\{[^}]+\}/', '', $path);
            // Remove trailing slash unless it's root '/', then replace leading slash with BasePath(true)
            return preg_replace('/^\//', BasePath(true), $stripped !== '/' ? rtrim($stripped, '/') : '/');
        }
    }

    // Get Page Header
    public function getPageHeader(): string
    {
        $header = $this->PageHeader;
        $this->pageDataRendering($header);
        if ($header != "") { // Header exists, display
            $header = '<div id="ew-page-header">' . $header . '</div>';
        }
        return $header;
    }

    // Get Page Footer
    public function getPageFooter(): string
    {
        $footer = $this->PageFooter;
        $this->pageDataRendered($footer);
        if ($footer != "") { // Footer exists, display
            $footer = '<div id="ew-page-footer">' . $footer . '</div>';
        }
        return $footer;
    }

    // Set field visibility
    public function setVisibility(): void
    {
        $this->id->setVisibility();
        $this->racket_name->setVisibility();
        $this->brand->setVisibility();
        $this->price_per_day->setVisibility();
        $this->image->setVisibility();
        $this->stock->setVisibility();
    }

    // Is lookup
    public function isLookup(): bool
    {
        return SameText(RouteAction(), Config("API_LOOKUP_ACTION"));
    }

    // Is AutoFill
    public function isAutoFill(): bool
    {
        return $this->isLookup() && SameText(Post("ajax"), "autofill");
    }

    // Is AutoSuggest
    public function isAutoSuggest(): bool
    {
        return $this->isLookup() && SameText(Post("ajax"), "autosuggest");
    }

    // Is modal lookup
    public function isModalLookup(): bool
    {
        return $this->isLookup() && SameText(Post("ajax"), "modal");
    }

    // Is terminated
    public function isTerminated(): bool
    {
        return $this->terminated;
    }

    /**
     * Terminate page
     *
     * @param ?string $url URL for redirection
     * @return void
     */
    public function terminate(?string $url = null): void
    {
        if ($this->terminated) {
            return;
        }
        global $httpContext;

        // Page is terminated
        $this->terminated = true;

        // Page Unload event
        if (method_exists($this, "pageUnload")) {
            $this->pageUnload();
        }
        DispatchEvent(new PageUnloadedEvent($this), PageUnloadedEvent::class);
        if (!IsApi() && method_exists($this, "pageRedirecting")) {
            $this->pageRedirecting($url);
        }

        // Return for API
        if (IsApi()) {
            if (!$this->Response) { // Show response for API
                $ar = array_merge($this->getMessages(), $url ? ["url" => GetUrl($url)] : []);
                $this->Response = new JsonResponse($ar);
            }
            $this->clearMessages(); // Clear messages for API request
            return;
        } else { // Check if response is JSON
            if (IsJsonResponse($this->Response)) { // Has JSON response
                $this->clearMessages();
                return;
            }
        }

        // Go to URL if specified
        if ($url !== null) {
            // Handle modal response
            if ($this->IsModal) { // Show as modal
                $pageName = GetPageName($url);
                $result = ["url" => GetUrl($url), "modal" => "1"];  // Assume return to modal for simplicity
                if (
                    SameString($pageName, GetPageName($this->getListUrl()))
                    || SameString($pageName, GetPageName($this->getViewUrl()))
                    || SameString($pageName, GetPageName(CurrentMasterTable()?->getViewUrl() ?? ""))
                ) { // List / View / Master View page
                    if (!SameString($pageName, GetPageName($this->getListUrl()))) { // Not List page
                        $result["caption"] = $this->getModalCaption($pageName);
                        $result["view"] = SameString($pageName, "RentRacketsView"); // If View page, no primary button
                    } else { // List page
                        $result["error"] = $this->getFailureMessage(); // List page should not be shown as modal => error
                    }
                } else { // Other pages (add messages and then clear messages)
                    $result = array_merge($this->getMessages(), ["modal" => "1"]);
                    // $this->clearMessages();
                }
                $this->Response = new JsonResponse($result);
            } else {
                $this->Response = new RedirectResponse(GetUrl($url), Config("REDIRECT_STATUS_CODE"));
            }
        }
        return; // Return to controller
    }

    // Get row(s) from array of entities
    protected function getRowsFromEntities(array $entities, bool $first = false): array
    {
        $rows = [];
        if (array_is_list($entities)) {
            foreach ($entities as $entity) {
                $row = $this->getRowFromEntity($entity);
                if ($first) {
                    return $row;
                } else {
                    $rows[] = $row;
                }
            }
        }
        return $rows;
    }

    // Get row from entity
    protected function getRowFromEntity(BaseEntity $entity): array
    {
        $row = [];
        foreach ($entity as $fldname => $val) {
            if ($this->TableName == Config("USER_TABLE_NAME") && $fldname == Config("PASSWORD_FIELD_NAME")) { // Skip user password field
                continue;
            }
            if (isset($this->Fields[$fldname]) && ($this->Fields[$fldname]->Visible || $this->Fields[$fldname]->IsPrimaryKey)) { // Primary key or Visible
                $fld = $this->Fields[$fldname];
                if ($fld->HtmlTag == "FILE") { // Upload field
                    if (IsEmpty($val)) {
                        $row[$fldname] = null;
                    } else {
                        if ($fld->DataType == DataType::BLOB) {
                            $url = FullUrl(GetApiUrl(Config("API_FILE_ACTION") .
                                "/" . $fld->TableVar . "/" . $fld->Param . "/" . $this->getKeyAsString($entity, Config("ROUTE_COMPOSITE_KEY_SEPARATOR"))));
                            $row[$fldname] = ["type" => ContentType($val), "url" => $url, "name" => $fld->Param . ContentExtension($val)];
                        } elseif (!$fld->UploadMultiple || !ContainsString($val, Config("MULTIPLE_UPLOAD_SEPARATOR"))) { // Single file
                            $key = SessionId() . ServerVar("ENCRYPTION_KEY");
                            $url = FullUrl(GetApiUrl(Config("API_FILE_ACTION") .
                                "/" . $fld->TableVar . "/" . Encrypt($fld->uploadPath() . $val, $key)));
                            $row[$fldname] = ["type" => MimeContentType($val), "url" => $url, "name" => $val];
                        } else { // Multiple files
                            $files = explode(Config("MULTIPLE_UPLOAD_SEPARATOR"), $val);
                            $ar = [];
                            $key = SessionId() . ServerVar("ENCRYPTION_KEY");
                            foreach ($files as $file) {
                                if (!IsEmpty($file)) {
                                    $url = FullUrl(GetApiUrl(Config("API_FILE_ACTION") .
                                        "/" . $fld->TableVar . "/" . Encrypt($fld->uploadPath() . $file, $key)));
                                    $ar[] = ["type" => MimeContentType($file), "url" => $url, "name" => $file];
                                }
                            }
                            $row[$fldname] = $ar;
                        }
                    }
                } else {
                    if ($val instanceof DateTimeInterface) {
                        $val = $val->format(DATE_ATOM);
                    }
                    $row[$fldname] = $val;
                }
            }
        }
        return $row;
    }

    // Hide fields for add/edit
    protected function hideFieldsForAddEdit(): void
    {
        if ($this->isAdd() || $this->isCopy() || $this->isGridAdd()) {
            $this->id->Visible = false;
        }
    }

    // Lookup data
    public function lookup(array $req = []): array
    {
        // Get lookup object
        $fieldName = $req["field"] ?? null;
        if (!$fieldName) {
            return [];
        }
        $fld = $this->Fields[$fieldName];
        $lookup = $fld->Lookup;
        $name = $req["name"] ?? "";
        if (ContainsString($name, "query_builder_rule")) {
            $lookup->FilterFields = []; // Skip parent fields if any
        }

        // Get lookup parameters
        $lookupType = $req["ajax"] ?? "unknown";
        $pageSize = -1;
        $offset = -1;
        $searchValue = "";
        if (SameText($lookupType, "modal") || SameText($lookupType, "filter")) {
            $searchValue = $req["q"] ?? $req["sv"] ?? "";
            $pageSize = $req["n"] ?? $req["recperpage"] ?? 10;
        } elseif (SameText($lookupType, "autosuggest")) {
            $searchValue = $req["q"] ?? "";
            $pageSize = $req["n"] ?? -1;
            $pageSize = is_numeric($pageSize) ? (int)$pageSize : -1;
            if ($pageSize <= 0) {
                $pageSize = Config("AUTO_SUGGEST_MAX_ENTRIES");
            }
        }
        $start = $req["start"] ?? -1;
        $start = is_numeric($start) ? (int)$start : -1;
        $page = $req["page"] ?? -1;
        $page = is_numeric($page) ? (int)$page : -1;
        $offset = $start >= 0 ? $start : ($page > 0 && $pageSize > 0 ? ($page - 1) * $pageSize : 0);
        $userSelect = Decrypt($req["s"] ?? "");
        $userFilter = Decrypt($req["f"] ?? "");
        $userOrderBy = Decrypt($req["o"] ?? "");
        $keys = $req["keys"] ?? null;
        $lookup->LookupType = $lookupType; // Lookup type
        $lookup->FilterValues = []; // Clear filter values first
        if ($keys !== null) { // Selected records from modal
            if (is_array($keys)) {
                $keys = implode(Config("MULTIPLE_OPTION_SEPARATOR"), $keys);
            }
            $lookup->FilterFields = []; // Skip parent fields if any
            $lookup->FilterValues[] = $keys; // Lookup values
            $pageSize = -1; // Show all records
        } else { // Lookup values
            $lookup->FilterValues[] = $req["v0"] ?? $req["lookupValue"] ?? "";
        }
        $cnt = is_array($lookup->FilterFields) ? count($lookup->FilterFields) : 0;
        for ($i = 1; $i <= $cnt; $i++) {
            $lookup->FilterValues[] = $req["v" . $i] ?? "";
        }
        $lookup->SearchValue = $searchValue;
        $lookup->PageSize = $pageSize;
        $lookup->Offset = $offset;
        if ($userSelect != "") {
            $lookup->UserSelect = $userSelect;
        }
        if ($userFilter != "") {
            $lookup->UserFilter = $userFilter;
        }
        if ($userOrderBy != "") {
            $lookup->UserOrderBy = $userOrderBy;
        }
        return $lookup->toJson($this); // Use settings from current page
    }

    /**
     * Page init
     *
     * @return void
     */
    public function init(): void
    {
    }

    /**
     * Page run
     *
     * @return void
     */
    public function run(): void
    {
        global $httpContext;

        // Is modal
        $this->IsModal = IsModal();
        $this->UseLayout = $this->UseLayout && !$this->IsModal;

        // Use layout
        $this->UseLayout = $this->UseLayout && ParamBool(Config("PAGE_LAYOUT"), true);

        // View
        $this->View = Get(Config("VIEW"));
        $this->CurrentAction = Param("action"); // Set up current action
        $this->setVisibility();

        // Global Page Loading event (in userfn*.php)
        DispatchEvent(new PageLoadingEvent($this), PageLoadingEvent::class);

        // Page Load event
        if (method_exists($this, "pageLoad")) {
            $this->pageLoad();
        }

        // Hide fields for add/edit
        if (!$this->UseAjaxActions) {
            $this->hideFieldsForAddEdit();
        }
        // Use inline delete
        if ($this->UseAjaxActions) {
            $this->InlineDelete = true;
        }

        // Check modal
        if ($this->IsModal) {
            $httpContext["SkipHeaderFooter"] = true;
        }
        $this->IsMobileOrModal = IsMobile() || $this->IsModal;
        $loaded = $this->CurrentRecord instanceof BaseEntity;
        $postBack = false;

        // Set up current action and primary key
        if (IsApi()) {
            // Load record
            if ($loaded) {
                // Load key values
                if (($keyValue = Route("id") ?? Get("id")) !== null) {
                    $this->id->setQueryStringValue($keyValue);
                    $this->id->setOldValue($this->id->QueryStringValue);
                } elseif (($keyValue = Post("id")) !== null) {
                    $this->id->setFormValue($keyValue);
                    $this->id->setOldValue($this->id->FormValue);
                } elseif (($keyValue = Key(0)) !== null) {
                    $this->id->setQueryStringValue($keyValue);
                    $this->id->setOldValue($this->id->QueryStringValue);
                }
                $loaded = $this->loadRow();
            } else {
                $this->setFailureMessage($this->language->phrase("NoRecord")); // Set no record message
                $this->terminate();
                return;
            }
            $this->CurrentAction = "update"; // Update record directly
            $this->OldKey = $this->getKey(true); // Get from CurrentValue
            $postBack = true;
        } else {
            if ($this->CurrentAction = Post("action")) { // Get action code
                if (!$this->isShow()) { // Not reload record, handle as postback
                    $postBack = true;
                }

                // Get key from Form
                $this->setKey($this->getFormOldKey(), $this->isShow());
            } else {
                $this->CurrentAction = "show"; // Default action is display

                // Load key from query string or route value
                $loadByQuery = false;
                if (($keyValue = Route("id") ?? Get("id")) !== null) {
                    $this->id->setQueryStringValue($keyValue);
                    $loadByQuery = true;
                } else {
                    $this->id->CurrentValue = null;
                }
            }

            // Load result set
            if ($this->isShow()) {
                if (!$this->CurrentRecord) { // No record found
                    if (!$this->peekSuccessMessage() && !$this->peekFailureMessage()) {
                        $this->setFailureMessage($this->language->phrase("NoRecord")); // Set no record message
                    }
                    $this->terminate("RentRacketsList"); // Return to list page
                    return;
                } else { // Load current row
                    $loaded = $this->loadRow();
                }
                $this->OldKey = $loaded ? $this->getKey(true) : []; // Get from CurrentValue
            }
        }

        // Process form if post back
        if ($postBack) {
            $this->loadFormValues(); // Get form values
        }

        // Validate form if post back
        if ($postBack) {
            if (!$this->validateForm()) {
                $this->EventCancelled = true; // Event cancelled
                if (IsApi()) {
                    $this->Response = new JsonResponse(["success" => false, "version" => PRODUCT_VERSION, "validation" => $this->getValidationErrors()]);
                    $this->terminate();
                    return;
                } else {
                    $this->restoreFormValues();
                    $this->CurrentAction = ""; // Form error, reset action
                }
            }
        }

        // Perform current action
        switch ($this->CurrentAction) {
            case "show": // Get a record to display
                if (!$this->IsModal) { // Normal edit page
                    if (!$loaded) {
                        if (!$this->peekSuccessMessage() && !$this->peekFailureMessage()) {
                            $this->setFailureMessage($this->language->phrase("NoRecord")); // Set no record message
                        }
                        $this->terminate("RentRacketsList"); // Return to list page
                        return;
                    }
                } else { // Modal edit page
                    if (!$loaded) { // Load record based on key
                        if (!$this->peekFailureMessage()) {
                            $this->setFailureMessage($this->language->phrase("NoRecord")); // No record found
                        }
                        $this->terminate("RentRacketsList"); // No matching record, return to list
                        return;
                    }
                } // End modal checking
                break;
            case "update": // Update
                $returnUrl = $this->getReturnUrl();
                if (GetPageName($returnUrl) == "RentRacketsList") {
                    $returnUrl = $this->addMasterUrl($returnUrl); // List page, return to List page with correct master key if necessary
                }
                if ($this->editRow()) { // Update record based on key
                    CleanUploadTempPaths(SessionId());
                    if (!$this->peekSuccessMessage()) {
                        $this->setSuccessMessage($this->language->phrase("UpdateSuccess")); // Update success
                    }

                    // Handle UseAjaxActions with return page
                    if ($this->IsModal && $this->UseAjaxActions) {
                        $this->IsModal = false;
                        if (GetPageName($returnUrl) != "RentRacketsList") {
                            FlashBag()->add("X-Return-Url", $returnUrl); // Save return URL
                            $returnUrl = "RentRacketsList"; // Return list page content
                        }
                    }
                    if (IsJsonResponse()) {
                        $this->terminate();
                    } else {
                        if ($this->IsModal && GetPageName($returnUrl) != "RentRacketsList") {
                            $this->IsModal = false;
                            FlashBag()->add("X-Refresh-Url", GetUrl("RentRacketsList")); // Refresh page after edit before going to return page
                            $returnUrl = BuildUrl($returnUrl, "modal=1"); // Redirection, but BaseController will only add the header if not redirection.
                        }
                        $this->terminate($returnUrl); // Return to caller
                    }
                    return;
                } elseif (IsApi()) { // API request, return
                    $this->terminate();
                    return;
                } elseif ($this->IsModal && $this->UseAjaxActions) { // Return JSON error message
                    $this->Response = new JsonResponse(["success" => false, "validation" => $this->getValidationErrors(), "error" => $this->getFailureMessage()]);
                    $this->terminate();
                    return;
                } elseif (($this->peekFailureMessage()[0] ?? "") == $this->language->phrase("NoRecord")) {
                    $this->terminate($returnUrl); // Return to caller
                    return;
                } else {
                    $this->EventCancelled = true; // Event cancelled
                    $this->restoreFormValues(); // Restore form values if update failed
                }
        }

        // Set up Breadcrumb
        $this->setupBreadcrumb();

        // Render row
        $this->renderRow(
            RowType::EDIT
        );

        // Set LoginStatus / Page_Rendering / Page_Render
        if (!IsApi() && !$this->isTerminated()) {
            // Pass login status to client side
            SetClientVar("login", LoginStatus());

            // Global Page Rendering event (in userfn*.php)
            DispatchEvent(new PageRenderingEvent($this), PageRenderingEvent::class);

            // Page Render event
            if (method_exists($this, "pageRender")) {
                $this->pageRender();
            }

            // Render search option
            if (method_exists($this, "renderSearchOptions")) {
                $this->renderSearchOptions();
            }
        }
    }

    // Get upload files
    protected function getUploadFiles(): void
    {
    }

    // Load form values
    protected function loadFormValues(): void
    {
        $validate = !Config("SERVER_VALIDATE");

        // id
        if (!$this->id->IsDetailKey) {
            $val = $this->hasInputValue($this->id) ? $this->getInputValue($this->id) : null;
            $this->id->setFormValue($val);
        }

        // racket_name
        if (!$this->racket_name->IsDetailKey) {
            $val = $this->hasInputValue($this->racket_name) ? $this->getInputValue($this->racket_name) : null;
            $this->racket_name->setFormValue($val);
        }

        // brand
        if (!$this->brand->IsDetailKey) {
            $val = $this->hasInputValue($this->brand) ? $this->getInputValue($this->brand) : null;
            $this->brand->setFormValue($val);
        }

        // price_per_day
        if (!$this->price_per_day->IsDetailKey) {
            $val = $this->hasInputValue($this->price_per_day) ? $this->getInputValue($this->price_per_day) : null;
            $this->price_per_day->setFormValue($val, true, $validate);
        }

        // image
        if (!$this->image->IsDetailKey) {
            $val = $this->hasInputValue($this->image) ? $this->getInputValue($this->image) : null;
            $this->image->setFormValue($val);
        }

        // stock
        if (!$this->stock->IsDetailKey) {
            $val = $this->hasInputValue($this->stock) ? $this->getInputValue($this->stock) : null;
            $this->stock->setFormValue($val, true, $validate);
        }
    }

    // Restore form values
    public function restoreFormValues(): void
    {
        $this->id->CurrentValue = $this->id->FormValue;
        $this->racket_name->CurrentValue = $this->racket_name->FormValue;
        $this->brand->CurrentValue = $this->brand->FormValue;
        $this->price_per_day->CurrentValue = $this->price_per_day->FormValue;
        $this->image->CurrentValue = $this->image->FormValue;
        $this->stock->CurrentValue = $this->stock->FormValue;
    }

    /**
     * Load row based on key values
     *
     * @return bool
     */
    public function loadRow(): bool
    {
        $result = $this->CurrentRecord !== null;
        if ($result) {
            $this->loadRowValues($this->CurrentRecord); // Load row values
        }
        return $result;
    }

    /**
     * Load row values from result set or record
     *
     * @param ?BaseEntity $row Record
     * @return void
     */
    public function loadRowValues(?BaseEntity $row = null): void
    {
        if ($row instanceof BaseEntity) { // Get array from entity
        }
        $row ??= $this->newRow();

        // Call Row Selected event
        $this->rowSelected($row);
        $this->id->setDbValue($row['id']);
        $this->racket_name->setDbValue($row['racket_name']);
        $this->brand->setDbValue($row['brand']);
        $this->price_per_day->setDbValue($row['price_per_day']);
        $this->image->setDbValue($row['image']);
        $this->stock->setDbValue($row['stock']);
    }

    /**
     * Return a row with default values
     *
     * @return BaseEntity
     */
    protected function newRow(): BaseEntity
    {
        $row = new $this->EntityClass();
        if (!IsEmpty($this->id->DefaultValue)) {
            $row['id'] = intval($this->id->DefaultValue);
        }
        if (!IsEmpty($this->racket_name->DefaultValue)) {
            $row['racket_name'] = strval($this->racket_name->DefaultValue);
        }
        if (!IsEmpty($this->brand->DefaultValue)) {
            $row['brand'] = strval($this->brand->DefaultValue);
        }
        if (!IsEmpty($this->price_per_day->DefaultValue)) {
            $row['price_per_day'] = strval($this->price_per_day->DefaultValue);
        }
        if (!IsEmpty($this->image->DefaultValue)) {
            $row['image'] = strval($this->image->DefaultValue);
        }
        if (!IsEmpty($this->stock->DefaultValue)) {
            $row['stock'] = intval($this->stock->DefaultValue);
        }
        return $row;
    }

    // Load old record
    protected function loadOldRecord(): ?object
    {
        if ($this->CurrentRecord !== null) {
            $this->loadRowValues($this->CurrentRecord);
            return $this->CurrentRecord;
        }
        $this->loadRowValues(); // Load default row values
        return null;
    }

    /**
     * Render row
     *
     * @param RowType $rowType Row type
     * @param bool $resetAttributes Reset attributes
     * @return void
     */
    public function renderRow(RowType $rowType = RowType::VIEW, bool $resetAttributes = true): void
    {
        global $httpContext;

        // Set up row type
        $this->RowType = $rowType;

        // Reset attributes
        if ($resetAttributes) {
            $this->resetAttributes();
        }

        // Initialize URLs

        // Call Row_Rendering event
        $this->rowRendering();

        // Common render codes for all row types

        // id
        $this->id->RowCssClass = "row";

        // racket_name
        $this->racket_name->RowCssClass = "row";

        // brand
        $this->brand->RowCssClass = "row";

        // price_per_day
        $this->price_per_day->RowCssClass = "row";

        // image
        $this->image->RowCssClass = "row";

        // stock
        $this->stock->RowCssClass = "row";

        // View row
        if ($this->RowType == RowType::VIEW) {
            // id
            $this->id->ViewValue = $this->id->CurrentValue;

            // racket_name
            $this->racket_name->ViewValue = $this->racket_name->CurrentValue;

            // brand
            $this->brand->ViewValue = $this->brand->CurrentValue;

            // price_per_day
            $this->price_per_day->ViewValue = $this->price_per_day->CurrentValue;
            $this->price_per_day->ViewValue = FormatNumber($this->price_per_day->ViewValue, $this->price_per_day->formatPattern());

            // image
            $this->image->ViewValue = $this->image->CurrentValue;

            // stock
            $this->stock->ViewValue = $this->stock->CurrentValue;
            $this->stock->ViewValue = FormatNumber($this->stock->ViewValue, $this->stock->formatPattern());

            // id
            $this->id->HrefValue = "";

            // racket_name
            $this->racket_name->HrefValue = "";

            // brand
            $this->brand->HrefValue = "";

            // price_per_day
            $this->price_per_day->HrefValue = "";

            // image
            $this->image->HrefValue = "";

            // stock
            $this->stock->HrefValue = "";
        } elseif ($this->RowType == RowType::EDIT) {
            // id
            $this->id->setupEditAttributes();
            $this->id->EditValue = $this->id->CurrentValue;

            // racket_name
            $this->racket_name->setupEditAttributes();
            $this->racket_name->EditValue = !$this->racket_name->Raw ? HtmlDecode($this->racket_name->CurrentValue) : $this->racket_name->CurrentValue;
            $this->racket_name->PlaceHolder = RemoveHtml($this->racket_name->caption());

            // brand
            $this->brand->setupEditAttributes();
            $this->brand->EditValue = !$this->brand->Raw ? HtmlDecode($this->brand->CurrentValue) : $this->brand->CurrentValue;
            $this->brand->PlaceHolder = RemoveHtml($this->brand->caption());

            // price_per_day
            $this->price_per_day->setupEditAttributes();
            $this->price_per_day->EditValue = $this->price_per_day->CurrentValue;
            $this->price_per_day->PlaceHolder = RemoveHtml($this->price_per_day->caption());
            if (strval($this->price_per_day->EditValue) != "" && is_numeric($this->price_per_day->EditValue)) {
                $this->price_per_day->EditValue = FormatNumber($this->price_per_day->EditValue, null);
            }

            // image
            $this->image->setupEditAttributes();
            $this->image->EditValue = !$this->image->Raw ? HtmlDecode($this->image->CurrentValue) : $this->image->CurrentValue;
            $this->image->PlaceHolder = RemoveHtml($this->image->caption());

            // stock
            $this->stock->setupEditAttributes();
            $this->stock->EditValue = $this->stock->CurrentValue;
            $this->stock->PlaceHolder = RemoveHtml($this->stock->caption());
            if (strval($this->stock->EditValue) != "" && is_numeric($this->stock->EditValue)) {
                $this->stock->EditValue = FormatNumber($this->stock->EditValue, null);
            }

            // Edit refer script

            // id
            $this->id->HrefValue = "";

            // racket_name
            $this->racket_name->HrefValue = "";

            // brand
            $this->brand->HrefValue = "";

            // price_per_day
            $this->price_per_day->HrefValue = "";

            // image
            $this->image->HrefValue = "";

            // stock
            $this->stock->HrefValue = "";
        }
        if ($this->RowType == RowType::ADD || $this->RowType == RowType::EDIT || $this->RowType == RowType::SEARCH) { // Add/Edit/Search row
            $this->setupFieldTitles();
        }

        // Call Row Rendered event
        if ($this->RowType != RowType::AGGREGATEINIT) {
            $this->rowRendered();
        }
    }

    // Validate form
    protected function validateForm(): bool
    {
        // Check if validation required
        if (!Config("SERVER_VALIDATE")) {
            return true;
        }
        $validateForm = true;
        if ($this->id->Visible) {
            if ($this->id->Required) {
                if (!$this->id->IsDetailKey && IsEmpty($this->id->FormValue)) {
                    $this->id->addErrorMessage(str_replace("%s", $this->id->caption(), $this->id->RequiredErrorMessage));
                }
            }
        }
        if ($this->racket_name->Visible) {
            if ($this->racket_name->Required) {
                if (!$this->racket_name->IsDetailKey && IsEmpty($this->racket_name->FormValue)) {
                    $this->racket_name->addErrorMessage(str_replace("%s", $this->racket_name->caption(), $this->racket_name->RequiredErrorMessage));
                }
            }
        }
        if ($this->brand->Visible) {
            if ($this->brand->Required) {
                if (!$this->brand->IsDetailKey && IsEmpty($this->brand->FormValue)) {
                    $this->brand->addErrorMessage(str_replace("%s", $this->brand->caption(), $this->brand->RequiredErrorMessage));
                }
            }
        }
        if ($this->price_per_day->Visible) {
            if ($this->price_per_day->Required) {
                if (!$this->price_per_day->IsDetailKey && IsEmpty($this->price_per_day->FormValue)) {
                    $this->price_per_day->addErrorMessage(str_replace("%s", $this->price_per_day->caption(), $this->price_per_day->RequiredErrorMessage));
                }
            }
            if (!CheckNumber($this->price_per_day->FormValue)) {
                $this->price_per_day->addErrorMessage($this->price_per_day->getErrorMessage(false));
            }
        }
        if ($this->image->Visible) {
            if ($this->image->Required) {
                if (!$this->image->IsDetailKey && IsEmpty($this->image->FormValue)) {
                    $this->image->addErrorMessage(str_replace("%s", $this->image->caption(), $this->image->RequiredErrorMessage));
                }
            }
        }
        if ($this->stock->Visible) {
            if ($this->stock->Required) {
                if (!$this->stock->IsDetailKey && IsEmpty($this->stock->FormValue)) {
                    $this->stock->addErrorMessage(str_replace("%s", $this->stock->caption(), $this->stock->RequiredErrorMessage));
                }
            }
            if (!CheckInteger($this->stock->FormValue)) {
                $this->stock->addErrorMessage($this->stock->getErrorMessage(false));
            }
        }

        // Return validate result
        $validateForm = $validateForm && !$this->hasInvalidFields();

        // Call Form_CustomValidate event
        $formCustomError = "";
        $validateForm = $validateForm && $this->formCustomValidate($formCustomError);
        if ($formCustomError != "") {
            $this->setFailureMessage($formCustomError);
        }
        return $validateForm;
    }

    // Update record based on key values
    protected function editRow(): ?bool
    {
        $row = $this->CurrentRecord; // Use current record for update
        if ($row === null) {
            $this->setFailureMessage($this->language->phrase("NoRecord")); // Set no record message
            return false; // Update Failed
        }

        // Clone entity (as old row)
        $oldRow = clone $row;

        // Update entity
        $newRow = $this->getEditRow($row);

        // Validate constraints
        $errors = Validate($newRow);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->fieldByPropertyName($error->getPropertyPath())?->addErrorMessage($error->getMessage());
            }
            return false;
        }

        // Get Entity Manager
        $em = $this->getEntityManager();

        // Call Row Updating event
        $update = method_exists($this, "rowUpdating") ? $this->rowUpdating($oldRow, $newRow) : true;
        $oldPk = $oldRow->identifierValues();
        $newPk = $newRow->identifierValues();
        if ($update) {
            try {
                $updateTableRow = null;
                if ($oldPk === $newPk) { // PK unchanged
                    if ($this->UpdateTable && $this->UpdateTable != $this->TableName) { // Use Update Table if set
                        $id = $oldRow->identifierValues();
                        $updateTableRow = $em->find($this->UpdateTableEntityClass, $id);
                        if (!$updateTableRow) {
                            throw new \RuntimeException("Cannot update: related entity not found.");
                        }
                        $updateTableRow->fromArray($newRow->toArray());
                        $em->detach($newRow);
                    }
                } else {
                    $this->handlePrimaryKeyChange($oldRow, $newRow);
                }
                $em->flush();
                $updated = true;
            } catch (Exception $e) {
                $this->dispatcher->dispatch(new RowUpdateFailedEvent($updateTableRow ?? $newRow, $e));
                $this->setFailureMessage($e->getMessage());
                $updated = false;
            }
            if ($updated) {
            }
        } else {
            if ($update === false) {
                if ($this->peekSuccessMessage() || $this->peekFailureMessage()) {
                    // Use the message, do nothing
                } elseif ($this->CancelMessage != "") {
                    $this->setFailureMessage($this->CancelMessage);
                    $this->CancelMessage = "";
                } else {
                    $this->setFailureMessage($this->language->phrase("UpdateCancelled"));
                }
            } elseif ($update === null) { // Skip update record
                $em->detach($newRow);
            }
            $updated = $update;
        }

        // Call Row_Updated event
        if ($updated && method_exists($this, "rowUpdated")) {
            $this->rowUpdated($oldRow, $newRow);
        }

        // Write JSON response
        if (IsJsonResponse() && $updated) {
            $row = $this->getRowsFromEntities([$newRow], true);
            $table = $this->TableVar;
            $this->Response = new JsonResponse(["success" => true, "action" => Config("API_EDIT_ACTION"), $table => $row]);
        }
        return $updated;
    }

    /**
     * Handle primary key change
     *
     * @param BaseEntity $oldRow
     * @param BaseEntity $newRow
     *
     * @return void
     */
    protected function handlePrimaryKeyChange(BaseEntity $oldRow, BaseEntity $newRow): void
    {
        $em = $this->getEntityManager();
        $meta = $newRow->metadata();
        $uow = $em->getUnitOfWork();

        // Recompute changes for lifecycle events
        $uow->recomputeSingleEntityChangeSet($meta, $newRow);
        $changeSet = $uow->getEntityChangeSet($newRow);

        // Replace entity and meta data if UpdateTable is different from current table
        if ($this->UpdateTable && $this->UpdateTable != $this->TableName) {
            $data = $newRow->toArray();
            $em->detach($newRow);
            $newRow = $this->UpdateTableEntityClass::createFromArray($data);
            $meta = $newRow->metadata();
        }

        // Trigger preUpdate if there are changes
        if ($changeSet) {
            $em->getEventManager()->dispatchEvent(
                \Doctrine\ORM\Events::preUpdate,
                new \Doctrine\ORM\Event\PreUpdateEventArgs($newRow, $em, $changeSet)
            );
            //$em->flush(); // Don't flush here
        }

        // Detach entity to avoid affecting UnitOfWork
        $em->detach($newRow);

        // Prepare data for DBAL update
        $data = [];
        foreach ($changeSet as $field => [, $newValue]) {
            if ($meta->hasField($field)) {
                $data[$newRow->columnName($field)] = $newValue;
            }
        }

        // Build criteria using old identifier values (safe for any entity)
        $criteria = [];
        foreach ($meta->getIdentifierValues($oldRow) as $field => $value) {
            $criteria[$oldRow->columnName($field)] = $value;
        }

        // Execute DBAL update
        if ($data) {
            $tableName = $meta->getTableName();
            $table = ServiceLocator($tableName) ?? $this;
            $affected = $table->update($data, $criteria);

            // Trigger postUpdate only if something was actually updated
            if ($affected > 0) {
                $em->getEventManager()->dispatchEvent(
                    \Doctrine\ORM\Events::postUpdate,
                    new \Doctrine\ORM\Event\PostUpdateEventArgs($newRow, $em, $changeSet)
                );
                //$em->flush(); // Don't flush here
            }
        }
    }

    /**
     * Get edit row
     *
     * @return BaseEntity
     */
    protected function getEditRow(BaseEntity $newRow): BaseEntity
    {
        // Load DbValue
        $this->loadDbValues($newRow);

        // racket_name
        if (!$this->racket_name->ReadOnly) {
            $newRow->setRacketName($this->racket_name->setDbValueDef($this->racket_name->CurrentValue));
        }

        // brand
        if (!$this->brand->ReadOnly) {
            $newRow->setBrand($this->brand->setDbValueDef($this->brand->CurrentValue));
        }

        // price_per_day
        if (!$this->price_per_day->ReadOnly) {
            $newRow->setPricePerDay($this->price_per_day->setDbValueDef($this->price_per_day->CurrentValue));
        }

        // image
        if (!$this->image->ReadOnly) {
            $newRow->setImage($this->image->setDbValueDef($this->image->CurrentValue));
        }

        // stock
        if (!$this->stock->ReadOnly) {
            $newRow->setStock($this->stock->setDbValueDef($this->stock->CurrentValue));
        }
        $this->Fields->setCurrentValues($newRow);
        return $newRow;
    }

    // Set up Breadcrumb
    protected function setupBreadcrumb(): void
    {
        $breadcrumb = Breadcrumb();
        $url = CurrentUrl();
        $breadcrumb->add("list", $this->TableVar, $this->addMasterUrl("RentRacketsList"), "", $this->TableVar, true);
        $pageId = "edit";
        $breadcrumb->add("edit", $pageId, $url);
    }

    // Setup lookup options
    public function setupLookupOptions(DbField $fld): void
    {
        if ($fld->Lookup && $fld->Lookup->Options === null) {
            // Get default connection and filter
            $conn = $this->getConnection();
            $lookupFilter = "";

            // No need to check any more
            $fld->Lookup->Options = [];

            // Set up lookup SQL and connection
            switch ($fld->FieldVar) {
                default:
                    $lookupFilter = "";
                    break;
            }

            // Always call to Lookup->getSql so that user can setup Lookup->Options in Lookup_Selecting server event
            $qb = $fld->Lookup->getSqlBuilder(false, "", $lookupFilter, $this);

            // Set up lookup cache
            if (!$fld->hasLookupOptions() && $fld->UseLookupCache && $qb != null && count($fld->Lookup->Options) == 0 && count($fld->Lookup->FilterFields) == 0) {
                $totalCnt = $this->getRecordCount($qb, $conn);
                if ($totalCnt > $fld->LookupCacheCount) { // Total count > cache count, do not cache
                    return;
                }

                // Define a structured and consistent cache key prefix
                $cachePrefix = "lookup.result." . Container($fld->Lookup->LinkTable)->TableVar . ".";

                // Generate a unique cache key using SQL and parameters
                $sqlHash = hash("sha256", $qb->getSQL() . serialize($qb->getParameters()));
                $cacheKey = $cachePrefix . $sqlHash;

                // Fetch rows from cache or database
                $rows = $this->cache->get($cacheKey, fn (ItemInterface $item) => $qb->executeQuery()->fetchAllAssociative());
                $ar = [];
                foreach ($rows as $row) {
                    $row = $fld->Lookup->renderViewRow($row);
                    $key = $row["lf"];
                    if (IsFloatType($fld->Type)) { // Handle float field
                        $key = (float)$key;
                    }
                    $ar[strval($key)] = $row;
                }
                $fld->Lookup->Options = $ar;
            }
        }
    }

    // Set up starting record parameters
    public function setupStartRecord(): void
    {
        $infiniteScroll = false;

        // Set up StartRecord
        $pagerTable = Get(Config("TABLE_PAGER_TABLE_NAME"));
        if ($pagerTable && $pagerTable != $this->TableVar) { // Skip if not paging for this table
            $this->StartRecord = $this->getStartRecordNumber();
        } else { // Set up from query string parameter
            $pageNumber = GetInt(Config("TABLE_PAGE_NUMBER"));
            $startRec = GetInt(Config("TABLE_START_REC"));
            $this->PageNumber = $pageNumber ?? $startRec ?? 0; // Record number = page number or start record
            if ($this->PageNumber > 0) {
                $this->StartRecord = $this->PageNumber;
            } else {
                $this->StartRecord = $this->getStartRecordNumber();
            }
        }

        // Check if correct start record counter
        if (!is_numeric($this->StartRecord) || intval($this->StartRecord) <= 0) { // Avoid invalid start record counter
            $this->StartRecord = 1; // Reset start record counter
        } elseif (($this->StartRecord - 1) % $this->DisplayRecords != 0) {
            $this->StartRecord = (int)(($this->StartRecord - 1) / $this->DisplayRecords) * $this->DisplayRecords + 1; // Point to page boundary
        }
        if (!$infiniteScroll) {
            $this->setStartRecordNumber($this->StartRecord);
        }
    }

    // Get page count
    public function pageCount(): int
    {
        return ceil($this->TotalRecords / $this->DisplayRecords);
    }

    // Page Load event
    public function pageLoad(): void
    {
        //Log("Page Load");
    }

    // Page Unload event
    public function pageUnload(): void
    {
        //Log("Page Unload");
    }

    // Page Redirecting event
    public function pageRedirecting(?string &$url): void
    {
        // Example:
        //$url = "your URL";
    }

    // Message Showing event
    // $type = ''|'success'|'danger'|'warning'
    public function messageShowing(string &$message, string $type): void
    {
        if ($type == "success") {
            //$message = "your success message";
        } elseif ($type == "danger") {
            //$message = "your failure message";
        } elseif ($type == "warning") {
            //$message = "your warning message";
        } else {
            //$message = "your message";
        }
    }

    // Page Render event
    public function pageRender(): void
    {
        //Log("Page Render");
    }

    // Page Data Rendering event
    public function pageDataRendering(string &$header): void
    {
        // Example:
        //$header = "your header";
    }

    // Page Data Rendered event
    public function pageDataRendered(string &$footer): void
    {
        // Example:
        //$footer = "your footer";
    }

    // Page Breaking event
    public function pageBreaking(bool &$break, string &$content): void
    {
        // Example:
        //$break = false; // Skip page break, or
        //$content = "<div style=\"break-after:page;\"></div>"; // Modify page break content
    }

    // Form Custom Validate event
    public function formCustomValidate(string &$customError): bool
    {
        // Return error message in $customError
        return true;
    }
}
