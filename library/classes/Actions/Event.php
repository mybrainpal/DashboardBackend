<?php 

/** 
 * This controller is responsible for adding and retrieving events to and from the database.
 * 
 * @author Nati
 */
class ActionsEvent extends Actions{
    
    protected $actions = array(
        'add' => false,
    );
    
	/**
	 * Stores a front-end event in the database for analyzing.
	 */
	public function add() {
	    /* Retrieve information from the request */
	    $client_id = intval($this->app->input('session', 'client_id')); // The client ID
	    $session_id = intval($this->app->input('session', 'session_id')); // The session ID
	    $tracker_id = intval($this->app->input('session', 'tracker_id')); // The session ID @TODO Set this in the session
	    $view_id = intval($this->app->input('post', 'tracker')); // The view ID
	    $experiment_id = intval($this->app->input('post', 'experimentId')); // The experiment ID
	    $experiment_group_id = intval($this->app->input('post', 'experimentGroupId')); // The experiment's groups ID
		$event = $this->app->input('post', 'event'); // The event type
		$selector = $this->app->input('post', 'selector') | ''; // The object the event occurred on
		$timestamp = $this->app->input('post', 'timestamp'); // Exact timestamp of when the event occurred
		$metadata = array(); // Other meta data we'd like to store
		$error = array(); // A temporary placeholder for any errors that might occur
		
		/* Validate the input */
		// Validate the timestamp
		if( !is_numeric($timestamp) ) {
		    // The timestamp is not valid
		    $error[] = '`timestamp` has to be a valid UNIX timestamp!';
		}
		
		// Make sure the log message is not empty
		if( empty($event) ) {
		    // It is empty, inform the developer
		    $error[] = 'The event type cannot be empty!';
		}
		
		// If there has been an error, send it and terminate the script
		if( !empty($error) ) {
		    error($error);
		}
		
		/* If we've reached here, the input is valid */
		// Store the event in the DB
		$this->app->db->insert_into('`events`')->_('(`client_id`, `session_id`, `tracker_id`,
		    `view_id`, `experiment_id`, `experiment_group_id`, `event`, `selector`, `metadata`, `timestamp`)')
		->values('(:client_id, :session_id, :tracker_id, :view_id, :experiment_id, :experiment_group_id,
		    :event, :selector, :metadata, :timestamp)', array(
                ':client_id' => $client_id,
                ':session_id' => $session_id,
		        ':tracker_id' => $tracker_id,
                ':view_id' => $view_id,
		        ':experiment_id' => $experiment_id,
		        ':experiment_group_id' => $experiment_group_id,
		        ':event' => $event,
		        ':selector' => $selector,
		        ':metadata' => json_encode($metadata),
                ':timestamp' => mysql_date($timestamp),
		))->execute();
		
		// If we've passed the last query we can safely assume that we have an ID to use, so get it
		$event_id = $this->app->db->lastInsertId;
		
		// Return the inserted log ID, along with the success flag
		$this->app->output->setArguments(array(
            FLAG_SUCCESS => true,
            ':event_id' => $event_id
        ));
	}
}

?>