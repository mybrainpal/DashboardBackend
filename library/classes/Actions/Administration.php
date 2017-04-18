<?php

/** 
 * This controller handles administrators actions.
 * 
 * @author Nati
 */
class ActionsAdministration extends Actions {
    
    protected $actions = array(
        'adduser' => true,
        'getclientsamount' => true,
        'getconvertedclientsamount' => true,
    );
    
/**
     * Constructs the ActionsAdministration instance and initialize its properties.
     * Makes sure that this controller is only accessible by an administrator.
     * 
	 * @param BrainPal $app_object A reference to the application object.
	 */
	public function __construct(&$app_object) {
        // Call the main Action constructor
        parent::__construct($app_object);
        
        // Make sure the current user is the administrator
        if($this->app->user->id !== 1) {
            // It isn't, throw an exception
            error('This controller is only accessible by an administrator!');
        }
	}
    
	/**
	 * Display the user creation form page for administrators,
	 * or create a new user if the registration form has been submitted.
	 */
	public function addUser() {
	    // Check if the registration form has been submitted
	    if( !empty($this->app->input('post')) ) {
	        // Process the registration input
	        $username = $this->app->input('post', 'username'); // Get the username
	        $password = $this->app->input('post', 'password'); // Get the password
	        	
	        // Create the new user and get the email validation token
	        $this->app->user->create($username, $password);
	        	
	        // Return that the registration process succeeded
	        $this->app->output->setArguments(array(FLAG_SUCCESS => true));
	        	
	    }
	    else {
	        // He isn't! Report the error
			error('No data received at Administration::addUser()');
	    }
	}
	
	/**
	 * Retrieves the amount of clients the system handled.
	 */
	public function getClientsAmount() {
	    /* Retrieve information from the request */
	    $days = intval($this->app->input('post', 'days')); // The amount of days to retrieve
	    $error = array(); // A temporary placeholder for any errors that might occur
	
	    /* Validate the input */
	    // The days parameter defaults to our days constant
	    $days = !empty($days) ? $days : DEFAULT_QUERY_DAYS;
	
	    // Make sure the client is not already registered
	    if( $days <= 0) {
	        $error[] = 'The amount of days has to be larger than 0!';
	    }
	
	    // If there has been an error, send it and terminate the script
	    if( !empty($error) ) {
	        error($error);
	    }
	    
	    // Get all the unique clients for that tracker
	    $result = $this->app->db->select('count(id)')->from('`clients`')->where(
            '(`created` BETWEEN DATE_SUB(NOW(), INTERVAL :days DAY) AND NOW())', array(
                ':days' => $days,
        ))->execute();
	
	    // If we've reached here, everything is OK. Return the clients amount
	    $this->app->output->setArguments(array(
	        FLAG_SUCCESS => true,
	        ':total_clients' => $result[0][0],
	    ));
	}
	
	/**
	 * Retrieves the amount of converted clients the system handled.
	 */
	public function getConvertedClientsAmount() {
	    /* Retrieve information from the request */
	    $days = intval($this->app->input('post', 'days')); // The amount of days to retrieve
	    $error = array(); // A temporary placeholder for any errors that might occur
	
	    /* Validate the input */
	    // The days parameter defaults to our days constant
	    $days = !empty($days) ? $days : DEFAULT_QUERY_DAYS;
	
	    // Make sure the client is not already registered
	    if( $days <= 0) {
	        $error[] = 'The amount of days has to be larger than 0!';
	    }
	
	    // If there has been an error, send it and terminate the script
	    if( !empty($error) ) {
	        error($error);
	    }
	     
	    // Get all converted clients
	    $result = $this->app->db->select('count(id)')->from('`clients`')->where(
            '`state`=:state AND
            (`created` BETWEEN DATE_SUB(NOW(), INTERVAL :days DAY) AND NOW())', array(
                ':state' => SESSION_STATE_CONVERTED,
                ':days' => $days,
        ))->execute();

        // If we've reached here, everything is OK. Return the clients amount
        $this->app->output->setArguments(array(
            FLAG_SUCCESS => true,
            ':total_clients' => $result[0][0],
        ));
	}
}

?>