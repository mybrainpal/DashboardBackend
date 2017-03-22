<?php

/** 
 * This is the users category controller.
 * This controller handles everything users related, ie. creating users, editing users or listing users.
 * 
 * @author Nati
 */
class ActionsUsers extends Actions {
    
    protected $actions = array(
        'index' => true,
        'listUsers' => true,
        'createUser' => true,
    );
    
	/**
	 * This is the function that's being called when the user doesn't enter any method.
	 * It executes the default functionality for the controller, so in this case
	 * it displays the users table page.
	 */
	public function index() {
		// The default action is to list the system's users
		$this->listUsers();
	}
	
	public function listUsers() {
	    // Return the list of users
	    // @TODO Return the users list
	}
	
	/**
	 * Display the user creation form page for administrators,
	 * or create a new user if the registration form has been submitted.
	 */
	public function createUser() {
		// Check if the registration form has been submitted
		if( !empty($this->app->input('post')) ) {
			// Process the registration input
			$username = $this->app->input('post', 'username'); // Get the username
			$password = $this->app->input('post', 'password'); // Get the password
			
			// Create the new user and get the email validation token
			$this->app->user->create($username, $password);
			
			// Return that the registration process succeeded
			$this->app->output->setArguments(array(':success' => true));
			
		}
		else {
			// It isn't! Load the registration template
			$this->app->output->setTemplate('users/create');
		}
	}
}

?>