<?php
/* Path constants */
define('APP_PATH', substr($_SERVER['SCRIPT_FILENAME'], 0, strlen(basename($_SERVER['SCRIPT_FILENAME'])) * -1)); // The root path for the application
define('SCRIPT_NAME', basename($_SERVER['SCRIPT_FILENAME'])); // The currently executed script name
define('LIB_PATH', APP_PATH . 'library/'); // The library directory path
define('TPL_PATH', APP_PATH . 'templates/'); // The path for the templates directory
define('CLASS_PATH', LIB_PATH . 'classes/'); // The classes directory path, containing PHP classes only
define('INC_PATH', LIB_PATH . 'includes/'); // The includes directory path, containing regular PHP functions

/* Database constants */
define('DB_CONNECTION_NAME', 'mysql:unix_socket=/cloudsql/dashboard-161017:us-east1:sql-test;'); // The database host
define('DB_USER', 'root'); // The database username
define('DB_PASS', 'shovavim'); // The database password
define('DB_NAME', 'brainpal'); // The database name
define('DEFAULT_QUERY_DAYS', 28); // The default amount of days for data retrieval
define('DEFAULT_QUERY_LIMIT', 30); // The default maximum amount of rows to query
define('DB_MAX_LIMIT', '9223372036854775807'); // The maximum number of rows a table can contain

/* Output constants */
define('REQUEST_PROTOCOL', ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')) ? 'https' : 'http'); // Check the request protocol (http or https)
define('HOST_NAME', preg_replace('/[^a-z0-9:\-\.]*/i', '', $_SERVER['HTTP_HOST'])); // The Host (without dangerous characters)
define('WEB_DIR', substr($_SERVER['SCRIPT_NAME'], 1, strlen('/index.php') * -1)); // The web directory
define('WEB_ROOT', sprintf('%s://%s/%s', REQUEST_PROTOCOL, HOST_NAME, WEB_DIR)); // The full web path (proto + host + dir)
define('TPL_MAIN', 'main'); // The main template name
define('TPL_HEAD', 'head'); // The header template name
define('TPL_HEADER', 'header'); // The header template name
define('TPL_FOOTER', 'footer'); // The header template name
define('TPL_ERROR', 'error'); // The error template name
define('FLAG_SUCCESS', ':success'); // The success flag name
define('VAR_ERROR', ':error_msg'); // The error variable name
define('VAR_CSRF_TOKEN', ':csrf_token'); // The CSRF token variable name

/* SESSION CONSTANTS */
define('SESSION_KEEPALIVE', 15 * 60); // The number of minutes (15) that a client's session will be kept alive
define('SESSION_ORIGIN', parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_HOST)); // The host from which the request came
define('TOKEN_KEEPALIVE', 60 * 30); // The number of minutes (10) each token will be alive
define('SESSION_STATE_CONVERTED', 'FINAL'); // The session state - indicating conversion

/* Security constants */
define('SALT', '4481c274433c22997e11eecd7de7af12');

?>