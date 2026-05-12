<?php

namespace PHPMaker2026\Project1;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\RememberMe\RememberMeHandlerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Exception;
use Throwable;

/**
 * Advanced Security class
 */
class AdvancedSecurity
{
    // User level contants
    public const ANONYMOUS_USER_LEVEL_ID = -2;
    public const ADMIN_USER_LEVEL_ID = -1;
    public const DEFAULT_USER_LEVEL_ID = 0;

    // User ID constant
    public const ADMIN_USER_ID = -1;

    // For all users
    public array $UserLevels = []; // All User Levels
    public array $UserLevelPrivs = []; // All User Level permissions

    // Current user
    public array $UserLevelIDs = []; // User Level ID array
    public array $UserIDs = []; // User ID array
    public array $ParentUserIDs = []; // Parent User ID array
    public int $CurrentUserLevel = Allow::ADMIN->value; // Permissions
    public int|string $CurrentUserLevelID = self::ANONYMOUS_USER_LEVEL_ID; // User Level (Anonymous by default)
    public mixed $CurrentUserID = null;
    public mixed $CurrentUserPrimaryKey = null;
    protected bool $isLoggedIn = false;
    protected bool $isSysAdmin = false;
    protected string $userName = "";
    protected SessionInterface $session;
    protected ?Security $security = null;

    // Constructor
    public function __construct(
        protected Language $language,
        protected RequestStack $requestStack,
        protected UserProfile $profile,
        protected ParameterBagInterface $parameters,
        protected CacheInterface $cache,
        protected ManagerRegistry $registry,
    ) {
    }

    // Initialize
    public function initialize(): void
    {
        $this->session = $this->requestStack->getSession();

        // Init User Level
        $this->CurrentUserLevelID = $this->isLoggedIn()
            ? $this->sessionUserLevelID() // Load from session
            : self::ANONYMOUS_USER_LEVEL_ID; // Anonymous user
        $this->setUserLevelID($this->CurrentUserLevelID);
        $this->session->set(SESSION_USER_LEVEL_LIST, $this->userLevelList());

        // Load User ID, Parent User ID and primary key
        $this->setCurrentUserID($this->sessionUserID());
        $this->setParentUserID($this->sessionParentUserID());
        $this->setCurrentUserPrimaryKey($this->sessionUserPrimaryKey());

        // Load User Level
        $this->loadUserLevel();

        // Set hierarchy
        $this->setHierarchy($this->CurrentUserLevelID);
    }

    /**
     * User ID
     */

    // Get session User ID
    protected function sessionUserID(): mixed
    {
        return $this->session->has(SESSION_USER_ID) ? strval($this->session->get(SESSION_USER_ID)) : $this->CurrentUserID;
    }

    // Set session User ID
    protected function setSessionUserID(mixed $v): void
    {
        $this->CurrentUserID = trim(strval($v));
        $this->session->set(SESSION_USER_ID, $this->CurrentUserID);
    }

    // Current User ID
    public function currentUserID(): mixed
    {
        return $this->CurrentUserID;
    }

    // Set current User ID
    public function setCurrentUserID(mixed $v): void
    {
        $this->CurrentUserID = trim(strval($v));
    }

    /**
     * Parent User ID
     */

    // Get session Parent User ID
    protected function sessionParentUserID(): mixed
    {
        return $this->session->has(SESSION_PARENT_USER_ID) ? strval($this->session->get(SESSION_PARENT_USER_ID)) : $this->getParentUserID();
    }

    // Set session Parent User ID
    protected function setSessionParentUserID(mixed $v): void
    {
        $this->setParentUserID($v);
        $this->session->set(SESSION_PARENT_USER_ID, $this->getParentUserID());
    }

    // Set Parent User ID to array
    public function setParentUserID(mixed $v): void
    {
        $ids = is_array($v) ? $v : explode(Config("MULTIPLE_OPTION_SEPARATOR"), strval($v));
        $this->ParentUserIDs = [];
    }

    // Get Parent User ID
    public function getParentUserID(): mixed
    {
        return implode(Config("MULTIPLE_OPTION_SEPARATOR"), $this->ParentUserIDs);
    }

    // Check if Parent User ID in array
    public function hasParentUserID(mixed $v): bool
    {
        $ids = is_array($v) ? $v : explode(Config("MULTIPLE_OPTION_SEPARATOR"), strval($v));
        return array_any($ids, fn($id) => in_array($id, $this->ParentUserID));
    }

    // Current Parent User ID
    public function currentParentUserID(): mixed
    {
        return $this->getParentUserID();
    }

    /**
     * User Level ID
     */

    // Get session User Level ID
    protected function sessionUserLevelID(): int|string
    {
        return $this->session->get(SESSION_USER_LEVEL_ID) ?? $this->CurrentUserLevelID;
    }

    // Set session User Level ID
    protected function setSessionUserLevelID(int|string $v): void
    {
        $this->setCurrentUserLevelID($v);
        $this->session->set(SESSION_USER_LEVEL_ID, $this->CurrentUserLevelID);
    }

    // Current User Level ID
    public function currentUserLevelID(): int|string
    {
        return $this->CurrentUserLevelID;
    }

    // Set current User Level ID
    public function setCurrentUserLevelID(int|string $v): void
    {
        $this->CurrentUserLevelID = $v;
        $this->setUserLevelID($v);
    }

