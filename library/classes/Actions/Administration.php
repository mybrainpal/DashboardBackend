<?php

/** 
 * This controller handles administrators actions.
 * 
 * @author Nati
 */
class ActionsAdministration extends Actions {
    
    protected $actions = array(
        'adduser' => true,
    );
    
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
}

?>