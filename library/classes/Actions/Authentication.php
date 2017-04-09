<?php

/** 
 * This controller handles authentication actions, such as user login and logout.
 * 
 * @author Nati
 */
class ActionsAuthentication extends Actions {
    
    protected $actions = array(
        'login' => false,
        'logout' => true,
    );
    
	/**
	 * Display the login form page for unauthenticated users,
	 * or login the user if the login form has been submitted.
	 * 
	 * If the user is authenticated, redirect to the profile page.
	 */
	public function login() {
		// Check if the user is logged in
		if( $this->app->user->authenticated ) {
			// He is, just return true
			$this->app->output->setArguments(array(FLAG_SUCCESS => true));
		}
		else {
			// He isn't, Check if the login form has been submitted
			if( !empty($this->app->input('post')) ) {
				// It did! process the login input
				$username = $this->app->input('post', 'username'); // Get the username
				$password = $this->app->input('post', 'password'); // Get the password
				
				// Check that we have both the username and the password
				if( !empty($username) && !empty($password) ) {
					// We do, send it to the user class for validation
					if( $this->app->user->login($username, $password) ) {
						// Logged in successfully! Set the user in the session
						$this->app->input('session', 'username', $username);
							
						// Return that the login process succeeded
						$this->app->output->setArguments(array(FLAG_SUCCESS => true));
					}
					else {
						// Login failed! Return with an error
						error('Invalid credentials at Authentication::login()');
					}
				}
				else {
					// The username or password are an empty string
					error('Username or password are empty at Authentication::login()');
				}
				
				
			}
			else {
				// He isn't! Report the error
				error('No data received at Authentication::login()');
			}
		}
	}
	
	/**
	 * Logout a logged in user.
	 * Destroys the entire session in the process.
	 *
	 * If the user is not authenticated, redirect to the login page.
	 */
	public function logout() {
        // Destroy the user's session
        session_destroy();

        // Logged out successfully
        $this->app->output->setArguments(array(FLAG_SUCCESS => true));
	}
}

?>