    // Set Hierarchy
    protected function setHierarchy(int|string $v): void
    {
        $this->UserLevelIDs = array_unique(array_merge($this->UserLevelIDs, $this->getAllUserLevelsFromHierarchy($v)));
    }

    /**
     * User Level (Permissions)
     */

    // Get session User Level
    protected function sessionUserLevel(): int
    {
        return $this->session->has(SESSION_USER_LEVEL) ? (int)$this->session->get(SESSION_USER_LEVEL) : $this->CurrentUserLevel;
    }

    // Set session User Level
    protected function setSessionUserLevel(int $v): void
    {
        $this->CurrentUserLevel = $v;
        $this->session->set(SESSION_USER_LEVEL, $this->CurrentUserLevel);
    }

    // Current User Level value
    public function currentUserLevel(): int
    {
        return $this->CurrentUserLevel;
    }

    /**
     * User name
     */

    // Get current user name
    public function getCurrentUserName(): string
    {
        return $this->session->has(SESSION_USER_NAME) ? strval($this->session->get(SESSION_USER_NAME)) : $this->userName;
    }

    // Set current user name
    public function setCurrentUserName(string $v): void
    {
        $this->userName = $v;
        $this->session->set(SESSION_USER_NAME, $this->userName);
    }

    // Get current user name (alias)
    public function currentUserName(): string
    {
        return $this->getCurrentUserName();
    }

    /**
     * User primary key
     */

    // Get session user primary key
    protected function sessionUserPrimaryKey(): mixed
    {
        return $this->session->has(SESSION_USER_PRIMARY_KEY) ? strval($this->session->get(SESSION_USER_PRIMARY_KEY)) : $this->CurrentUserPrimaryKey;
    }

    // Set session user primary key
    protected function setSessionUserPrimaryKey(mixed $v): void
    {
        $this->setCurrentUserPrimaryKey($v);
        $this->session->set(SESSION_USER_PRIMARY_KEY, $this->CurrentUserPrimaryKey);
    }

    // Get current user primary key
    public function currentUserPrimaryKey(): mixed
    {
        return $this->CurrentUserPrimaryKey;
    }

    // Set current user primary key
    public function setCurrentUserPrimaryKey(mixed $v): void
    {
        $this->CurrentUserPrimaryKey = $v;
    }

    /**
     * Other methods
     */

    // Set User Level ID to array
    public function setUserLevelID(int|string|array $v): void
    {
        $ids = is_array($v) ? $v : explode(Config("MULTIPLE_OPTION_SEPARATOR"), strval($v));
        $this->UserLevelIDs = [];
        foreach ($ids as $id) {
            if ((int)$id >= self::ANONYMOUS_USER_LEVEL_ID) {
                $this->UserLevelIDs[] = (int)$id;
            }
        }
    }

    // Check if User Level ID in array
    public function hasUserLevelID(int|string|array|null $v): bool
    {
        $ids = is_array($v) ? $v : explode(Config("MULTIPLE_OPTION_SEPARATOR"), strval($v));
        return array_any($ids, fn($id) => in_array((int)$id, $this->UserLevelIDs));
    }

    // Get JWT payload
    public function getJwtPayload($permission = 0): array
    {
        return [
            "username" => $this->currentUserName() ?: "Anonymous",
            "userid" => $this->currentUserID(),
            "parentuserid" => $this->currentParentUserID(),
            "userlevel" => $this->currentUserLevelID(),
            "userprimarykey" => $this->currentUserPrimaryKey(),
            "userPermission" => $permission
        ];
    }

    // Can add
    public function canAdd(): bool
    {
        return ($this->CurrentUserLevel & Allow::ADD->value) == Allow::ADD->value;
    }

    // Set can add
    public function setCanAdd(bool $b): void
    {
        if ($b) {
            $this->CurrentUserLevel |= Allow::ADD->value;
        } else {
            $this->CurrentUserLevel &= ~(Allow::ADD->value);
        }
    }

    // Can delete
    public function canDelete(): bool
    {
        return ($this->CurrentUserLevel & Allow::DELETE->value) == Allow::DELETE->value;
    }

    // Set can delete
    public function setCanDelete(bool $b): void
    {
        if ($b) {
            $this->CurrentUserLevel |= Allow::DELETE->value;
        } else {
            $this->CurrentUserLevel &= ~(Allow::DELETE->value);
        }
    }

    // Can edit
    public function canEdit(): bool
    {
        return ($this->CurrentUserLevel & Allow::EDIT->value) == Allow::EDIT->value;
    }

    // Set can edit
    public function setCanEdit(bool $b): void
    {
        if ($b) {
            $this->CurrentUserLevel |= Allow::EDIT->value;
        } else {
            $this->CurrentUserLevel &= ~(Allow::EDIT->value);
        }
    }

    // Can access (View all records)
    public function canAccess(): bool
    {
        return true;
    }

    // Set can access
    public function setCanAccess(bool $b): void
    {
        if ($b) {
            $this->CurrentUserLevel |= Allow::ACCESS->value;
        } else {
            $this->CurrentUserLevel &= ~(Allow::ACCESS->value);
        }
    }

    // Can view
    public function canView(): bool
    {
        return ($this->CurrentUserLevel & Allow::VIEW->value) == Allow::VIEW->value;
    }

