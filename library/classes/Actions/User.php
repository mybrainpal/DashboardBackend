<?php

/** 
 * This is the user category controller.
 * This controller handles everything user related, ie. login in, profile viewing, etc.
 * 
 * @author Nati
 */
class ActionsUser extends Actions {
    
    protected $actions = array(
        'index' => false,
        'profile' => true,
        'login' => false,
        'logout' => true,
        'basicinfo' => true,
        'additionalinfo' => true,
        'listusers' => true,
        'adduser' => true,
    );
    
    /**
     * Constructs the ActionUser instance and initialize its properties.
     * Declares that this controller DOES NOT require authentication.
     * 
	 * @param BrainPal $app_object A reference to the application object.
	 */
	public function __construct(&$app_object) {
        // Call the main Action constructor
        parent::__construct($app_object);
	}
	
	/**
	 * This is the function that's being called when the user doesn't enter any method.
	 * It executes the default functionality for the controller, so in this case
	 * it displays the profile page for a logged in user,
	 * or redirects to a login page for an unauthenticated one.
	 */
	public function index() {
		// Check if the user is logged in
		if( $this->app->user->authenticated ) {
			// He is! Send back the CSRF token along with the authentication flag
		    $this->app->output->setArguments(array('auth' => true, ':csrf_token' => $this->app->getCSRFToken()));
		}
		else {
		    // Inform the front-end the user is not authenticated
		    $this->app->output->setArguments(array('auth' => false));
		}
	}
	
	/**
	 * Displays the complete profile of the specified user.
	 * If no user is specified, display the profile of the currently logged in user.
	 * 
	 * If the user is not authenticated, redirect to the login page.
	 */
	public function profile() {	    
	    // @TODO Load the profile page with the user values
	}
	
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
			$this->app->output->setArguments(array(':success' => true));
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
						$this->app->output->setArguments(array(':success' => true));
					}
					else {
						// Login failed! Return with an error
						$this->app->output->setArguments(array(':success' => false, ':error_msg' => 'Invalid credentials'));
					}
				}
				else {
					// The username or password are an empty string
					$this->app->output->setArguments(array(':success' => false, ':error_msg' => 'Username or password are empty'));
				}
				
				
			}
			else {
				// He isn't! Don't do anything
				$this->app->output->setTemplate('user/login');
				// @TODO maybe report this as an error?
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
        $this->app->output->setArguments(array(':success' => true));
	}
	
	/**
	 * Get/set the basic information for the current user (username and password).
	 */
	public function basicInfo() {
		// He is, Check if the form has been submitted
		if( !empty($this->app->input('post')) ) {
			// Check that the user has entered his correct password
			if( $this->app->input('post', 'verify_password') === $this->app->user->password ) {
				// If the user is changing his password, check that it matches
				if( !empty($this->app->input('post', 'password1')) &&
					($this->app->input('post', 'password1') === $this->app->input('post', 'password2')) ) {
					// It does, Set the basic information of the user
					$this->app->user->username = $this->app->input('post', 'username');
					$this->app->user->password = $this->app->input('post', 'password1');
					
					// Save the additional information of the user in the DB
					$this->app->user->save(USER_SAVE_BASIC);
					
					// Return that the saving process succeeded
					$this->app->output->setArguments(array(':success' => true));
				}
				else {
					// It doesn't, inform the user
					$this->app->output->setArguments(array(':success' => false, ':error_msg' => "Passwords Doesn't match!"));
				}					
			}
			else {
				// He hasn't, terminate with an error
				$this->app->output->setArguments(array(':success' => false, ':error_msg' => 'Wrong password!'));
			}
		}
		else {
			// Returns the user basic information
			// @TODO Add the template arguments
		}
	}
	
	/**
	 * Get/set the additional information for the current user (full name, address, phone number, etc.).
	 */
	public function additionalInfo() {
		// He is, Check if the form has been submitted
		if( !empty($this->app->input('post')) ) {
			// Set the additional information of the user
			$this->app->user->first_name = $this->app->input('post', 'first_name');
			$this->app->user->last_name = $this->app->input('post', 'last_name');
			$this->app->user->gender = ($this->app->input('post', 'gender') === 'Male') ? '0' : '1'; // Gender (0 = male, 1 = female)
			$this->app->user->phone_number = preg_replace('/[^0-9+-]/', '', $this->app->input('post', 'phone_number')); // Phone number (digits, '+' and '-' only)
			$this->app->user->address = $this->app->input('post', 'address');
			$this->app->user->hobbies = $this->app->input('post', 'hobbies');
			
			// @TODO Add support for profile pictures
			
			// Save the additional information of the user in the DB
			$this->app->user->save(USER_SAVE_ADDITIONAL);
			
			// Return that the registration process succeeded
			$this->app->output->setArguments(array(':success' => true));
		}
		else {
			// Set its arguments
			// @TODO Add the additional information arguments
		}
	}
	
	public function listUsers() {
	    // Return the list of users
	    // @TODO Return the users list
	}
	
	/**
	 * Display the user creation form page for administrators,
	 * or create a new user if the registration form has been submitted.
	 */
	public function addUser() {
	    // Check if the registration form has been submitted
	    if( !empty($this->app->input('post')) ) {
	        // Process the registration input
	        $username = $this->app->input('post', 'username'); // Get the username
	        $password = $this->app->input('post', 'password'); // Get the password
	        	
	        // Create the new user and get the email validation token
	        $this->app->user->create($username, $password);
	        	
	        // Return that the registration process succeeded
	        $this->app->output->setArguments(array(':success' => true));
	        	
	    }
	    else {
	        // It isn't! Load the registration template
	        $this->app->output->setTemplate('users/create');
	    }
	}
}

?>