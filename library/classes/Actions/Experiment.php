<?php 

/** 
 * This controller handles all operations for a single experiment,
 * such as retrieving the total number of clients, calculating group conversion rates and more.
 * 
 * @author Nati
 */
class ActionsExperiment extends Actions{
    
    protected $actions = array(
        'getclients' => true,
        'getclientsamount' => true,
    );
    
    /**
     * Retrieves the clients a specific experiment handled.
     */
    public function getClients() {
        /* Retrieve information from the request */
        $experiment_id = intval($this->app->input('post', 'experiment_id')); // The ID of the experiment
        $days = intval($this->app->input('post', 'days')); // The amount of days to retrieve
        $limit = intval($this->app->input('post', 'limit')); // The amount of different clients to retrieve
    
        /* Validate the input */
        // The days parameter defaults to our days constant
        $days = !empty($days) ? $days : DEFAULT_QUERY_DAYS;
    
        // Make sure the experiment ID is not empty
        if( empty($experiment_id) ) {
            error('Invalid experiment ID.');
        }
    
        // Days can't be a negative number
        if( $days <= 0) {
            error('The amount of days has to be larger than 0!');
        }
        
        // Clients limit can't be a negative number
        if( $limit <= 0) {
            error('The clients limit has to be larger than 0!');
        }
    
        // Create a new Experiment object
        $experiment = new Experiment($experiment_id);
    
        // Make sure the current user is the owner of the experiment
        if( $experiment->owner_id !== $this->app->user->id
            && !$this->app->user->isSuperUser() ) {
            error('You do not have permissions to view this experiment!');
        }

        // Get the unique clients for that experiment
        $clients = $experiment->getClients(true, $days, $limit);

        // If we've reached here, everything is OK. Return the clients amount
        $this->app->output->setArguments(array(
            FLAG_SUCCESS => true,
            ':total_clients' => $clients,
        ));
    }
    
    /**
     * Retrieves the amount of clients a specific experiment handled.
     */
    public function getClientsAmount() {
        /* Retrieve information from the request */
        $experiment_id = intval($this->app->input('post', 'experiment_id')); // The ID of the experiment
        $days = intval($this->app->input('post', 'days')); // The amount of days to retrieve

        /* Validate the input */
        // The days parameter defaults to our days constant
        $days = !empty($days) ? $days : DEFAULT_QUERY_DAYS;
        
        // Make sure the experiment ID is not empty
        if( empty($experiment_id) ) {
            error('Invalid experiment ID.');
        }
        
        // Days can't be a negative number
        if( $days <= 0) {
            error('The amount of days has to be larger than 0!');
        }
        
        // Create a new Experiment object
        $experiment = new Experiment($experiment_id);
        
        // Make sure the current user is the owner of the experiment
        if( $experiment->owner_id !== $this->app->user->id 
            && !$this->app->user->isSuperUser() ) {
            error('You do not have permissions to view this experiment!');
        }
        
        // Get the amount of unique clients for that experiment
        $clients = $experiment->getClients(false, $days, DB_MAX_LIMIT);
            
        // If we've reached here, everything is OK. Return the clients amount
        $this->app->output->setArguments(array(
            FLAG_SUCCESS => true,
            ':total_clients' => $clients,
        ));
    }
}

?>