    // Set can view
    public function setCanView(bool $b): void
    {
        if ($b) {
            $this->CurrentUserLevel |= Allow::VIEW->value;
        } else {
            $this->CurrentUserLevel &= ~(Allow::VIEW->value);
        }
    }

    // Can list
    public function canList(): bool
    {
        return ($this->CurrentUserLevel & Allow::LIST->value) == Allow::LIST->value;
    }

    // Set can list
    public function setCanList(bool $b): void
    {
        if ($b) {
            $this->CurrentUserLevel |= Allow::LIST->value;
        } else {
            $this->CurrentUserLevel &= ~(Allow::LIST->value);
        }
    }

    // Can search
    public function canSearch(): bool
    {
        return ($this->CurrentUserLevel & Allow::SEARCH->value) == Allow::SEARCH->value;
    }

    // Set can search
    public function setCanSearch(bool $b): void
    {
        if ($b) {
            $this->CurrentUserLevel |= Allow::SEARCH->value;
        } else {
            $this->CurrentUserLevel &= ~(Allow::SEARCH->value);
        }
    }

    // Can admin
    public function canAdmin(): bool
    {
        return ($this->CurrentUserLevel & Allow::ADMIN->value) == Allow::ADMIN->value;
    }

    // Set can admin
    public function setCanAdmin(bool $b): void
    {
        if ($b) {
            $this->CurrentUserLevel |= Allow::ADMIN->value;
        } else {
            // $this->CurrentUserLevel &= ~(Allow::ADMIN->value);
            throw new \Nette\NotSupportedException('setCanAdmin(false) is not supported.');
        }
    }

    // Can grant
    public function canGrant(): bool
    {
        return ($this->CurrentUserLevel & Allow::GRANT->value) == Allow::GRANT->value;
    }

    // Set can grant
    public function setCanGrant(bool $b): void
    {
        if ($b) {
            $this->CurrentUserLevel |= Allow::GRANT->value;
        } else {
            $this->CurrentUserLevel &= ~(Allow::GRANT->value);
        }
    }

    // Can import
    public function canImport(): bool
    {
        return ($this->CurrentUserLevel & Allow::IMPORT->value) == Allow::IMPORT->value;
    }

    // Set can import
    public function setCanImport(bool $b): void
    {
        if ($b) {
            $this->CurrentUserLevel |= Allow::IMPORT->value;
        } else {
            $this->CurrentUserLevel &= ~(Allow::IMPORT->value);
        }
    }

    // Can lookup
    public function canLookup(): bool
    {
        return ($this->CurrentUserLevel & Allow::LOOKUP->value) == Allow::LOOKUP->value;
    }

    // Set can lookup
    public function setCanLookup(bool $b): void
    {
        if ($b) {
            $this->CurrentUserLevel |= Allow::LOOKUP->value;
        } else {
            $this->CurrentUserLevel &= ~(Allow::LOOKUP->value);
        }
    }

    // Can push
    public function canPush(): bool
    {
        return ($this->CurrentUserLevel & Allow::PUSH->value) == Allow::PUSH->value;
    }

    // Set can push
    public function setCanPush(bool $b): void
    {
        if ($b) {
            $this->CurrentUserLevel |= Allow::PUSH->value;
        } else {
            $this->CurrentUserLevel &= ~(Allow::PUSH->value);
        }
    }

    // Can export
    public function canExport(): bool
    {
        return ($this->CurrentUserLevel & Allow::EXPORT->value) == Allow::EXPORT->value;
    }

    // Set can push
    public function setCanExport(bool $b): void
    {
        if ($b) {
            $this->CurrentUserLevel |= Allow::EXPORT->value;
        } else {
            $this->CurrentUserLevel &= ~(Allow::EXPORT->value);
        }
    }

    // Can switch user
    public function canSwitchUser(): bool
    {
        return $this->isAdmin() || count($this->UserIDs) > 1;
    }

    // Last URL
    public function lastUrl(): ?string
    {
        return $this->getTargetPath($this->session, "main");
    }

    // Save last URL
    public function saveLastUrl(): void
    {
        $url = $this->requestStack->getCurrentRequest()->getUri();
        if ($this->lastUrl() == $url) {
            $url = "";
        }
        if (!IsModal()) {
            $this->saveTargetPath($this->session, "main", $url);
        }
    }

    // Remove last URL
    public function removeLastUrl(): void
    {
        $this->removeTargetPath($this->session, "main");
    }

    // Login current user
    public function login(): void
    {
        if ($this->isLoggedIn()) {
            return;
        }
        $user = $this->getUser();
        if (!$user) {
            return;
        }
        if (IsEntityUser($user) || IsSysAdminUser($user)) {
            $this->loginUser($user);
        } else { // Other user, e.g. LDAP, OAuth, AccessToken
            $this->profile->setUser($user); // Load user ID/level from cache, e.g. set by User_CustomValidate
            $this->loginUser($this->profile);
        }
    }

