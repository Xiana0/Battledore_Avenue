<?php

/**
 * PHPMaker constants
 */

namespace PHPMaker2026\Project1;

/**
 * Constants
 */
const PROJECT_NAMESPACE = __NAMESPACE__ . '\\';

// System
define(PROJECT_NAMESPACE . "IS_WINDOWS", strtolower(substr(PHP_OS, 0, 3)) === "win"); // Is Windows OS
const PATH_DELIMITER = IS_WINDOWS ? '\\' : '/'; // Physical path delimiter
const CLASS_PREFIX = '_'; // Prefix for invalid class names

// Product version
const PRODUCT_VERSION = '26.10.0';

// Project
const PROJECT_NAME = 'project1'; // Project name
const PROJECT_ID = '{FE4FB9E5-AAE5-4131-86B3-707F288EED86}'; // Project ID

// Character encoding (utf-8)
const PROJECT_CHARSET = 'utf-8'; // Charset
const EMAIL_CHARSET = 'utf-8'; // Charset
const PROJECT_ENCODING = 'UTF-8'; // Character encoding (uppercase)

// Session keys
const SESSION_STATUS = PROJECT_NAME . '_Status'; // Login status
const SESSION_USER_NAME = SESSION_STATUS . '_UserName'; // User name
const SESSION_USER_ID = SESSION_STATUS . '_UserID'; // User ID
const SESSION_USER_PRIMARY_KEY = SESSION_STATUS . '_UserPrimaryKey'; // User primary key
const SESSION_USER_IDENTIFIER = SESSION_STATUS . '_UserIdentifier'; // User identifier
const SESSION_USER_REMEMBER_ME = SESSION_STATUS . '_RememberMe'; // Remember me
const SESSION_USER_LEVEL_ID = SESSION_STATUS . '_UserLevel'; // User Level ID
const SESSION_USER_LEVEL_LIST = SESSION_STATUS . '_UserLevelList'; // User Level List
const SESSION_USER_LEVEL = SESSION_STATUS . '_UserLevelValue'; // User Level
const SESSION_PARENT_USER_ID = SESSION_STATUS . '_ParentUserId'; // Parent User ID
const SESSION_SYS_ADMIN = PROJECT_NAME . '_SysAdmin'; // System admin
const SESSION_PROJECT_ID = PROJECT_NAME . '_ProjectId'; // Project ID
const SESSION_USER_LEVEL_MSG = PROJECT_NAME . '_UserLevelMessage'; // User Level Message
const SESSION_INLINE_MODE = PROJECT_NAME . '_InlineMode'; // Inline mode
const SESSION_BREADCRUMB = PROJECT_NAME . '_Breadcrumb'; // Breadcrumb
const SESSION_HISTORY = PROJECT_NAME . '_History'; // History (Breadcrumb)
const SESSION_TEMP_IMAGES = PROJECT_NAME . '_TempImages'; // Temp images
const SESSION_CAPTCHA_CODE = PROJECT_NAME . '_Captcha'; // Captcha code
const SESSION_LANGUAGE_ID = PROJECT_NAME . '_LanguageId'; // Language ID
const SESSION_MYSQL_ENGINES = PROJECT_NAME . '_MySqlEngines'; // MySQL table engines
const SESSION_ACTIVE_USERS = PROJECT_NAME . '_ActiveUsers'; // Active users
const SESSION_TWO_FACTOR_AUTHENTICATION_TYPE = PROJECT_NAME . '_TwoFactorAuthenticationType'; // Two factor authentication type
