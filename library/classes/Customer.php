<?php

/* Constants */
/* These constants are used in order to tell the class which parts of the object should it save in the DB */
define('USER_SAVE_BASIC',       0b0001); // Just the basic information
define('USER_SAVE_ADDITIONAL',  0b0010); // Just the additional information
define('USER_SAVE_ALL',         0b0111); // The entire object

/**
 * This class is responsible for everything user related - such as:
 * - Authentication
 * - Preferences
 * - Sessions
 * - Other user related activity
 * 
 * @author Nati
 */
class Customer extends Queryable {
    
    /**
     * @see Queryable::$table
     */
    protected $table = '`customers`';
    
    /**
     * @see Queryable::$update_fields
     */
    protected $update_fields = array('username', 'password');
    
	/**
	 * The user's user name.
	 *
	 * @var string
	 */
	protected $username;
	
	/**
	 * The user's encrypted password.
	 *
	 * @var string
	 */
	protected $password;
	
	/**
	 * Every additional information the user has entered (name, gender, home address, etc.).
	 * 
	 * @var array
	 */
	private $additional_info;
	
	/**
	 * Custom user preferences (theme, amount of items to display, etc.).
	 *
	 * @var array
	 */
	private $preferences;
	
	/**
	 * Is the current user authenticated or not?
	 * 
	 * @var boolean
	 */
	private $authenticated;
	
	/**
	 * Initialize the default properties for the user.
	 * 
	 * @param string $id Optional - The user ID to load. If no user ID is provided, load the user from the session.
	 */
	public function __construct($id = null, $init = true) {
	    global $app;
	    
		// Set the authentication flag to false
		$this->authenticated = false; 
		
		// If no user ID is provided, attempt to get one from the session
		if( $id === null && !empty( $app->input('session', 'user_id') ) ) {
		    // There's a user logged in, use it
		    $id = $app->input('session', 'user_id');
		    
		    // The user is authenticated
		    $this->authenticated = true;
		}
		
		// Call the parent constructor for further initialization
		parent::__construct($id, $init);
	}
	
	/**
	 * Load a user into the object. In case no user is entered, the function takes the user ID from the session instead.
	 * If the user is not authenticated and the user ID is not provided, the function loads the default values for a guest.
	 * 
	 * @param string $id The username to load.
	 */
	public function load($id = 0) {
		global $app;
		
		// Load the object
		parent::load($id);
		
		// Check if we still haven't got the user ID
		if( empty($this->id) ) {
		    // Set the default guest values
		    $this->id = 0; // The user ID
			$this->username = 'Guest'; // The user name
			$this->password = ''; // Empty password
			$this->authenticated = false; // Guests are not authenticated
		}

		// Get the additional information for the user from the DB
		$this->loadAdditionalInformation();
	}
	
	/**
	 * @see Queryable::save()
	 */
	public function save() {
		// Check the user name for invalid characters
		Security::validateUsername($this->username);
		
		// Call the parent's save method to save the object in the DB
		parent::save();
		
		// Save the additional information array
		$this->setAdditionalInformation();
	}
	
	/**
	 * Get the additional information for a specific user.
	 * 
	 * @return array The additional information for the user.
	 */
	private function loadAdditionalInformation() {
		global $app;
		
		// If we still doesn't have any user ID, load the additional info for a guest
		if (empty($this->id)) {
			// Set the additional information array to its default values
			$this->additional_info = array(
				'first_name' => 'Guest'
			);
			
			// Just return as we don't want to load anything from the DB
			return;
		}
		
		// Get the additional information row from the DB
		$result = $app->db->select('*')->from('`customers_info`')->where('`id`=:id', array(
		    ':id' => $this->id
		))->execute();
		
		// If we've got a row
		if( count($result) === 1) {
			// Store it
			$this->additional_info = $result[0];
		}
		// Otherwise, raise an error
		else {
			error("Could not load the additional information for user ID '$this->id'!");
		}
	}
	
	/**
	 * Set the additional information for the loaded user.
	 */
	private function setAdditionalInformation() {
        global $app;
        
        // If we doesn't have any user ID, raise an exception
        if (empty($this->id)) {
            error('Invalid user ID');
        }

		/* Generate the query */
		$query = ''; // The query string variable
		// Loop through the information array
		foreach($this->additional_info as $key => $value) {
			// Add the key and quoted value to the query
			$query .= "`$key` = {$app->db->quote($value)}, ";
		}
		
		// Remove the last comma
		$query = substr($query, 0, -2);
		
		// Set the additional information row in the DB
		$result = $app->db->update('`customers_info`')->set("($query)")->where('`id`=:id', array(
		    ':id' => $this->id
		))->execute();
	}
	