    // Login user
    public function loginUser(UserInterface $user): void
    {
        $userName = $user->getUserIdentifier();
        $this->setCurrentUserName($userName);
        if (IsSysAdminUser($user)) {
            $user = new SysAdminUser(); // Super user
            $this->isSysAdmin = true;
            $this->session->set(SESSION_SYS_ADMIN, 1); // System administrator
        } else {
            $this->isSysAdmin = false;
            $this->session->set(SESSION_SYS_ADMIN, 0); // Non system administrator
        }
        if ($user instanceof AdvancedUserInterface) {
            if (($userLevel = $user->userLevel()) !== null) {
                $userLevels = explode(Config("MULTIPLE_OPTION_SEPARATOR"), strval($userLevel));
                if (count(array_filter($userLevels, fn($id) => (int)$id > AdvancedSecurity::ANONYMOUS_USER_LEVEL_ID)) > 0) {
                    $this->isLoggedIn = true;
                    $this->session->set(SESSION_STATUS, "login");
                }
            }
            if (($userId = $user->userId()) !== null) {
                $this->setSessionUserID($userId);
            }
            if (($parentUserId = $user->parentUserId()) !== null) {
                $this->setSessionParentUserID($parentUserId);
            }
            if (count(array_filter($userLevels, fn($id) => (int)$id >= AdvancedSecurity::ANONYMOUS_USER_LEVEL_ID)) > 0) {
                $this->setSessionUserLevelID($userLevel);
                $this->setupUserLevel();
            }
            if ($user instanceof Entity && ($id = $user->id()) !== null) {
                $this->setSessionUserPrimaryKey($id);
            }
        }

        // Call User Validated event
        $this->userValidated($user);
    }

    // Logout user
    public function logout(): void
    {
        $user = $this->getUser();
        if ($user && !IsEntityUser($user) && !IsSysAdminUser($user)) {
            $this->profile->setUser($user)->clearCache(); // Clear cache
        }
        $this->isLoggedIn = false;
        $this->session->remove(SESSION_STATUS);
        $this->setCurrentUserName("");
        $this->setSessionUserID(null);
        $this->setSessionParentUserID([]);
        $this->setSessionUserLevelID(self::ANONYMOUS_USER_LEVEL_ID);
        $this->setSessionUserPrimaryKey(null);
        $this->setupUserLevel();
        $this->profile->clear();
    }

    // Get User Level settings from storage
    public function setupUserLevel(): void
    {
    }

    // Get all User Level settings from database
    public function loadFromStorage(): bool
    {
        $userLevels = $this->parameters->get("user.levels");
        $userLevelPrivs = $this->parameters->get("user.level.privs");
        $userLevelTables = $this->parameters->get("user.level.tables");

        // Load from user level settings first
        $this->UserLevels = $userLevels;
        $this->UserLevelPrivs = $userLevelPrivs;
        return true;
    }

    // Update user level permissions
    public function updatePermissions($userLevel, $privs): void
    {
        $c = Conn(Config("USER_LEVEL_PRIV_DBID"));
        foreach ($privs as $table => $priv) {
            if (is_numeric($priv)) {
                $sql = "SELECT * FROM " . Config("USER_LEVEL_PRIV_TABLE") . " WHERE " .
                    Config("USER_LEVEL_PRIV_TABLE_NAME_FIELD") . " = '" . AdjustSql($table) . "' AND " .
                    Config("USER_LEVEL_PRIV_USER_LEVEL_ID_FIELD") . " = " . $userLevel;
                if ($c->fetchAssociative($sql)) {
                    $sql = "UPDATE " . Config("USER_LEVEL_PRIV_TABLE") . " SET " . Config("USER_LEVEL_PRIV_PRIV_FIELD") . " = " . $priv . " WHERE " .
                        Config("USER_LEVEL_PRIV_TABLE_NAME_FIELD") . " = '" . AdjustSql($table) . "' AND " .
                        Config("USER_LEVEL_PRIV_USER_LEVEL_ID_FIELD") . " = " . $userLevel;
                    $c->executeStatement($sql);
                } else {
                    $sql = "INSERT INTO " . Config("USER_LEVEL_PRIV_TABLE") . " (" . Config("USER_LEVEL_PRIV_TABLE_NAME_FIELD") . ", " . Config("USER_LEVEL_PRIV_USER_LEVEL_ID_FIELD") . ", " . Config("USER_LEVEL_PRIV_PRIV_FIELD") . ") VALUES ('" . AdjustSql($table) . "', " . $userLevel . ", " . $priv . ")";
                    $c->executeStatement($sql);
                }
            }
        }
    }

    // Set user permissions
    public function setUserPermissions(Allow|string|int $userPermission = 0): void
    {
        $permission = GetPrivilege($userPermission);
        if ($permission > 0) {
            foreach ($this->UserLevelPrivs as &$row) {
                $priv = &$row[2];
                if (is_numeric($priv)) {
                    $priv &= $permission;
                }
            }
        }
    }

    // Add user permission
    protected function addUserPermissionEx(string $userLevelName, string $tableName, Allow|string|int $userPermission): void
    {
        // Get User Level ID from user name
        $userLevelID = "";
        $permission = GetPrivilege($userPermission);
        foreach ($this->UserLevels as $row) {
            [$levelid, $name, $hierarchy] = $row;
            if (SameText($userLevelName, $name)) {
                $userLevelID = $levelid;
                break;
            }
        }
        if ($userLevelID != "") {
            $cnt = count($this->UserLevelPrivs);
            for ($i = 0; $i < $cnt; $i++) {
                list($table, $levelid, $priv) = $this->UserLevelPrivs[$i];
                if (SameText($table, PROJECT_ID . $tableName) && SameString($levelid, $userLevelID)) {
                    $this->UserLevelPrivs[$i][2] = $priv | $permission; // Add permission
                    return;
                }
            }
            // Add new entry
            $this->UserLevelPrivs[] = [PROJECT_ID . $tableName, $userLevelID, $permission];
        }
    }

