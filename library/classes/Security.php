<?php

/**
 * This class is responsible for the security of the application.
 * It contains the validation functions being used (such as `validate_username` and `validate_email`),
 * as well as the tokens validation.
 * 
 * @author Nati
 */
class Security {
	/**
	 * Generates a new token for instant usage. The token is always 32 bytes in length.
	 * The function makes sure that the token doesn't already exist.
	 * 
	 * @return string The generated token.
	 */
	public static function generateToken() {
		global $app;
		
		// Loop until we have a new unused token
		do {
			// Generate a token
			$token = md5(mt_rand());
			
			// Check if the token does not exist in the DB
		} while($app->db->select('*')->from('`tokens`')->where('`token`=:token', array(':token' => $token))->execute());
		
		// Now, return the token
		return $token;
	}
	
	/**
	 * Creates a new token entry at the DB.
	 *
	 * @param string $type The type of the token. Default is `request`.
	 * @param int $user_id The user ID for the token. If the user ID is not set, the currently loaded user ID is used.
	 * @return string The newly created token string.
	 */
	public static function createToken($type = 'request', $user_id = false) {
		global $app;
		
		// Check if we've got a user or the user is logged in
		if( !$user_id && !$app->user->authenticated ) {
			// The user is not logged in and we have'nt got any specific user. Do nothing
			return '';
		}

		// Get the user ID from the currently logged in user or from a specific one we've got
		$user_id = $user_id ? $user_id : $app->user->id;

	
		// Generate a new token
		$token = Security::generateToken();
		
		// Add the token to the DB
		$app->db->insert_into('`tokens`')->_('(`user_id`, `token`, `type`)')->values('(:user_id, :token, :type)', array(
				':user_id' 	=> $user_id,
				':token' 	=> $token,
				':type' 	=> $type
		))->execute();
		
		// Return the token string
		return $token;
	}

	/**
	 * Check that the given token is a valid one, by value, type and user ID.
	 *
	 * @param string $token The token string.
	 * @param string $type The type of the token. Default is `request`.
	 * @param int $user_id The user ID for the token. If the user ID is not set, the currently loaded user ID is used.
	 * @return boolean True if the token is valid, false otherwise.
	 */
	public static function checkToken($token, $type = 'request', $user_id = false) {
		global $app;
		
		// Check if we've got a user or the user is logged in
		if( !$user_id && !$app->user->authenticated ) {
			// The user is not logged in and we have'nt got any specific user. Do nothing
			return true;
		}

		// Get the user ID from the currently logged in user or from a specific one we've got
		$user_id = $user_id ? $user_id : $app->user->id;
		
		// Check that the token isn't empty
		if( empty($token) ) {
			// It is, return false
			return false;
		}
	
		// Check that the token exist in the DB
		if( $app->db->select('*')->from('`tokens`')->where('`type`=:type AND `token`=:token AND `user_id`=:user_id', array(
				':token' 	=> $token,
				':type'		=> $type,
				':user_id'	=> $user_id
		))->execute() ) {
			// It does, return true
			return true;
		}
		
		// The token doesn't exist, return false
		return false;
	}
	
	/**
	 * Delete a token from the DB.
	 *
	 * @param string $token The token to delete. If the token is not given,
	 *                      the function will delete all the tokens matching $type for the user requested.
	 * @param string $type The type of the token. Default is `request`.
	 * @param int $user_id The user ID for the token. If the user ID is not set, the currently loaded user ID is used.
	 */
	public static function deleteToken($token = false, $type = 'request', $user_id = 0) {
		global $app;
	
		// Check if we've got a user or the user is logged in
		if( !$user_id && !$app->user->authenticated ) {
			// The user is not logged in and we have'nt got any specific user. Do nothing
			return;
		}

		// Get the user ID from the currently logged in user or from a specific one we've got
		$user_id = $user_id ? $user_id : $app->user->id;
	
		// If the token is given
		if( !empty($token) ) {
			$where_statement = '`token`=:token AND `type`=:type AND `user_id`=:user_id';
		}
		// Otherwise delete every token $type for the user
		else {
			$where_statement = '`type`=:type AND `user_id`=:user_id';
		}
	
		// Delete the requested token/s
		$app->db->delete()->from('tokens')->where($where_statement, array(
				':user_id'	=> $user_id,
				':token'	=> $token,
				':type'		=> $type
		))->execute();
	}
	
	/**
	 * Check the email address entered for dangerous characters and length.
	 * The email address can contain only alpha-numeric characters as well as the `_`, `.`, `-` and `@` characters.
	 * Its length must be lower than 64 characters.
	 *
	 * @param string $email The email address to validate.
	 */
	public static function validateEmail($email) {
		// Check the email for any dangerous characters errors
		if( !preg_match('/[a-z0-9_\.-]+\@[a-z0-9-]+\.[a-z0-9-\.]+/i', $email) ) {
			// The email is not valid! Print an error but don't log it
			error('The username entered is not valid!', false);
		}
		
		// Check the email for the maximum length
		if( strlen($email) > 64 ) {
			// The email is too long! Print an error but don't log it
			error('The username entered is too long!', false);
		}
	}
	
	/**
	 * Check the username entered for dangerous characters and length.
	 * The username can contain only alpha-numeric characters as well as the `_`, `-` characters.
	 * Its length must be lower than 32 characters.
	 *
	 * @param string $username The username to validate.
	 */
	public static function validateUsername($username) {
	    
	    // Check the username for any dangerous characters errors
	    if( preg_match('/[^a-z0-9_-]+/i', $username) ) {
	        // The email is not valid! Print an error but don't log it
	        error('The username entered is not valid!', false);
	    }
	
	    // Check the email for the maximum length
	    if( strlen($username) > 32 ) {
	        // The email is too long! Print an error but don't log it
	        error('The username entered is too long!', false);
	    }
	}
}

?>