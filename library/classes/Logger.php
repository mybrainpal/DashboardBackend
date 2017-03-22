<?php

/**
 * A class responsible for the handling of logging in the application.
 *
 * @author Nati
 *        
 */
class Logger {
	
	/**
	 * The log file handler.
	 * 
	 * @var resource
	 */
	private $fh;
	
	/**
	 * Initialize the log.
	 */
	public function __construct() {
		// @TODO Do we really need this?
	}
	
	/**
	 * Log a message.
	 * 
	 * @param string $message The message to log.
	 * @param string $type The type of the message (INFO, WARNING, ERROR, etc.).
	 * @example 26/09/2014 19:24:31	ERROR - The template 'user/login' could not be found!	(ActionsUser:login)
	 */
	public function log($message, $type = LOG_INFO) {
		global $app;
		
		// Parse the log message.
		$log_message = sprintf("%s - %s\t(%s:%s)\n", date("d/m/Y H:i:s"), $message, $app->getClass(), $app->getMethod());
		
		// Write the log message
		syslog($type, $log_message);
	}
	
	/**
	 * Called when the object is destroyed.
	 * Logs the termination of the application.
	 */
	public function __destruct() {
		// @TODO Do we really need this?
	}
}

?>