    // Add user permission
    public function addUserPermission(string $userLevelName, string $tableName, Allow|string|int $userPermission): void
    {
        $arUserLevelName = is_array($userLevelName) ? $userLevelName : [$userLevelName];
        $arTableName = is_array($tableName) ? $tableName : [$tableName];
        foreach ($arUserLevelName as $userLevelName) {
            foreach ($arTableName as $tableName) {
                $this->addUserPermissionEx($userLevelName, $tableName, $userPermission);
            }
        }
    }

    // Delete user permission
    protected function deleteUserPermissionEx(string $userLevelName, string $tableName, Allow|string|int $userPermission): void
    {
        // Get User Level ID from user name
        $userLevelID = "";
        $permission = GetPrivilege($userPermission);
        foreach ($this->UserLevels as $row) {
            [$levelid, $name, $hierarchy] = $row;
            if (SameText($userLevelName, $name)) {
                $userLevelID = $levelid;
                break;
            }
        }
        if ($userLevelID != "") {
            $cnt = count($this->UserLevelPrivs);
            for ($i = 0; $i < $cnt; $i++) {
                list($table, $levelid, $priv) = $this->UserLevelPrivs[$i];
                if (SameText($table, PROJECT_ID . $tableName) && SameString($levelid, $userLevelID)) {
                    $this->UserLevelPrivs[$i][2] = $priv & ~$permission; // Remove permission
                    break;
                }
            }
        }
    }

    // Delete user permission
    public function deleteUserPermission(string $userLevelName, string $tableName, Allow|string|int $userPermission): void
    {
        $arUserLevelName = is_array($userLevelName) ? $userLevelName : [$userLevelName];
        $arTableName = is_array($tableName) ? $tableName : [$tableName];
        foreach ($arUserLevelName as $userLevelName) {
            foreach ($arTableName as $tableName) {
                $this->deleteUserPermissionEx($userLevelName, $tableName, $userPermission);
            }
        }
    }

    /**
     * Get table name
     *
     * @param string $tblVar Table name or variable name (could be in snake case from route name)
     * @return string Table name
     */
    function getTableName(string $tblVar): string
    {
        $table = array_find(
            $this->parameters->get("user.level.tables"),
            fn ($tbl) => SameText($tbl[0], $tblVar) || SameText($tbl[1], $tblVar) || SnakeCase($tbl[1]) === $tblVar
        );
        return $table[0] ?? $tblVar;
    }

    /**
     * Load table permissions
     *
     * @param string $tblVar Table name or variable name (could be in snake case from route name)
     * @return void
     */
    public function loadTablePermissions(string $tblVar): void
    {
        $this->setHierarchy($this->CurrentUserLevelID); // Make sure Hierarchy permissions are loaded
        $tblName = $this->getTableName($tblVar);
        if ($this->isLoggedIn() && method_exists($this, "tablePermissionLoading")) {
            $this->tablePermissionLoading();
        }
        $this->loadCurrentUserLevel(PROJECT_ID . $tblName);
        if ($this->isLoggedIn() && method_exists($this, "tablePermissionLoaded")) {
            $this->tablePermissionLoaded();
        }
        if ($this->isLoggedIn()) {
            if (method_exists($this, "userIDLoading")) {
                $this->userIDLoading();
            }
            if (method_exists($this, "loadUserID")) {
                $this->loadUserID();
            }
            if (method_exists($this, "userIDLoaded")) {
                $this->userIDLoaded();
            }
        }
    }

    // Load current User Level
    public function loadCurrentUserLevel(string $table): void
    {
        $this->setSessionUserLevel($this->currentUserLevelPriv($table));
    }

    // Get current user privilege
    protected function currentUserLevelPriv(string $tableName): int
    {
        if ($this->isLoggedIn()) {
            return $this->isAdmin()
                ? Allow::ADMIN->value
                : Allow::ADMIN->value - Allow::ACCESS->value; // Default User ID Security for non Admin user
        } else { // Anonymous
            return Allow::ADMIN->value;
        }
    }

    // Get User Level ID by User Level name
    public function getUserLevelID(string $userLevelName): int
    {
        if (SameString($userLevelName, "Anonymous")) {
            return self::ANONYMOUS_USER_LEVEL_ID;
        } elseif (SameString($userLevelName, $this->language->phrase("UserAnonymous"))) {
            return self::ANONYMOUS_USER_LEVEL_ID;
        } elseif (SameString($userLevelName, "Administrator")) {
            return self::ADMIN_USER_LEVEL_ID;
        } elseif (SameString($userLevelName, $this->language->phrase("UserAdministrator"))) {
            return self::ADMIN_USER_LEVEL_ID;
        } elseif (SameString($userLevelName, "Default")) {
            return self::DEFAULT_USER_LEVEL_ID;
        } elseif (SameString($userLevelName, $this->language->phrase("UserDefault"))) {
            return self::DEFAULT_USER_LEVEL_ID;
        } elseif ($userLevelName != "") {
            foreach ($this->UserLevels as $row) {
                [$levelid, $name, $hierarchy] = $row;
                if (SameString($name, $userLevelName)) {
                    return $levelid;
                }
            }
        }
        return self::ANONYMOUS_USER_LEVEL_ID; // Anonymous
    }

