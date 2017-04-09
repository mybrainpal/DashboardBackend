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
     * Retrieves the amount of clients this specific customer handled.
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
        
        // Get the IDs of the customer trackers
        $trackers = $this->getTrackers();
        
        // Get all the unique clients for that tracker
        $result = $this->app->db->select('count(id)')->from('`clients`')->where(
            '`tracker_id` IN :trackers AND 
            (`created` BETWEEN DATE_SUB(NOW(), INTERVAL :days DAY) AND NOW())', array(
                ':trackers' => array_keys($trackers),
                ':days' => $days,
        ))->execute();
            
        // If we've reached here, everything is OK. Return the clients amount
        $this->app->output->setArguments(array(
            FLAG_SUCCESS => true,
            ':total_clients' => $result[0][0],
        ));
    }
    
    /**
     * Returns this customer's trackers.
     * 
     * @return array An array containing all the current customer trackers.
     */
    private function getTrackers() {
        // The result array
        $trackers = array();
        
        // Get the current customer ID
        $user_id = $this->app->user->id;
        
        // Get all the tracker IDs
        $result = $this->app->db->select('id, name')->from('`trackers`')->where('`user_id` = :user_id', array(
            ':user_id' => $user_id,
        ))->execute();
        
        // Loop through the trackers
        foreach($result as $row) {
            // Store the tracker ID as the index, and its name as the value
            $trackers[intval($row['id'])] = $row['name'];
        }
        
        // Return the trackers
        return $trackers;
    }
}

?>