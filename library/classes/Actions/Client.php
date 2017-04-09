<?php 

/** 
 * This is the front-end client handling controller.
 * This controller stores, retrieves, and initializes, BrainPal's front end clients.
 * 
 * @author Nati
 */
class ActionsClient extends Actions{
    
    protected $actions = array(
        'init' => false,
        'setstate' => false,
    );
    
    /**
     * Initializes the client.
     *
     * If this is a new client, this function will initialize its data in the database.
     * If this client needs a new session, this function will generate one for it.
     */
    public function init() {
        $error = array(); // A temporary placeholder for any errors that might occur
        
        // Check if this is a new client for this domain
        if( empty($this->app->input('session', 'client_id'))) {
            // This is a brand new client, we need to initialize the it
            $client_id = $this->addClient();
            
            // Check if the client has been created successfully
            if($client_id > 0) {
                // It did, set the ID in the session
                $this->app->input('session', 'client_id', $client_id);
            } else {
                // It didn't, error it out
                error('Could not create the client!');
            }
        }
        
        // Check if we need to create or renew the client's session
        if( empty($this->app->input('session', 'session_id')) ||
            (time() - $this->app->input('session', 'session_created')) > SESSION_KEEPALIVE) {
                // This is a brand new client, we need to initialize the it
                $session_id = $this->addSession();
                
                // Check if the client has been created successfully
                if($session_id > 0) {
                    // It did, set the ID in the session
                    $this->app->input('session', 'session_id', $session_id);
                } else {
                    // It didn't, error it out
                    error('Could not create the session!');
                }
            }
            
        // If we've reached here, everything is OK. Return the CSRF token
        $this->app->output->setArguments(array(
            FLAG_SUCCESS => true
        ));
    }
    
    /**
     * Sets the client state.
     */
    public function setState() {
        /* Retrieve information from the request */
        $session_id = intval($this->app->input('session', 'session_id')); // The session ID
        $state = $this->app->input('post', 'state'); // The tracker ID
        
        // Update the sessions's state
        $this->app->db->update('`sessions`')->SET('(`state`)')
        ->values('(:state)', array(':state' => $state))->where('`id`=:id', array(
            ':id' => $session_id,
        ))->execute();
        
        // If we've reached here, everything is OK. Return the CSRF token
        $this->app->output->setArguments(array(
            FLAG_SUCCESS => true
        ));
    }
    
    /**
     * Adds a new client to the database.
     */
    private function addClient() {
        /* Retrieve information from the request */
        $tracker_id = intval($this->app->input('post', 'tracker')); // The tracker ID
        $device_type = intval($this->app->input('post', 'client.agent.mobile')); // The client's device type
        $created = $this->app->input('post', 'timestamp'); // Exact timestamp of when the client creation occurred
        $error = array(); // A temporary placeholder for any errors that might occur
        
        /* Validate the input */
        // Make sure the client is not already registered
        if( !empty($this->app->input('session', 'client_id'))) {
            $error[] = 'The client is already stored in the database!';
        }
            
        // Validate the user agent
        if( empty($tracker_id) ) {
            // The timestamp is not valid
            $error[] = 'Tracker ID is missing!';
        }
        
        // Validate the timestamp
        if( !is_numeric($created) ) {
            // The timestamp is not valid
            $error[] = '`timestamp` has to be a valid UNIX timestamp!';
        }
        
        // If there has been an error, send it and terminate the script
        if( !empty($error) ) {
            error($error);
        }
        
        /* If we've reached here, the input is valid */
        // Store the log message in the DB
        $this->app->db->insert_into('`clients`')->_('(`tracker_id`, `device_type`, `created`)')
        ->values('(:tracker_id, :device_type, :created)', array(
            ':tracker_id' => $tracker_id,
            ':device_type' => $device_type,
            ':created' => mysql_date($created),
        ))->execute();
        
        // If we've passed the last query we can safely assume that we have an ID to use, so get it
        $client_id = $this->app->db->lastInsertId;
        
        // Return the client ID, as it is saved in the database
        return $client_id;
    }
    
    /**
     * Adds a new client session to the database.
     */
    private function addSession() {
        /* Retrieve information from the request */
        $client_id = intval($this->app->input('session', 'client_id')); // The client ID
        $tracker_id = intval($this->app->input('post', 'tracker_id')); // The tracker ID
        $created = $this->app->input('post', 'timestamp'); // Exact timestamp of when the client init occurred
        $useragent = $this->app->input('server', 'HTTP_USER_AGENT'); // The client's user agent
        $state = $this->app->input('post', 'state'); // The client's state (landing page, filling form, etc.)
        $manipulated = intval($this->app->input('post', 'manipulated')); // Did BrainPal change this user's page?
        $metadata = array( // Some metadata regarding the client's session
            'browser' => $this->app->input('post', 'client.agent.browser'),
            'browserVersion' => $this->app->input('post', 'client.agent.browserVersion'),
            'deviceType' => intval($this->app->input('post', 'client.agent.mobile')),
            'os' => $this->app->input('post', 'client.agent.os'),
            'osVersion' => $this->app->input('post', 'client.agent.osVersion'),
            'cookiesEnabled' => $this->app->input('post', 'client.cookiesEnabled') == true,
            'height' => intval($this->app->input('post', 'client.screen.height')),
            'width' => intval($this->app->input('post', 'client.screen.width')),
            'availHeight' => intval($this->app->input('post', 'client.screen.availHeight')),
            'availWidth' => intval($this->app->input('post', 'client.screen.availWidth')),
        );
    
        $error = array(); // A temporary placeholder for any errors that might occur
    
        /* Validate the input */
        // The state can be an empty string
        $state = $state ? $state : '';
        
        // Validate the user agent
        if( empty($useragent) ) {
            // The timestamp is not valid
            $error[] = '`useragent` cannot be empty!';
        }
    
        // Validate the timestamp
        if( !is_numeric($created) ) {
            // The timestamp is not valid
            $error[] = '`timestamp` has to be a valid UNIX timestamp!';
        }
    
        // If there has been an error, send it and terminate the script
        if( !empty($error) ) {
            error($error);
        }
    
        /* If we've reached here, the input is valid */
        // Store the log message in the DB
        $this->app->db->insert_into('`sessions`')
        ->_('(`client_id`, `tracker_id`, useragent`, `state`, `manipulated`, metadata`, `created`)')
        ->values('(:client_id, :tracker_id, :useragent, :state, :manipulated, :metadata, :created)', array(
            ':client_id' => $client_id,
            ':tracker_id' => $tracker_id,
            ':useragent' => $useragent,
            ':state' => $state,
            ':manipulated' => $manipulated,
            ':metadata' => json_encode($metadata),
            ':created' => mysql_date($created),
        ))->execute();
        
        // Store the time the session was created (in seconds, not microseconds)
        $this->app->input('session', 'session_created', substr($created, 0, 10));
        
        // Store the tracker ID
        $this->app->input('session', 'tracker_id', $tracker_id);
    
        // If we've passed the last query we can safely assume that we have an ID to use, so get it
        $session_id = $this->app->db->lastInsertId;
        
        // Return the session ID, as it is saved in the database
        return $session_id;
    }
}

?>