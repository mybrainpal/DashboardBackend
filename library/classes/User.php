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
class User {
	
	/**
	 * The user ID.
	 * 
	 * @var int
	 */
	private $id;
	
	/**
	 * The basic information for the user (username, password, etc.).
	 *
	 * @var array
	 */
	private $basic_info;
	
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
	 * @param string $username Optional - The username to load. If no username is provided, load a user from the session.
	 */
	public function __construct($username = 'Guest') {
		// Set the authentication flag to false
		$this->authenticated = false; 
		
		// Call the user initialization function
		$this->loadBasicInformation(($username !== 'Guest') ? $username : null);
	}
	
	/**
	 * Load a user into the object. In case no user is entered, the function takes the username from the session instead.
	 * If the user is not authenticated and the username is not provided, the function loads the default values for a guest.
	 * 
	 * @param string $username The username to load.
	 */
	private function loadBasicInformation($username = false) {
		global $app;
        
		// If no username is provided, attempt to get one from the session
		if( empty($username) && !empty( $app->input('session', 'username') ) ) {
			// There's a user logged in, use it
			$username = $app->input('session', 'username');
		}
		
		// Check if we still haven't got the username
		if( empty($username) ) {
			// We haven't, set the user basic information to their default values
			$this->basic_info = array(
					'username' => 'Guest'
			);
			
			// Set the user ID to 0
			$this->id = 0; 
			
			// The user is not authenticated
			$this->authenticated = false;
			
			// Load the default additional information for guests
			$this->loadAdditionalInformation();
			
			// Just return as we don't want to load anything from the DB
			return;
		}
		
		// Now that we have a username, we can grab it from the DB
		$result = $app->db->select('*')->from('`users`')->where('`username` = :username', array(':username' => $username))->execute();
		
		// If a user has returned from the database
		if( count($result) === 1 ) {
			// Use it
			$user = $result[0];

			// Set the ID property
			$this->id = intval(array_shift($user));
			
			// Set the user basic info containing everything else from the DB
			$this->basic_info = $user;
			
			// The user is authenticated
			$this->authenticated = true; 
			
			// Get the additional information for the user from the DB
			$this->loadAdditionalInformation($this->id);
		}
		// Otherwise raise an error
		else {
			error("The user '$username' doesn't exist in the database!");
		}
	}
	
	/**
	 * Set user data in the DB. In case no user is entered, the function takes the username from the session instead.
	 * If the user is not authenticated and the username is not provided, the function doesn't do anything.
	 *
	 * @param array $user_data Optional - The data to set. If no data is provided, just use the internal array.
	 * @param string $username Optional - The username to load. If no username is provided, try to take it from the session
	 */
	private function setBasicInformation($user_data = null, $username = false) {
		global $app;
	
		// If no username is provided, attempt to get one from the session
		if( $username && !empty( $app->input('session', 'username') ) ) {
			// It exist, use it
			$username = $app->input('session', 'username');
		}
	
		// Check if we still haven't got the username
		if( empty($username) ) {
			// We haven't, just return
			return;
		}
		
		// Set the $array_data array in case it is null
		$user_data = isset($user_data) ? $user_data : $this->basic_info;
		
		// Check the the username for invalid characters
		Security::validateUsername($user_data['username']);
	
		// Generate the query
		$query = ''; // The query string variable
		foreach($user_data as $key => $value) {
			// Add the key and quoted value to the query
			$query .= "`$key` = {$app->db->quote($value)}, ";
		}
		
		// Remove the last comma
		$query = substr($query, 0, -2);
		
		// Set the additional information row in the DB
		$result = $app->db->update('`users`')->set("($query)")->where('`username`=:username', array(
		    ':username' => $username
		))->execute();
		
		// Initialize the user again
		$this->loadBasicInformation($username);
	}
	
	/**
	 * Get the additional information for a specific user.
	 * 
	 * @param int $user_id Optional - The user ID to query. Default is the current user.
	 * @return array The additional information for the user.
	 */
	private function loadAdditionalInformation($user_id = 0) {
		global $app;
		
		// If no user ID has been entered, use the current one
		$user_id = (empty($user_id) && $this->id !== 0 ) ? $this->id : intval($user_id);
		
		// If we still doesn't have any user ID, load the basic info for a guest
		if (empty($user_id)) {
			// Set the additional information array to its default values
			$this->additional_info = array(
				'first_name' => 'Guest'
			);
			
			// Just return as we don't want to load anything from the DB
			return;
		}
		
		// Get the additional information row from the DB
		$result = $app->db->select('*')->from('`users_info`')->where('`id`=:id', array(
		    ':id' => $user_id
		))->execute();
		
		// If we've got a row
		if( count($result) === 1) {
			// Store it
			$this->additional_info = $result[0];
		}
		// Otherwise, raise an error
		else {
			error("Could not load the additional information for user ID '$user_id'!");
		}
	}
	
