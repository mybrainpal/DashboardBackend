<?php

/** 
 * This controller handles administrators actions.
 * 
 * @author Nati
 */
class ActionsAdministration extends Actions {
    
    protected $actions = array(
        'addcustomer' => true,
        'deletecustomer' => true,
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
        if( !$this->app->user->isSuperUser() ) {
            // It isn't, throw an exception
            error('This controller is only accessible by an administrator!');
        }
	}
    
	/**
	 * Adds a new customer to the database.
	 */
	public function addCustomer() {
	    // Check if the registration form has been submitted
	    if( !empty($this->app->input('post')) ) {
	        // Process the registration input
	        $username = $this->app->input('post', 'username'); // Get the username
	        $password = $this->app->input('post', 'password'); // Get the password
	        
	        // Generate a new empty customer instance
	        $new_customer = new Customer(0);
	        
	        // Set its properties
	        $new_customer->username = $username;
	        $new_customer->password = $password;
	        
	        // Create the new customer
	        $new_customer->create();
	        	
	        // Return that the registration process succeeded along with the new customer ID
	        $this->app->output->setArguments(array(
	            FLAG_SUCCESS => true,
	            ':id' => $new_customer->id
	        ));
	    }
	    else {
	        // He isn't! Report the error
			error('No data received');
	    }
	}
	
	/**
	 * Delete the request user.
	 */
	public function deleteCustomer() {
	    // Check if the registration form has been submitted
	    if( !empty($this->app->input('post')) ) {
	        // Process the registration input
	        $customer_id = intval($this->app->input('post', 'customer_id')); // Get the customer ID
	         
	        // Create a new customer instance
	        $customer = new Customer($customer_id);
	         
	        // Delete the customer from the DB
	        $customer->delete();
	
	        // Return that the registration process succeeded
	        $this->app->output->setArguments(array(
	            FLAG_SUCCESS => true
	        ));
	    }
	    else {
	        // He isn't! Report the error
	        error('No data received');
	    }
	}
	
	/**
	 * Retrieves the amount of clients the system handled.
	 */
	public function getClientsAmount() {
	    /* Retrieve information from the request */
	    $days = intval($this->app->input('post', 'days')); // The amount of days to retrieve
	
	    /* Validate the input */
	    // The days parameter defaults to our days constant
	    $days = !empty($days) ? $days : DEFAULT_QUERY_DAYS;
	
	    // Make sure the client is not already registered
	    if( $days <= 0) {
	        error('The amount of days has to be larger than 0!');
	    }
	    
	    // Get all the unique clients for that tracker
	    $result = $this->app->db->select('count(id)')->from('`clients`')->where(
            '(`created` BETWEEN DATE_SUB(SUBDATE(CURDATE(),1), INTERVAL :days DAY) AND CURDATE())', array(
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
	
	    /* Validate the input */
	    // The days parameter defaults to our days constant
	    $days = !empty($days) ? $days : DEFAULT_QUERY_DAYS;
	
	    // Make sure the client is not already registered
	    if( $days <= 0) {
	        error('The amount of days has to be larger than 0!');
	    }
	     
	    // Get all converted clients
	    $result = $this->app->db->select('count(id)')->from('`clients`')->where(
            '`state`=:state AND
            (`created` BETWEEN DATE_SUB(SUBDATE(CURDATE(),1), INTERVAL :days DAY) AND CURDATE())', array(
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