    // Add User Level by name
    public function addUserLevel(string $userLevelName): void
    {
        $this->addUserLevelID($this->getUserLevelID($userLevelName));
    }

    // Add User Level by ID
    public function addUserLevelID(int $userLevelID): void
    {
        if ($userLevelID < self::ADMIN_USER_LEVEL_ID) {
            return;
        }
        if (!in_array($userLevelID, $this->UserLevelIDs)) {
            $this->UserLevelIDs[] = $userLevelID;
            $this->session->set(SESSION_USER_LEVEL_LIST, $this->userLevelList()); // Update session variable
        }
    }

    // Delete User Level by name
    public function deleteUserLevel(string $userLevelName): void
    {
        $this->deleteUserLevelID($this->getUserLevelID($userLevelName));
    }

    // Delete User Level by ID
    public function deleteUserLevelID(int $userLevelID): void
    {
        if ($userLevelID < self::ADMIN_USER_LEVEL_ID) {
            return;
        }
        $cnt = count($this->UserLevelIDs);
        for ($i = 0; $i < $cnt; $i++) {
            if ($this->UserLevelIDs[$i] == $userLevelID) {
                unset($this->UserLevelIDs[$i]);
                $this->session->set(SESSION_USER_LEVEL_LIST, $this->userLevelList()); // Update session variable
                break;
            }
        }
    }

    // User Level list
    public function userLevelList(): string
    {
        return implode(", ", $this->UserLevelIDs);
    }

    // User level ID exists
    public function userLevelIDExists(int $id): bool
    {
        return array_any($this->UserLevels, fn($row) => SameString($row[0], $id));
    }

    // User Level name list
    public function userLevelNameList(): string
    {
        $list = [];
        foreach ($this->UserLevelIDs as $userLevelID) {
            $list[] = QuotedValue($this->getUserLevelName($userLevelID), DataType::STRING, Config("USER_LEVEL_DBID"));
        }
        return implode(", ", $list);
    }

    // Get user privilege based on table name and User Level
    public function getUserLevelPrivEx(string $tableName, int|string $userLevelID): int
    {
        $ids = explode(Config("MULTIPLE_OPTION_SEPARATOR"), strval($userLevelID));
        $userPriv = 0;
        foreach ($ids as $id) {
            if ($id == self::ADMIN_USER_LEVEL_ID) { // System admin
                return Allow::ADMIN->value;
            } elseif ((int)$id >= self::DEFAULT_USER_LEVEL_ID || $id == self::ANONYMOUS_USER_LEVEL_ID) {
                foreach ($this->UserLevelPrivs as $row) {
                    list($table, $levelid, $priv) = $row;
                    if (SameText($table, $tableName) && SameText($levelid, $id)) {
                        if (is_numeric($priv)) {
                            $userPriv |= (int)$priv;
                        }
                    }
                }
            }
        }
        return $userPriv;
    }

    // Get current User Level name
    public function currentUserLevelName(): string
    {
        return $this->getUserLevelName($this->currentUserLevelID());
    }

    // Get User Level name based on User Level
    public function getUserLevelName(int|string|null $userLevelID, bool $lang = true): string
    {
        if ($userLevelID == self::ANONYMOUS_USER_LEVEL_ID) {
            return $lang ? $this->language->phrase("UserAnonymous") : "Anonymous";
        } elseif ($userLevelID == self::ADMIN_USER_LEVEL_ID) {
            return $lang ? $this->language->phrase("UserAdministrator") : "Administrator";
        } elseif ($userLevelID == self::DEFAULT_USER_LEVEL_ID) {
            return $lang ? $this->language->phrase("UserDefault") : "Default";
        } elseif ($userLevelID > self::DEFAULT_USER_LEVEL_ID) {
            foreach ($this->UserLevels as $row) {
                [$levelid, $name, $hierarchy] = $row;
                if (SameString($levelid, $userLevelID)) {
                    $userLevelName = "";
                    if ($lang) {
                        $userLevelName = $this->language->phrase($name);
                    }
                    return ($userLevelName != "") ? $userLevelName : $name;
                }
            }
        }
        return "";
    }

    // Get current user level hierarchy (sub levels)
    public function currentUserLevelHierarchy(): array
    {
        return $this->getUserLevelHierarchy($this->currentUserLevelID());
    }

    // Get user level hierarchy (sub levels)
    public function getUserLevelHierarchy(int|string $userLevelId): array
    {
        $userLevels = [];
        $userLevelIds = explode(Config("MULTIPLE_OPTION_SEPARATOR"), strval($userLevelId));
        foreach ($this->UserLevels as $row) {
            [$levelid, $name, $hierarchy] = $row;
            if (in_array(strval($levelid), $userLevelIds)) {
                $userLevels = array_merge($userLevels, explode(Config("MULTIPLE_OPTION_SEPARATOR"), strval($hierarchy)));
            }
        }
        return array_filter(array_unique($userLevels), fn($level) => !IsEmpty($level));
    }

