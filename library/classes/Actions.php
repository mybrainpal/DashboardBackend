<?php

/**
 * The base class for every other action class.
 *
 * @author Nati
 *        
 */
abstract class Actions {
	/**
	 * A reference to the application main object.
	 * 
	 * @var BrainPal
	 */
	protected $app;
	
	/**
	 * Does the action requires authentication? Defaults to 'true' (yes).
	 * 
	 * @var boolean
	 */
	protected $require_auth;
	
	/**
	 * A dictionary containing all the actions the module offers,
	 * and whether they need authentication or not.
	 * 
	 * @var array
	 */
	protected $actions = array();
	
	/**
	 * Constructs the Action instance and initialize properties.
	 * 
	 * @param BrainPal $app_object A reference to the application object
	 */
	public function __construct(&$app_object) {
		// Save the application object in a property
		$this->app = &$app_object;
		
		// Set the authentication flag to 'true' (requires authentication) by default
		$this->require_auth = true;
	}
	
	/**
	 * Checks if the action requires authentication,
	 * and if it does, make sure the user has already logged in.
	 * 
	 * @return boolean True if no authentication required or the user is authenticated, false otherwise.
	 */
	public function checkAuth() {
	    // Get the method to execute
	    $method = $this->app->getMethod();

	    // Check if the method exists and is not NULL (it can be 'empty' as
	    // boolean value of 'false' is considered 'empty' by PHP)
	    if(isset($this->actions[$method]) && !is_null($this->actions[$method])) {
	        // It does, store whether it needs authentication or not
	        $this->require_auth = $this->actions[$method];
	    } else {
	        // It doesn't, terminate the script
	        error('method not defined in $actions dictionary!');
	    }
	    
	    // Check if the action requires authentication
	    if( $this->require_auth ) {
	        // It does, check if the user is logged in
	        if( !$this->app->user->authenticated ) {
	            // It didn't, return false
	            return false;
	        }
	    }
	    
	    // Otherwise, all is well, return true
	    return true;
	}
}

?>