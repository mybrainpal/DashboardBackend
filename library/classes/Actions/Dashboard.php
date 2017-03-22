<?php

/** 
 * This is the dashboard controller.
 * This controller handles everything related to the dashboard functionality.
 * 
 * @author Nati
 */
class ActionsDashboard extends Actions {
    
    protected $actions = array(
        'index' => true,
        'home'  => true
    );
    
	/**
	 * This is the function that's being called when the user doesn't enter any method.
	 * It executes the default functionality for the controller, so in this case
	 * it displays the dashboard home page.
	 */
	public function index() {
	    // Just call the home() method, so we can display the dashboard home page
	    $this->home();
	}
	
	/**
	 * Displays the dashboard "Home" page, including all the relevant graphs,
	 * tables, and other related information.
	 * 
	 * Technically, this method just loads the dashboard's "Home" template.
	 */
	public function home() {
	    // @TODO What information should we send back?
	}
}

?>