<?php 

/** 
 * This is the client's log storage controller.
 * This controller stores, retrieves, and parse, log sent from the client.
 * 
 * @author Nati
 */
class ActionsLog extends Actions{
    
    protected $actions = array(
        'add' => false,
    );
    
    /**
     * Stores all the different types of log in the system,
     * and their matching ID.
     * 
     * @var array
     */
    private $log_types = array(
        'ERROR'     => 0,
        'WARNING'   => 1,
        'INFO'      => 2,
        'DEBUG'     => 3
    );
    
	/**
	 * Stores a front-end log message in the database for later inspection.
	 */
	public function add() {
	    /* Retrieve information from the request */
	    $client_id = intval($this->app->input('session', 'client_id')); // The client ID
		$message = $this->app->input('post', 'message'); // The log message
		$level = $this->app->input('post', 'level'); // The message level (error, warning, info, etc.)
		$timestamp = $this->app->input('post', 'timestamp'); // Exact time stamp of when the event occurred
		
		/* Validate the input */
		// Validate the message level
		if( !isset($this->log_types[$level]) ) {
		    /* The server could not identify the message level */
		    // Parse the error message
		    $error = 'Could not identify the message level. Available levels are: ';
		    $error .= implode(', ', $this->log_types);
		    
		    // Remove the last comma and add a line break
		    error(substr($error, 0, -2));
		}

		// Validate the time stamp
		if( !is_numeric($timestamp) ) {
		    // The time stamp is not valid
		    error('`timestamp` has to be a valid UNIX timestamp!');
		}
		
		// Make sure the log message is not empty
		if( empty($message) ) {
		    // It is empty, inform the developer
		    error('The log message cannot be empty!');
		}
		
		/* If we've reached here, the input is valid */
		// Convert the log message into its matching ID
		$level = intval($this->log_types[$level]);

		// Store the log message in the DB
		$this->app->db->insert_into('`client_logs`')->_('(`client_id`, `message`, `level`, `timestamp`)')
		->values('(:client_id, :message, :level, :timestamp)', array(
		    ':client_id' => $client_id,
		    ':message' => $message,
		    ':level' => $level,
		    ':timestamp' => mysql_date($timestamp),
		))->execute();
		
		// If we've passed the last query we can safely assume that we have an ID to use, so get it
		$log_id = $this->app->db->lastInsertId;
		
		// Return the inserted log ID, along with the success flag
		$this->app->output->setArguments(array(
            FLAG_SUCCESS => true,
            ':log_id' => $log_id
        ));
	}
}

?>