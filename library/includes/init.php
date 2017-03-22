<?php
require_once dirname(__FILE__) . '/constants.php'; // Get the constants
require_once dirname(__FILE__) . '/functions.php'; // Load the main functions

/**
 * Dynamically include classes without using an 'include' statement.
 * 
 * @param string $class_name The name of the class that the user is trying to include.
 * @return boolean True if the class could be found, false otherwise.
 */
function __autoload($class_name) {
	// For security measures, remove any dangerous characters
	$class_name = preg_replace('/[^a-z_]/i', '', $class_name); 
	
	// Make sure we always know where the main class is
	if($class_name === 'BrainPal') {
	    // The main class should alwyas be located in the root of the 'classes' folder
	    $include_path = CLASS_PATH . 'BrainPal.php';
	} else {
    	// For any other class, just guess the class file path
    	$include_path = CLASS_PATH . preg_replace('/([a-z])([A-Z])/', '$1/$2', $class_name) . '.php';
	}
	
	// Check if the file exists
	if( file_exists($include_path) ) {
		// Include the class file
		require_once $include_path;

		// Check if the class exists
		if( class_exists($class_name, false) ) {
			// It is, return true
			return true;
		}
	}
	
	// Return false, the class could not be loaded
	return false;
}

/**
 * Called when script execution ends.
 * Responsible for writing the session data, close open sockets,
 * and so on. 
 */
function shutdown() {
    // Write the session data to the disk
    session_write_close();
}

// Fix GAE PATH_INFO
$_SERVER['PATH_INFO'] = explode('?', $_SERVER['REQUEST_URI'])[0];

// Disable cross-origin protections (so we can use Ajax from any domain)
// @TODO We REALLY need to white list this.
$origin = preg_replace('/[^a-z0-9\.:\/]/i', '', $_SERVER['HTTP_ORIGIN']); // Get the origin of the request
header("Access-Control-Allow-Origin: $origin"); // Allow that origin to access this domain through AJAX
header("Access-Control-Allow-Credentials: true"); // Allow the origin to send cookies to this domain

// Register the autoloader
spl_autoload_register('__autoload');

// Register the shutdown function
register_shutdown_function('shutdown');

// Set the session name to match the host the client come's from
session_name('BrainPal_' . preg_replace('/[^a-z]/i', '', SESSION_ORIGIN));

// Set session lifetime for 365 days (60sec * 60mins * 24hours * 365days)
ini_set('session.cookie_lifetime', 60 * 60 * 24 * 365);
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 365);

// Start the session
session_start();
?>