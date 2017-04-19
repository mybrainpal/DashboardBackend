<?php

/**
 * Handles what happens when an error occur.
 * 1. Log the error (If needed)
 * 2. Display the error(If needed)
 * 3. Terminate the script
 * 
 * @param string $message The error message to display.
 * @param boolean $log Should we log the error?.
 * @param boolean $display Should we display the error?.
 */
function error($message, $log = true, $display = true) {
	global $app;
	
	/* Add the error location to the error message */
	// Get the debug back trace (without the object itself or the function arguments)
	$backtrace = debug_backtrace(2, 2)[1];
	
	// Add the class name and method to the error message
	$message .= sprintf(' At %s::%s', $backtrace['class'], $backtrace['function']);
	
	// Log the error (If needed)
	if($log) $app->logger->log($message, LOG_CRIT);
	
	// Display the error (If needed)
	if($display) $app->output->error($message);
	
	// Terminate the script
	exit();
}

/**
 * Logs the given information string.
 * 
 * @param string $message The error message to display.
 */
function info($message) {
	global $app;

	// Log the information
	$app->logger->log($message, LOG_INFO);
}

/**
 * Logs debug information.
 *
 * @param string $message The error message to display.
 */
function debug($message) {
    global $app;

    // Log the information
    $app->logger->log($message, LOG_DEBUG);
}

/**
 * Redirect the browser to the given URL and terminate the script.
 * 
 * @param string $url The URL to redirect the request to.
 */
function redirect($url) {
    global $app;
    
    // Remove CRLF characters
    $url = str_replace(array("\r", "\n"), array('', ''), $url);
    
    // Remove forwarding slash (we're adding it automatically later
    $url = trim($url, '/');
    
    // Check if this is a full URL or relative one
    if(strpos($url, 'http') !== 0) {
        // Relative, add the site prefix
        $url = sprintf('%s%s', WEB_ROOT, $url);
    }

    // Check if the request has been sent using AJAX
    if($app->json) {
        // We're sending a JSON response
        header('Content-Type: application/json');
        
        // Send the redirect parameter
        echo json_encode(array('redirect' => $url));
    } else {
        // Regular request, redirect the request to the requested $url using a header
        header("Location: $url");
    }
    
    // Goodbye!
    exit();
}

/**
 * Converts a UNIX timestamp into a proper DATETIME MySQL string.
 * This function also supports timestamps with microseconds.
 * 
 * @param int $timestamp The UNIX timestamp as an integer.
 * @return string The proper DATETIME value for the supplied timestamp.
 */
function mysql_date($timestamp) {
    // Get the length of the timestamp
    $timestamp_length = strlen($timestamp);
    
    // Check if the timestamp contains microseconds (normal timestamps contain 10 characters max)
    if($timestamp_length > 10) {
        // It does, get the microseconds
        $microseconds = substr($timestamp, 10 - $timestamp_length);
        
        // Add trailing zeroes if needed (microseconds should always be 6 digit numbers)
        $microseconds = str_pad($microseconds, 6, '0', STR_PAD_RIGHT);
        
        // Remove the microseconds
        $timestamp = substr($timestamp, 0, 10);
    } else {
        // Otherwise just set the microseconds to 0
        $microseconds = '000000';
    }
       
    // Return the complete date, parsed as a MySQL DATETIME string
    return date('Y-m-d H:i:s.', $timestamp) . $microseconds;
}

?>