	/**
	 * Returns this customer's trackers.
	 *
	 * @return array An array containing all the current customer trackers.
	 */
	public function getTrackers() {
	    global $app;
	    
	    // Make sure the tracker ID is a valid number
	    if($this->id <= 0) {
	        // It isn't, throw an exception
	        error('Invalid user ID');
	    }
	    
	    // The result array
	    $trackers = array();
	    
	    // Get all the tracker IDs
	    $result = $app->db->select('id, name')->from('`trackers`')->where(
	        '`owner_id` = :owner_id', array(
	        ':owner_id' => $this->id,
	    ))->execute();
	    
	    // Loop through the trackers
	    foreach($result as $row) {
	        // Store the tracker ID as the index, and its name as the value
	        $trackers[intval($row['id'])] = $row['name'];
	    }
	
	    // Return the trackers
	    return $trackers;
	}
	
	/**
	 * Returns the clients information for the loaded user trackers.
	 * The data is returned per day (ie. GROUP BY date(`created`)).
	 * 
	 * @param bool $data Optional. Should the function return the clients data or just their amount?
	 * @param int $days Optional. The amount of days to query. Defaults to DEFAULT_QUERY_DAYS.
	 * @param int $limit Optional. The query limit. Defaults to DEFAULT_QUERY_LIMIT.
	 * @return array A two dimensional array containing the clients and their information.
	 */
	public function getClients($data = true, $days = DEFAULT_QUERY_DAYS, $limit = DEFAULT_QUERY_LIMIT) {
	    global $app;
	    
	    // Make sure that the current instance can support querying,
	    // and validate the user input
	    $this->validateQueryInput($days, $limit);
	    
	    // Check if we should return the data or just the clients amount
	    $select = ($data) ? '*' : 'count(id)';
	    
	    // Get the trackers
	    $trackers = $this->getTrackers();
	    
	    // Get all the unique clients for that tracker
	    $result = $app->db->select($select . ', DATE(created)')->from('`clients`')->where(
	        '`tracker_id` IN :trackers AND 
            (`created` BETWEEN DATE_SUB(SUBDATE(CURDATE(),1), INTERVAL :days DAY) AND CURDATE())', array(
                ':trackers' => array_keys($trackers),
                ':days' => $days,
	    ))->group_by('DATE(created)')->limit($limit)->execute();
            
	    // Organize the query result
        $result = $this->organizeResult($result);
        
