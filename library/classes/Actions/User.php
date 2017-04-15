<?php

/** 
 * This is the user category controller.
 * This controller handles everything user related, ie. login in, profile viewing, etc.
 * 
 * @author Nati
 */
class ActionsUser extends Actions {
    
    protected $actions = array(
        'index' => false,
        'profile' => true,
        'basicinfo' => true,
        'additionalinfo' => true,
        'getclientsamount' => true,
        'getconvertedclientsamount' => true,
    );
    
    /**
     * Constructs the ActionUser instance and initialize its properties.
     * Declares that this controller DOES NOT require authentication.
     * 
	 * @param BrainPal $app_object A reference to the application object.
	 */
	public function __construct(&$app_object) {
        // Call the main Action constructor
        parent::__construct($app_object);
	}
	
	/**
	 * This is the function that's being called when the user doesn't enter any method.
	 * It executes the default functionality for the controller, so in this case
	 * it displays the profile page for a logged in user,
	 * or redirects to a login page for an unauthenticated one.
	 */
	public function index() {
		// Check if the user is logged in
		if( $this->app->user->authenticated ) {
			// He is! Send back the authentication flag
		    $this->app->output->setArguments(array('auth' => true));
		}
		else {
		    // Inform the front-end the user is not authenticated
		    $this->app->output->setArguments(array('auth' => false));
		}
	}
	
	/**
	 * Displays the complete profile of the specified user.
	 * If no user is specified, display the profile of the currently logged in user.
	 * 
	 * If the user is not authenticated, redirect to the login page.
	 */
	public function profile() {	    
	    // @TODO Load the profile page with the user values
	}
	
	/**
	 * Get/set the basic information for the current user (username and password).
	 */
	public function basicInfo() {
		// He is, Check if the form has been submitted
		if( !empty($this->app->input('post')) ) {
			// Check that the user has entered his correct password
			if( $this->app->input('post', 'verify_password') === $this->app->user->password ) {
				// If the user is changing his password, check that it matches
				if( !empty($this->app->input('post', 'password1')) &&
					($this->app->input('post', 'password1') === $this->app->input('post', 'password2')) ) {
					// It does, Set the basic information of the user
					$this->app->user->username = $this->app->input('post', 'username');
					$this->app->user->password = $this->app->input('post', 'password1');
					
					// Save the additional information of the user in the DB
					$this->app->user->save(USER_SAVE_BASIC);
					
					// Return that the saving process succeeded
					$this->app->output->setArguments(array(FLAG_SUCCESS => true));
				}
				else {
					// It doesn't, inform the user
					error('Passwords Doesn\'t match at User::basicInfo()');
				}					
			}
			else {
				// He hasn't, terminate with an error
				error('Wrong password! at User::basicInfo()');
			}
		}
		else {
			// Returns the user basic information
			// @TODO Add the template arguments
		}
	}
	
	/**
	 * Get/set the additional information for the current user (full name, address, phone number, etc.).
	 */
	public function additionalInfo() {
		// He is, Check if the form has been submitted
		if( !empty($this->app->input('post')) ) {
			// Set the additional information of the user
			$this->app->user->first_name = $this->app->input('post', 'first_name');
			$this->app->user->last_name = $this->app->input('post', 'last_name');
			$this->app->user->gender = ($this->app->input('post', 'gender') === 'Male') ? '0' : '1'; // Gender (0 = male, 1 = female)
			$this->app->user->phone_number = preg_replace('/[^0-9+-]/', '', $this->app->input('post', 'phone_number')); // Phone number (digits, '+' and '-' only)
			$this->app->user->address = $this->app->input('post', 'address');
			$this->app->user->hobbies = $this->app->input('post', 'hobbies');
			
			// @TODO Add support for profile pictures
			
			// Save the additional information of the user in the DB
			$this->app->user->save(USER_SAVE_ADDITIONAL);
			
			// Return that the registration process succeeded
			$this->app->output->setArguments(array(FLAG_SUCCESS => true));
		}
		else {
			// Set its arguments
			// @TODO Add the additional information arguments
		}
	}
	
	/**
	 * Retrieves the amount of clients this specific user handled.
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
	
	    // Get the amount of unique clients for that user
	    $clients = $this->app->user->getClients(false, $days, DB_MAX_LIMIT);
	
	    // If we've reached here, everything is OK. Return the clients amount
	    $this->app->output->setArguments(array(
	        FLAG_SUCCESS => true,
	        ':total_clients' => $clients[0][0],
	    ));
	}
	
	/**
	 * Retrieves the amount of clients this specific user handled.
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
	
	    // Get the amount of unique clients for that user
	    $clients = $this->app->user->getConvertedClients(false, $days, DB_MAX_LIMIT);
	
	    // If we've reached here, everything is OK. Return the clients amount
	    $this->app->output->setArguments(array(
	        FLAG_SUCCESS => true,
	        ':total_clients' => $clients[0][0],
	    ));
	}
}

?>