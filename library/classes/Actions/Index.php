<?php

/** 
 * This is the index controller.
 * This is the default controller, executed in case no controller was specified.
 * 
 * @author Nati
 */
class ActionsIndex extends Actions {
    
    protected $actions = array(
        'index' => false,
    );
    
	/**
	 * This is the function that's being called when the user doesn't enter any method.
	 * It executes the default functionality for the controller, so in this case
	 * it just redirects the user to the login page if he isn't logged in,
	 * or to the dashboard page if he is.
	 */
	public function index() {
	    // Check if the user is logged in
	    if( $this->app->user->authenticated ) {
	        // He is, redirect to the main dashboard
	        redirect('/Dashboard/Home');
	    }
	    else {
    		// He isn't, redirect to the login page
    		redirect('/User/Login');
	    }
	}
}

?>