    // Get all user levels from hierarchy
    public function getAllUserLevelsFromHierarchy(int|string|null $userLevelId = null): array
    {
        $userLevelId ??= $this->CurrentUserLevelID;
        if (
            is_int($userLevelId) && $userLevelId <= AdvancedSecurity::ADMIN_USER_LEVEL_ID
            || IsEmpty($userLevelId)
        ) {
            return [];
        }
        $userLevelIds = [$userLevelId];
        $userLevels = $this->getUserLevelHierarchy($userLevelId);
        foreach ($userLevels as $userLevel) {
            $userLevelIds[] = (int)$userLevel; // Add this level
            $userLevelSubIds = $this->getAllUserLevelsFromHierarchy($userLevel); // Add sub levels
            foreach ($userLevelSubIds as $userLevelSubId) {
                if (!in_array((int)$userLevelSubId, $userLevelIds)) {
                    $userLevelIds[] = (int)$userLevelSubId;
                }
            }
        }
        return $userLevelIds;
    }

    // Get roles
    public function getRoles(int|string|null $userLevelId = null): array
    {
        $userLevelId ??= $this->CurrentUserLevelID;
        // Get roles for this user level
        return $this->getRoleNamesFromUserLevels($userLevelId);
    }

    // Get reachable roles
    public function getReachableRoles(int|string|null $userLevelId = null): array
    {
        $userLevelId ??= $this->CurrentUserLevelID;
        $roles = [];
        // Set up roles from hierarchy
        $userLevelSubIds = $this->getAllUserLevelsFromHierarchy($userLevelId);
        foreach ($userLevelSubIds as $id) {
            $roles = array_merge($roles, $this->getRoleNamesFromUserLevels($id));
        }
        return array_filter(array_unique($roles));
    }

    // Get all roles (current and reachable)
    public function getAllRoles(int|string|null $userLevelId = null): array
    {
        $userLevelId ??= $this->CurrentUserLevelID;
        return array_filter(array_unique(array_merge($this->getRoles($userLevelId), $this->getReachableRoles($userLevelId))));
    }

    // Is Granted
    public function isGranted(string $role): bool
    {
        return in_array($role, $this->getAllRoles()); // Role in current and reachable roles
    }

    // Get role names from user levels
    protected function getRoleNamesFromUserLevels(int|string|null $userLevelId = null): array
    {
        $roles = $this->isLoggedIn() ? ["ROLE_USER"] : ["PUBLIC_ACCESS"];
        $userLevelId ??= $this->CurrentUserLevelID;
        $ids = explode(Config("MULTIPLE_OPTION_SEPARATOR"), strval($userLevelId));
        foreach ($ids as $id) {
            if ((int)$id === AdvancedSecurity::ADMIN_USER_LEVEL_ID/* && $this->isSysAdmin()*/) { // Super Admin
                $roles[] = "ROLE_SUPER_ADMIN";
            } else {
                foreach ($this->parameters->get("user.roles") as $userRole) {
                    if (SameString($userRole[0], $id)) {
                        $roles[] = $userRole[1];
                        break;
                    }
                }
                foreach ($this->UserLevels as $row) {
                    [$levelid, $name, $hierarchy] = $row;
                    if (SameString($levelid, $id)) {
                        $roles[] = "ROLE_" . ConstantCase($name);
                        break;
                    }
                }
            }
        }
        return array_filter(array_unique($roles));
    }

    // Display all the User Level settings (for debugging only)
    public function showUserLevelInfo(): void
    {
        echo VarDump([
            'UserLevels' => $this->UserLevels,
            'UserLevelPrivs' => $this->UserLevelPrivs,
            'CurrentUserLevelID' => $this->currentUserLevelID(),
            'UserLevelIDs' => $this->UserLevelIDs,
        ]);
    }

    // Check privilege for List page (for menu items)
    public function allowList(string $tableName): bool
    {
        return ($this->currentUserLevelPriv($tableName) & Allow::LIST->value);
    }

    // Check privilege for View page (for Allow-View / Detail-View)
    public function allowView(string $tableName): bool
    {
        return ($this->currentUserLevelPriv($tableName) & Allow::VIEW->value);
    }

    // Check privilege for Add page (for Allow-Add / Detail-Add)
    public function allowAdd(string $tableName): bool
    {
        return ($this->currentUserLevelPriv($tableName) & Allow::ADD->value);
    }

    // Check privilege for Edit page (for Detail-Edit)
    public function allowEdit(string $tableName): bool
    {
        return ($this->currentUserLevelPriv($tableName) & Allow::EDIT->value);
    }

    // Check privilege for delete
    public function allowDelete(string $tableName): bool
    {
        return ($this->currentUserLevelPriv($tableName) & Allow::DELETE->value);
    }

    // Check privilege for lookup
    public function allowLookup(string $tableName): bool
    {
        return ($this->currentUserLevelPriv($tableName) & Allow::LOOKUP->value);
    }

    // Check privilege for export
    public function allowExport(string $tableName): bool
    {
        return ($this->currentUserLevelPriv($tableName) & Allow::EXPORT->value);
    }

    // Check if user password expired
    public function isPasswordExpired(): bool
    {
        return $this->session->get(SESSION_STATUS) == "passwordexpired";
    }