        // Success! Return the clients information
        return $result;
	}
	
	/**
	 * Returns the successfully converted clients information for the loaded user trackers.
	 *
	 * @param bool $data Optional. Should the function return the clients data or just their amount?
	 * @param int $days Optional. The amount of days to query. Defaults to DEFAULT_QUERY_DAYS.
	 * @param int $limit Optional. The query limit. Defaults to DEFAULT_QUERY_LIMIT.
	 * @return array A two dimensional array containing the clients and their information.
	 */
	public function getConvertedClients($data = true, $days = DEFAULT_QUERY_DAYS, $limit = DEFAULT_QUERY_LIMIT) {
	    global $app;
	     
	    // Make sure that the current instance can support querying,
	    // and validate the user input
	    $this->validateQueryInput($days, $limit);
	     
	    // Check if we should return the data or just the clients amount
	    $select = ($data) ? 'client_id' : 'count(id)';
	     
	    // Get the trackers
	    $trackers = $this->getTrackers();
	     
	    // Get all the unique clients for that tracker
	    $result = $app->db->select($select . ', DATE(created)')->from('`sessions`')->where(
	        '`tracker_id` IN :trackers AND
            `state`=:state AND
            (`created` BETWEEN DATE_SUB(SUBDATE(CURDATE(),1), INTERVAL :days DAY) AND CURDATE())', array(
                ':trackers' => array_keys($trackers),
                ':state' => SESSION_STATE_CONVERTED,
                ':days' => $days,
        ))->group_by('DATE(created)')->limit($limit)->execute();
            
        // Organize the query result
        $result = $this->organizeResult($result);
	
        // Success! Return the clients information
        return $result;
	}
	
	/**
	 * @see Queryable::create()
	 */
	public function create() {
	    global $app;
	
	    // Validate the username
	    Security::validateUsername($this->username);
	
	    // Encrypt his password
	    $this->password = sha1($this->username . $this->password . SALT);
	
	    // Create the database row using the parent's method
	    parent::create();
	
	    // If we've passed the last query we can safely assume that we have an ID to use, so get it
	    $user_id = $app->db->lastInsertId;
	
	    // Also add a row at the additional information table
	    $app->db->insert_into('`customers_info`')->_('(`id`)')->values('(:id)', array(
	        ':id' => $user_id
	    ))->execute();
	
	    // All went well, return the user ID
	    return $this->id;
	}
	
	/**
	 * @see Queryable::delete()
	 */
	public function delete() {
	    global $app;
	
	    // Delete the database row using the parent's method
	    parent::delete();
	
	    // And insert everything to the DB
	    $app->db->delete_from('`customers_info`')->where('`id`=:id', array(
	        ':id' => $this->id
	    ))->execute();
	
	    // All went well, return the user ID
	    return $this->id;
	}
	
	/**
	 * Is the loaded user the site's administrator?
	 * 
	 * @return boolean True if it is, False if it isn't.
	 */
	function isSuperUser() {
	    // The administrator's user ID is 1.
	    return $this->id === 1;
	}
	
	/**
	 * Attempt to login as the requested user.
	 * In case the login succeed, the function initializes the user automatically.
	 *
	 * @param string $username The username requested.
	 * @param string $password The password supplied by the user.
	 * @return boolean True if the login succeed, false otherwise.
	 */
	public function login($username, $password) {
	    global $app;
	
	    // Check the username for any dangerous characters and length
	    Security::validateUsername($username);
	
	    // Try to get the requested username from the DB
	    $result = $app->db->select('*')->from($this->table)->where('`username`=:username', array(
	        ':username' => $username
	    ))->execute();
	
	    // If a user has returned from the database
	    if( count($result) === 1 ) {
	        // Get the user
	        $user = $result[0];
	
	        // Check the password stored and the password entered are the same
	        if($user['password'] === sha1($username . $password . SALT)) {
	            // It does - logged in successfully! Initialize the user...
	            $this->load($user['id']);
	
	            // Update the user last login
	            $app->db->update($this->table)->set('`last_login` = now()')->where('`username` = :username', array(
	                ':username' => $username
	            ))->execute();
	
	            // Done!
	            return true;
	        }
	    }
	
	    // If we've reached here the user has supplied the wrong credentials
	    return false;
	}
	
	/**
	 * This function is called when the user tries to get a property that does not exist or is protected.
	 * The function returns the requested property if it exist either in the object, the basic information array or in the user additional information array.
	 * If the property could not be found, an error is being thrown.
	 *
	 * @param string $name The name of the property to fetch.
	 * @return mixed The value of the property if it's exist.
	 */
	public function __get($name) {
	    // Check if $name is a property in the object
	    if( property_exists($this, $name) ) {
	        // It is! Return it
	        return $this->$name;
	    }
	    // Check if the property exist in the user additional information
	    else if( array_key_exists($name, $this->additional_info) ) {
	        // It is! Return it
	        return $this->additional_info[$name];
	    }
	    // We couldn't find the property, raise an error
	    else {
	        error("Could not find property '$name' in the User object!");
	    }
	}
	
	/**
	 * This function is called when the user tries to set a property that does not exist or is protected.
	 * The function sets the requested property if it exist either in the basic information array or in the user additional information array.
	 * If the property could not be found, an error is being thrown.
	 *
	 * @param string $name The name of the property to set.
	 * @param mixed $value The value of the property to set.
	 */
	public function __set($name, $value) {
	    // Check if the property exist in the user basic information and it's not the instance ID
	    if( property_exists($this, $name) && $name !== 'id') {
	        // It is! Set it
	        $this->$name = $value;
	    }
	    // Check if the property exist in the user additional information
	    else if( array_key_exists($name, $this->additional_info) ) {
	        // It is! Set it
	        $this->additional_info[$name] = $value;
	    }
	    // We couldn't find the property, raise an error
	    else {
	        error("Could not set property '$name' in the User object!");
	    }
	}
}

?>