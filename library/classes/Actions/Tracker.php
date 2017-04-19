<?php 

/** 
 * This controller handles all operations for a single tracker,
 * such as retrieving the total number of clients, calculating conversion rates and more.
 * 
 * @author Nati
 */
class ActionsTracker extends Actions{
    
    protected $actions = array(
        'getclientsamount' => true,
        'getconvertedclientsamount' => true,
    );
    
    /**
     * Retrieves the amount of clients a specific tracker handled.
     */
    public function getClientsAmount() {
        /* Retrieve information from the request */
        $tracker_id = intval($this->app->input('post', 'tracker_id')); // The amount of days to retrieve
        $days = intval($this->app->input('post', 'days')); // The amount of days to retrieve

        /* Validate the input */
        // The days parameter defaults to our days constant
        $days = !empty($days) ? $days : DEFAULT_QUERY_DAYS;
        
        // Make sure the tracker is not empty
        if( empty($tracker_id) ) {
            error('Invalid tracker ID.');
        }
        
        // Days can't be a negative number
        if( $days <= 0) {
            error('The amount of days has to be larger than 0!');
        }
        
        // Create a new Tracker
        $tracker = new Tracker($tracker_id);
        
        // Make sure the current user is the owner of the tracker
        if( $tracker->owner_id !== $this->app->user->id 
            && !$this->app->user->isSuperUser() ) {
            error('You do not have permissions to view this tracker!');
        }
        
        // Get the amount of unique clients for that tracker
        $clients = $tracker->getClients(false, $days, DB_MAX_LIMIT);
            
        // If we've reached here, everything is OK. Return the clients amount
        $this->app->output->setArguments(array(
            FLAG_SUCCESS => true,
            ':total_clients' => $clients,
        ));
    }
    
    /**
     * Retrieves the amount of clients a specific tracker handled.
     */
    public function getConvertedClientsAmount() {
        /* Retrieve information from the request */
        $tracker_id = intval($this->app->input('post', 'tracker_id')); // The amount of days to retrieve
        $days = intval($this->app->input('post', 'days')); // The amount of days to retrieve
    
        /* Validate the input */
        // The days parameter defaults to our days constant
        $days = !empty($days) ? $days : DEFAULT_QUERY_DAYS;
    
        // Make sure the tracker is not empty
        if( empty($tracker_id) ) {
            error('Invalid tracker ID.');
        }
    
        // Days can't be a negative number
        if( $days <= 0) {
            error('The amount of days has to be larger than 0!');
        }
    
        // Create a new Tracker
        $tracker = new Tracker($tracker_id);
        
        // Make sure the current user is the owner of the tracker
        // or the system administrator
        if( $tracker->getOwner() !== $this->app->user->id
            && !$this->app->user->isSuperUser() ) {
            error('You do not have permissions to view this tracker!');
        }
    
        // Get the amount of unique clients for that tracker
        $clients = $tracker->getConvertedClients(false, $days, DB_MAX_LIMIT);
    
        // If we've reached here, everything is OK. Return the clients amount
        $this->app->output->setArguments(array(
            FLAG_SUCCESS => true,
            ':total_clients' => $clients,
        ));
    }
}

?>