    // Set session password expired
    public function setSessionPasswordExpired(): void
    {
        $this->session->set(SESSION_STATUS, "passwordexpired");
    }

    // Set login status
    public function setLoginStatus(string $status = ""): void
    {
        $this->session->set(SESSION_STATUS, $status);
    }

    // Check if user password reset
    public function isPasswordReset(): bool
    {
        return $this->session->get(SESSION_STATUS) == "passwordreset";
    }

    // Check if user is logging in (2FA)
    public function isLoggingIn2FA(): bool
    {
        return $this->session->get(SESSION_STATUS) == "loggingin2fa"
            || $this->getToken() instanceof TwoFactorAuthenticatingToken;
    }

    // Check if user is logged in (2FA)
    public function isLoggedIn2FA(): bool
    {
        return $this->getToken() instanceof TwoFactorAuthenticatedToken;
    }

    // Check if user is logged in
    public function isLoggedIn(): bool
    {
        return $this->isLoggedIn || $this->session->get(SESSION_STATUS) == "login";
    }

    // Check if user is system administrator
    public function isSysAdmin(): bool
    {
        return $this->isSysAdmin || $this->session->get(SESSION_SYS_ADMIN) === 1;
    }

    // Check if user is administrator
    public function isAdmin(): bool
    {
        $isAdmin = $this->isSysAdmin();
        return $isAdmin;
    }

    // Proxy to Security::getUser()
    public function getUser(): ?object
    {
        return $this->security?->getUser();
    }

    // Proxy to Security::getToken()
    public function getToken(): ?object
    {
        return $this->security?->getToken();
    }

    // Save User Level to cache (explicit)
    public function saveUserLevel(): void
    {
        if (Config("USE_CACHE_FOR_USER_LEVEL_AND_USER_ID")) {
            $this->cache->delete("user_level.data"); // Clear old data
            $this->cache->get("user_level.data", function () {
                return [$this->UserLevels, $this->UserLevelPrivs];
            });
        }
    }

    // Load User Level from cache (or regenerate if missing)
    public function loadUserLevel(): void
    {
        if (Config("USE_CACHE_FOR_USER_LEVEL_AND_USER_ID")) {
            [$this->UserLevels, $this->UserLevelPrivs] = $this->cache->get("user_level.data", function () {
                $this->setupUserLevel(); // This calls saveUserLevel() internally
                return [$this->UserLevels, $this->UserLevelPrivs];
            });
        } else {
            $this->setupUserLevel();
        }
    }

    // Get current user info
    public function currentUserInfo(string $fldname): mixed
    {
        if (!$this->isSysAdmin() && Config("USER_TABLE") && $this->currentUserName()) {
            return LoadUserByIdentifier($this->currentUserName())?->get($fldname);
        }
        return null;
    }

    // Activate account based on user
    public function activateUser(UserInterface $user): bool
    {
        if (!Config("REGISTER_ACTIVATE") || IsEmpty(Config("USER_ACTIVATED_FIELD_NAME"))) {
            return false;
        }
        $flash = $this->session->getFlashBag();
        if ($user) {
            try {
                if (!ConvertToBool($user->get(Config("USER_ACTIVATED_FIELD_NAME")))) {
                    $user->set(Config("USER_ACTIVATED_FIELD_NAME"), Config("USER_ACTIVATED_FIELD_VALUE"));
                    $em = $this->registry->getManagerForClass(Config("USER_TABLE_ENTITY_CLASS"));
                    $em->flush();

                    // Call User Activated event
                    $this->userActivated($user);
                    return true;
                } else {
                    $flash->add("danger", $this->language->phrase("ActivateAgain"));
                    return false;
                }
            } catch (Exception $e) {
                $flash->add("danger", $e->getMessage());
                return false;
            }
        } else {
            $flash->add("danger", $this->language->phrase("NoRecord"));
            return false;
        }
    }

    // UserID Loading event
    public function userIdLoading(): void
    {
        //Log("UserID Loading: " . $this->currentUserID());
    }

    // UserID Loaded event
    public function userIdLoaded(): void
    {
        //Log("UserID Loaded: " . $this->userIDList());
    }

    // User Level Loaded event
    public function userLevelLoaded(): void
    {
        //$this->addUserPermission(<UserLevelName>, <TableName>, <UserPermission>);
        //$this->deleteUserPermission(<UserLevelName>, <TableName>, <UserPermission>);
    }

    // Table Permission Loading event
    public function tablePermissionLoading(): void
    {
        //Log("Table Permission Loading: " . $this->CurrentUserLevelID);
    }

    // Table Permission Loaded event
    public function tablePermissionLoaded(): void
    {
        //Log("Table Permission Loaded: " . $this->CurrentUserLevel);
    }

    // User Custom Validate event
    public function userCustomValidate(string &$userName): bool
    {
        // Enter your custom code to validate user, return true if valid.
        return false;
    }

    // User Validated event
    public function userValidated(UserInterface $user): void
    {
        // Example:
        //Session('UserEmail', $user->getEmail());
    }

    // User PasswordExpired event
    public function userPasswordExpired(UserInterface $user): void
    {
        //Log("User_PasswordExpired");
    }

    // User Activated event
    public function userActivated(UserInterface $user): void
    {
        //Log("User_Activated");
    }
}
