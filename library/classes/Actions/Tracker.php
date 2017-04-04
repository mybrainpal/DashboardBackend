<?php 

define('DEFAULT_QUERY_DAYS', 28); // The default amount of days for data retrieval

/** 
 * This controller handles all operations for a single customer,
 * such as retrieving the total number of clients, calculating conversion rates and more.
 * 
 * @author Nati
 */
class ActionsCustomer extends Actions{
    
    protected $actions = array(
        'getclientsamount' => true,
    );
    
    /**
     * Retrieves the amount of clients a specific tracker handled.
     */
    public function getClientsAmount() {
        /* Retrieve information from the request */
        $tracker_id = intval($this->app->input('post', 'tracker_id')); // The amount of days to retrieve
        $days = intval($this->app->input('post', 'days')); // The amount of days to retrieve
        $error = array(); // A temporary placeholder for any errors that might occur

        /* Validate the input */
        // The days parameter defaults to our days constant
        $days = !empty($days) ? $days : DEFAULT_QUERY_DAYS;
        
        // Make sure the tracker is not empty
        if( empty($tracker_id) ) {
            $error[] = 'Invalid tracker ID.';
        }
        
        if( $days <= 0) {
            $error[] = 'The amount of days has to be larger than 0!';
        }
        
        // Make sure the current user owns the requested tracker
        $result = $this->app->db->select('count(id)')->from('`trackers`')->where(
            '`user_id` = :user_id AND `id` = :tracker_id', array(
                ':user_id' => $this->app->user->id,
                ':tracker_id' => $tracker_id
        ))->execute();
            
        // Make sure at least 1 row returned (it not rows returned, it means the tracker
        // ID is invalid or the current user does not own it)
        if( !$result[0][0] ) {
            $error[] = 'Tracker does not exists or you don\'t have permissions to view it!';
        }
        
        // If there has been an error, send it and terminate the script
        if( !empty($error) ) {
            error($error);
        }
        
        // Get all the unique clients for that tracker
        $result = $this->app->db->select('count(id)')->from('`clients`')->where(
            '`tracker_id`=:tracker_id AND 
            (`created` BETWEEN DATE_SUB(NOW(), INTERVAL :days DAY) AND NOW())', array(
                ':tracker_id' => $tracker_id,
                ':days' => $days,
        ))->execute();
            
        // If we've reached here, everything is OK. Return the clients amount
        $this->app->output->setArguments(array(
            ':success' => true,
            ':total_clients' => $result[0][0],
        ));
    }
}

?>