	/**
	 * Set the additional information for a specific user.
	 * 
	 * @param array $info Optional - The additional information to set. If no data is provided, just use the internal array.
	 * @param int $user_id Optional - The user ID to set. Default is the current user.
	 */
	private function setAdditionalInformation($info = null, $user_id = 0) {
        global $app;
        
        // If no user ID has been entered, use the current one
        $user_id = (empty($user_id) && $this->id !== 0) ? $this->id : intval($user_id);
        
        // If we still doesn't have any user ID, don't do anything
        if (empty($user_id)) {
            return;
        }
        
        // If we don't have any information, use the additional_info array
        $info = isset($info) ? $info : $this->additional_info;
		
		/* Generate the query */
		$query = ''; // The query string variable
		// Loop through the information array
		foreach($info as $key => $value) {
			// Add the key and quoted value to the query
			$query .= "`$key` = {$app->db->quote($value)}, ";
		}
		
		// Remove the last comma
		$query = substr($query, 0, -2);
		
		// Set the additional information row in the DB
		$result = $app->db->update('`users_info`')->set("($query)")->where('`id`=:id', array(
		    ':id' => $user_id
		))->execute();
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
		$result = $app->db->select('*')->from('`users`')->where('`username`=:username', array(
		    ':username' => $username
		))->execute();
		
		// If a user has returned from the database
		if( count($result) === 1 ) {
			// Get the user
			$user = $result[0];
			
			// Check the password stored and the password entered are the same
			if($user['password'] === sha1($username . $password . SALT)) {
				// It does! Logged in successfully! Initialize the user...
				$this->loadBasicInformation($username);
				
				// Update the user last login
				$app->db->update('`users`')->set('`last_login` = now()')->where('`username` = :username', array(
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
	 * Try to create a new user.
	 * If the function fails it prints an error, otherwise it returns the email validation token.
	 * 
	 * @param string $username The username entered.
	 * @param string $password The password entered.
	 * @param boolean $validate_email Controls whether or not we validate the email address
	 * @return string The email validation token
	 */
	public function create($username, $password, $validate_email = true) {
		global $app;
		
		// Validate the username
		Security::validateUsername($username);
		
		// Encrypt his password
		$enc_password = sha1($username . $password . SALT);
		
		// And insert everything to the DB
		$app->db->insert_into('`users`')->_('(`username`, `password`)')->values('(:username, :password)', array(
				':username' => $username,
				':password' => $enc_password,
		))->execute();
		
		// If we've passed the last query we can safely assume that we have an ID to use, so get it
		$user_id = $app->db->lastInsertId;
		
		// Also add a row at the additional information table
		$app->db->insert_into('`users_info`')->_('(`id`)')->values('(:id)', array(
		    ':id' => $user_id
		))->execute();
		
		// Now, create a new token for the email validation
		$token = Security::createToken('email', $user_id);
		
		// @TODO Email the user the validation token
		
		// All went well, return the token
		return $token;
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
		if( isset($this->$name) ) {
			// It is! Return it
			return $this->$name;
		}
		// Check if the property exist in the user basic information
		else if( array_key_exists($name, $this->basic_info) ) {
			// It is! Return it
			return $this->basic_info[$name];
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
		// Check if the property exist in the user basic information
		if( array_key_exists($name, $this->basic_info) ) {
			// It is! Set it
			$this->basic_info[$name] = $value;
		}
		// Check if the property exist in the user additional information
		else if( array_key_exists($name, $this->additional_info) ) {
			// It is! Set it
			$this->additional_info[$name] = $value;
		}
		// We couldn't find the property, raise an error
		else {
			error("Could not find property '$name' in the User object!");
		}
	}
	
	/**
	 * This function saves the changes made to the user object in the DB.
	 * 
	 * @param string $type The type of information to store ('all', 'basic' or 'additional').
	 */
	public function save($type = USER_SAVE_ALL) {
		// Save the basic information of the user
		if( $type & USER_SAVE_BASIC ) $this->setBasicInformation();
		
		// Save the additional information of the user
		if( type & USER_SAVE_ADDITIONAL ) $this->setAdditionalInformation();
	}
}

?>