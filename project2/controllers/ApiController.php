<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use RuntimeException;
use Exception;

/**
 * API controller
 */
#[Route('/api')]
class ApiController
{
    /**
     * Constructor
     */
    public function __construct(
        protected Language $language,
        protected AdvancedSecurity $security,
        protected AppServiceLocator $locator,
        protected UserProfile $profile,
        protected ParameterBagInterface $parameters,
    ) {
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
     * Get class short name by alias
     *
     * @param string $alias Class alias
     * @return ?string Class short name
     */
    public function getClassNameByAlias(string $alias): ?string
    {
        $alias = strtolower($alias);
        $aliasMap = $this->parameters->get('app.alias_to_class_map');
        $map = array_change_key_case($aliasMap, CASE_LOWER);
        return $map[$alias] ?? null;
    }

    /**
     * Process page
     */
    public function processPage(PageInterface $page, Entity|array|null $resolved = null): Response
    {
        // Inject resolved data
        if ($resolved instanceof Entity) {
            $page->CurrentRecord = $resolved;
        } elseif (is_array($resolved) && array_is_list($resolved)) {
            $page->Records = $resolved;
        }
        try {
            $page->run();
            if ($page->Response instanceof Response) {
                return $page->Response;
            }
            throw new RuntimeException("Page did not return a valid response.");
        } finally {
            $page->terminate(); // Ensure cleanup always happens
        }
    }

    /**
     * List
     */
    #[Route('/list/{table}', methods: ['GET', 'OPTIONS'], name: 'api.list')]
    public function list(string $table): Response
    {
        $tableVar = $this->getClassNameByAlias($table);
        $pageName = $tableVar . 'List';
        if (!$this->locator->has($pageName)) {
            throw new NotFoundHttpException("Page '$pageName' not found");
        }
        $page = $this->locator->get($pageName);
        $page->init();

        // Load records and total record count
        $page->TotalRecords = $page->listRecordCount();
        $page->Records = $page->loadRecords($page->StartRecord - 1, $page->DisplayRecords);
        return $this->processPage($page);
    }

    /**
     * View
     */
    #[Route('/view/{table}/{key}', methods: ['GET', 'OPTIONS'], requirements: ['key' => Requirement::CATCH_ALL], name: 'api.view')]
    public function view(string $table, ?Entity $entity): Response
    {
        if ($entity === null) {
            return new JsonResponse(["success" => false, "version" => PRODUCT_VERSION, "failureMessage" => $this->language->phrase("NoRecord", true) ]);
        }
        $tableVar = $this->getClassNameByAlias($table);
        $pageName = $tableVar . 'View';
        if (!$this->locator->has($pageName)) {
            throw new NotFoundHttpException("Page '$pageName' not found");
        }
        $page = $this->locator->get($pageName);
        $page->init();
        return $this->processPage($page, $entity);
    }

    /**
     * Add
     */
    #[Route('/add/{table}/{key?}', methods: ['POST', 'OPTIONS'], name: 'api.add')]
    public function add(string $table, ?Entity $entity): Response
    {
        $tableVar = $this->getClassNameByAlias($table);
        $pageName = $tableVar . 'Add';
        if (!$this->locator->has($pageName)) {
            throw new NotFoundHttpException("Page '$pageName' not found");
        }
        $page = $this->locator->get($pageName);
        $page->init();
        return $this->processPage($page, $entity);
    }

    /**
     * Edit
     */
    #[Route('/edit/{table}/{key}', methods: ['POST', 'OPTIONS'], requirements: ['key' => Requirement::CATCH_ALL], name: 'api.edit')]
    public function edit(string $table, ?Entity $entity): Response
    {
        if ($entity === null) {
            return new JsonResponse(["success" => false, "version" => PRODUCT_VERSION, "failureMessage" => $this->language->phrase("NoRecord", true) ]);
        }
        $tableVar = $this->getClassNameByAlias($table);
        $pageName = $tableVar . 'Edit';
        if (!$this->locator->has($pageName)) {
            throw new NotFoundHttpException("Page '$pageName' not found");
        }
        $page = $this->locator->get($pageName);
        $page->init();
        return $this->processPage($page, $entity);
    }

    /**
     * delete
     */
    #[Route('/delete/{table}/{key?}', methods: ['GET', 'POST', 'DELETE', 'OPTIONS'], name: 'api.delete')]
    public function delete(Request $request, string $table, ?Entity $entity, ?array $entities): Response
    {
        if ($request->isMethod("GET") && $entity === null && $entities === null) {
            return new JsonResponse(["success" => false, "version" => PRODUCT_VERSION, "failureMessage" => $this->language->phrase("NoRecord", true) ]);
        }
        $tableVar = $this->getClassNameByAlias($table);
        $pageName = $tableVar . 'Delete';
        if (!$this->locator->has($pageName)) {
            throw new NotFoundHttpException("Page '$pageName' not found");
        }
        $page = $this->locator->get($pageName);
        $page->init();
        return $this->processPage($page, $entity ?? $entities);
    }

    /**
     * Register
     */
    #[Route('/register', methods: ['POST', 'OPTIONS'], name: 'api.register')]
    public function register(): Response
    {
        $pageName = 'Register';
        if (!$this->locator->has($pageName)) {
            throw new NotFoundHttpException("Page '$pageName' not found");
        }
        $page = $this->locator->get($pageName);
        return $this->processPage($page);
    }

    /**
     * File
     *
     * /api/file/{table}/{field}/{key}
     * /api/file/{table}/{path}
     * $param can be {field} or {path}
     * Note: Method name changed to avoid conflicts with Symfony\Bundle\FrameworkBundle\Controller\AbstractController::file()
     */
    #[Route('/file/{table}/{param}/{key?}', methods: ['GET', 'OPTIONS'], requirements: ['key' => Requirement::CATCH_ALL], name: 'api.file')]
    public function getFile(string $table, string $param, ?string $key): Response
    {
        $fileViewer = $this->locator->get(FileViewer::class);
        return $fileViewer();
    }

    /**
     * Export
     *
     * /api/export/{type}/{table}/{key}
     * /api/export/{id}
     * /api/export/search
     * $args['param'] can be {type} or {id} or 'search'
     */
    #[Route('/export/{param}/{table?}/{key?}', methods: ['GET', 'POST', 'OPTIONS'], requirements: ['key' => Requirement::CATCH_ALL], name: 'api.export')]
    public function export(string $param, ?string $table, ?string $key, ?array $entities): Response
    {
        $exportHandler = $this->locator->get(ExportHandler::class);
        return $exportHandler($entities);
    }

    /**
     * Upload
     */
    #[Route('/upload', methods: ['POST', 'OPTIONS'], name: 'api.upload')]
    public function upload(Request $request): Response
    {
        $httpUpload = new HttpUpload($request, $this->language);
        return new JsonResponse($httpUpload->getUploadedFiles());
    }

    /**
     * jupload
     */
    #[Route('/jupload', methods: ['GET', 'POST', 'OPTIONS'], name: 'api.jupload')]
    public function jupload(Request $request): Response
    {
        $uploadHandler = $this->locator->get(FileUploadHandler::class);
        return $uploadHandler($request);
    }

    /**
     * Lookup
     */
    #[Route('/lookup', methods: ['GET', 'POST', 'OPTIONS'], name: 'api.lookup')]
    public function lookup(Request $request): Response
    {
        if ($request->getContentTypeFormat() == 'json') { // Multiple requests
            $req = $request->request->all();
            if (is_array($req)) { // Multiple requests
                $out = [];
                foreach ($req as $ar) {
                    if (is_string($ar)) { // Request is QueryString
                        parse_str($ar, $ar);
                    }
                    $object = $ar[Config('API_LOOKUP_PAGE')];
                    $fieldName = $ar[Config('API_FIELD_NAME')];
                    $res = [Config('API_LOOKUP_PAGE') => $object, Config('API_FIELD_NAME') => $fieldName];
                    $page = $this->locator->get($object);
                    $lookupField = $page?->Fields[$fieldName] ?? null;
                    if ($lookupField) {
                        $lookup = $lookupField->Lookup;
                        if ($lookup) {
                            $tbl = $lookup->getTable();
                            if ($tbl) {
                                $res = array_merge($res, $page->lookup($ar));
                            }
                        }
                    }
                    if ($fieldName) {
                        $out[] = $res;
                    }
                }
                return new JsonResponse($out);
            }
        } else { // Single request
            $page = $this->get($request, Config('API_LOOKUP_PAGE'));
            $fieldName = $this->get($request, Config('API_FIELD_NAME'));
            $res = [Config('API_LOOKUP_PAGE') => $page, Config('API_FIELD_NAME') => $fieldName];
            $res = array_merge($res, $this->locator->get($page)?->lookup(array_merge($request->request->all(), $request->query->all())));
            return new JsonResponse($res);
        }
    }

    /**
     * Export chart
     */
    #[Route('/chart/{params}', methods: ['GET', 'OPTIONS'], requirements: ['params' => Requirement::CATCH_ALL], name: 'api.chart')]
    public function chart(Request $request, string $params): Response
    {
        $chartExporter = $this->locator->get(ChartExporter::class);
        return $chartExporter();
    }

    /**
     * Permissions
     */
    #[Route('/permissions/{userLevel}', methods: ['GET', 'POST', 'OPTIONS'], requirements: ['userLevel' => Requirement::DIGITS], name: 'api.permissions')]
    public function permissions(Request $request, int $userLevel): Response
    {
        // Set up security
        $this->security->setupUserLevel(); // Get all User Level info
        $tables = $this->parameters->get('user.level.tables');

        // Get permissions
        if ($request->isMethod('GET')) {
            // Check user level
            $userLevels = [-2]; // Default anonymous
            if ($this->security->isLoggedIn()) {
                if ($this->security->isAdmin()) { // Get permissions for user level (Admin only)
                    if (is_numeric($userLevel) && !SameString($userLevel, '-1') && $this->security->userLevelIDExists($userLevel)) {
                        $userLevels = $this->security->getAllUserLevelsFromHierarchy($userLevel);
                    }
                } else {
                    $userLevel = $this->security->CurrentUserLevelID;
                    $userLevels = $this->security->getAllUserLevelsFromHierarchy($userLevel);
                }
            }
            $privs = [];
            $cnt = count($tables);
            for ($i = 0; $i < $cnt; $i++) {
                $projectId = $tables[$i][4];
                $tableVar = $tables[$i][1];
                $tableName = $tables[$i][0];
                $allowed = $tables[$i][3];
                if ($allowed) {
                    $priv = 0;
                    foreach ($userLevels as $level) {
                        $priv |= $this->security->getUserLevelPrivEx($projectId . $tableName, $level);
                    }
                    $privs[$tableVar] = $priv;
                }
            }
            $res = ['userlevel' => $userLevel, 'permissions' => $privs];
            return new JsonResponse($res);

        // Update permissions
        } elseif ($request->isMethod('POST') && $this->security->isAdmin()) { // Admin only
            $json = $request->getContentTypeFormat() == 'json' ? $request->request->all() : [];

            // Validate user level
            if (
                !is_numeric($userLevel)
                || SameString($userLevel, '-1')
                || !array_find($this->security->UserLevels, fn ($level) => SameString($level[0], $userLevel))
            ) {
                $res = ['userlevel' => $userLevel, 'permissions' => $json, 'success' => false];
                return new JsonResponse($res);
            }

            // Validate table names / permissions
            $newPrivs = [];
            $outPrivs = [];
            foreach ($json as $tableName => $permission) {
                $table = array_find($tables, fn ($privs) => $privs[0] === $tableName || $privs[1] === $tableName);
                if (!$table || !is_numeric($permission) || intval($permission) < 0 || intval($permission) > Allow::ADMIN->value) {
                    $res = ['userlevel' => $userLevel, 'permissions' => $json, 'success' => false];
                    return new JsonResponse($res);
                }
                $permission = intval($permission) & Allow::ADMIN->value;
                $newPrivs[$table[4] . $table[1]] = $permission;
                $outPrivs[$table[1]] = $permission;
            }

            // Update permissions for user level
            if (method_exists($this->security, 'updatePermissions')) {
                $this->security->updatePermissions($userLevel, $newPrivs);
                $res = ['userlevel' => $userLevel, 'permissions' => $outPrivs, 'success' => true];
                return new JsonResponse($res);
            } else {
                $res = ['userlevel' => $userLevel, 'permissions' => $json, 'success' => false];
                return new JsonResponse($res);
            }
        }
        return new Response();
    }

    /**
     * Two factor authentication
     *
     * @param mixed $request
     * @param mixed $action secret/show/verify/reset/codes/newcodes/otp/enable/disable
     * @param mixed $authType
     * @param mixed $parm code/account
     * @return Response
     */
    #[Route('/twofa/{action}/{authType?}/{parm?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'api.twofa')]
    public function twofa(Request $request, string $action, ?string $authType, ?string $parm): Response
    {
        $className = TwoFactorAuthenticationClass($authType);
        $auth = $this->locator->get($className);
        try {
            $user = $this->profile->getUserName();
            if (!$user) {
                throw new Exception($this->language->phrase('MissingUsername', true));
            }
            // twofa/otp/user/authtype/account
            if ($action == Config('API_2FA_SEND_OTP')) {
                if (!$auth instanceof SendOneTimePasswordInterface) {
                    throw new Exception('The authentication type "{$authType}" does not support sending one time password');
                }
                if (!$parm) {
                    throw new Exception('Missing account for the authentication type "{$authType}"');
                }
            } elseif (in_array($action, [Config('API_2FA_ENABLE'), Config('API_2FA_DISABLE')])) {
                if (IsLoggedIn()) {
                    // twofa/enable/user
                    if ($action == Config('API_2FA_ENABLE')) {
                        $this->profile->set2FAEnabled(true)->saveToStorage();
                        return new JsonResponse(['success' => true, 'enabled' => true]);
                    // twofa/disable/user
                    } elseif ($action == Config('API_2FA_DISABLE')) {
                        $this->profile->set2FAEnabled(false)->saveToStorage();
                        return new JsonResponse(['success' => true, 'disabled' => true]);
                    }
                }
                return new JsonResponse(['success' => false]);
            }
            return match ($action) {
                // twofa/config (Get configuration)
                Config('API_2FA_CONFIG') => new JsonResponse(['success' => true, 'config' => $this->profile->get2FAConfig()]),
                // twofa/show/authtype (Show QR Code URL or email/phone)
                Config('API_2FA_SHOW') => new JsonResponse($auth->show($user)),
                // twofa/verify/authtype/code
                Config('API_2FA_VERIFY') => new JsonResponse([...$auth->verify($user, $parm), 'config' => $this->profile->get2FAConfig()]),
                // twofa/reset/authtype
                Config('API_2FA_RESET') => new JsonResponse([...($authType ? $auth->reset($user) : $auth->resetAll($user)), 'config' => $this->profile->get2FAConfig()]),
                // twofa/codes
                Config('API_2FA_BACKUP_CODES') => new JsonResponse($auth->getBackupCodes($user)),
                // twofa/newcodes
                Config('API_2FA_NEW_BACKUP_CODES') => new JsonResponse([...$auth->getNewBackupCodes($user), 'config' => $this->profile->get2FAConfig()]),
                // twofa/otp/authtype/account
                Config('API_2FA_SEND_OTP') => ($result = $auth->sendOneTimePassword($user, $parm)) === true
                    ? new JsonResponse(['success' => true])
                    : throw new Exception($result)
            };
        } catch (Exception $e) {
            return new JsonResponse(['success' => false, 'error' => ['description' => $e->getMessage()]]);
        }
    }

    /**
     * Chat
     */
    #[Route('/chat/{value}', methods: 'GET', requirements: ['value' => Requirement::DIGITS], name: 'api.chat')]
    public function chat(Request $request, int $value): Response
    {
        if (IsLoggedIn() && !IsSysAdmin()) {
            $this->profile->setChatEnabled(ConvertToBool($value))->saveToStorage();
            return new JsonResponse(['success' => true]);
        }
        return new JsonResponse(['success' => false]);
    }
}
